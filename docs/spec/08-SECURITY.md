# PHẦN 8 — SECURITY ARCHITECTURE
## Bảo mật, Phân quyền, Kiểm soát Truy cập

---

## 1. Security Principles

```
Defense in Depth:    Nhiều lớp bảo vệ chồng lên nhau
Least Privilege:     Chỉ cấp đúng quyền cần thiết
Zero Trust:          Không tin tưởng mặc nhiên, luôn xác thực
Audit Everything:    Mọi hành động đều có log không thể xóa
Fail Secure:         Khi lỗi → từ chối thay vì cho phép
Data Minimization:   Chỉ thu thập và hiển thị dữ liệu cần thiết
```

---

## 2. Authentication Architecture

### 2.1 JWT Token Strategy

```
Access Token:
  - Lifetime: 15 minutes
  - Contains: user_id, context (company, plant, role, permissions)
  - Signed: RS256 (asymmetric)
  - Stored: Memory only (never localStorage)

Refresh Token:
  - Lifetime: 7 days
  - Stored: HttpOnly Cookie (not accessible by JS)
  - Rotated on each use
  - Invalidated on logout from all devices

Token Rotation:
  - Every 15 minutes (silent refresh)
  - On context switch
  - On permission change
```

### 2.2 Password Policy

```
Minimum length: 12 characters
Requires: uppercase + lowercase + digit + special char
Bcrypt rounds: 12
Password history: cannot reuse last 12 passwords
Max age: 90 days (configurable)
Account lockout: 5 failed attempts → 15 min lockout
MFA: Optional (TOTP / Email OTP) — recommended for C-level
```

### 2.3 Session Management

```
Concurrent sessions: Max 3 per user (configurable)
Idle timeout: 30 minutes
Absolute timeout: 8 hours
Session invalidation: On password change, on role change
Remember me: 30-day refresh token (optional, configurable)
```

---

## 3. Authorization — RBAC System

### 3.1 Role Hierarchy

```
System Admin (super admin — internal only)
    │
Group Level Roles:
    ├── group_owner        (Chủ tịch HĐQT)
    ├── group_executive    (CEO, CFO, COO Group)
    └── group_viewer       (Board member, observer)
         │
Company Level Roles:
    ├── company_admin      (Trưởng phòng IT)
    ├── company_director   (Giám đốc công ty)
    ├── finance_director
    ├── hr_director
    ├── operations_director
    └── company_viewer
         │
Plant Level Roles:
    ├── plant_manager
    ├── production_manager
    ├── quality_manager
    ├── warehouse_manager
    ├── hr_manager
    └── plant_viewer
         │
Department/Line Roles:
    ├── supervisor
    ├── operator
    ├── qc_inspector
    ├── warehouse_staff
    └── data_entry
```

### 3.2 Permission Naming Convention

```
{resource}.{action}

Resources: work_orders, production_plans, lots, inventory_transactions,
           sales_orders, customers, employees, payroll, journal_entries,
           ai_recommendations, alerts, action_items, decisions

Actions: view_list, view_detail, create, update, delete,
         approve, reject, release, complete, export, import

Examples:
  work_orders.view_list
  work_orders.create
  work_orders.approve
  payroll.view_detail      (restricted: only own payslip unless manager)
  ai_recommendations.approve
  decisions.create
```

### 3.3 Role Permission Matrix (Abridged)

| Permission | Operator | Supervisor | Plant Mgr | Ops Director | CFO | CEO |
|-----------|---------|-----------|----------|-------------|-----|-----|
| work_orders.view_list | Own WO | Plant | Plant | All Plants | - | All |
| work_orders.create | ❌ | ✅ | ✅ | ✅ | ❌ | ❌ |
| work_orders.approve | ❌ | ❌ | ✅ | ✅ | ❌ | ❌ |
| inventory.issue | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| payroll.view_detail | Own | Dept | Plant | All | All | All |
| payroll.approve | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| journal_entries.create | ❌ | ❌ | ❌ | ❌ | ✅ | ❌ |
| ai_recommendations.approve | ❌ | Domain | Domain | Domain | ✅ | ✅ |
| decisions.create | ❌ | ✅ | ✅ | ✅ | ✅ | ✅ |

---

## 4. Row-Level Security (RLS)

### 4.1 PostgreSQL RLS Policies

```sql
-- Enable RLS on all business tables
ALTER TABLE work_orders ENABLE ROW LEVEL SECURITY;

-- Company isolation policy
CREATE POLICY work_orders_company_isolation ON work_orders
    FOR ALL
    USING (company_id = current_setting('app.company_id')::uuid);

-- Plant isolation policy (additive)
CREATE POLICY work_orders_plant_isolation ON work_orders
    FOR ALL
    USING (
        plant_id = current_setting('app.plant_id')::uuid
        OR current_setting('app.scope') = 'company'
        -- Company-level roles see all plants
    );

-- Set session variables from JWT context
-- (Done in application middleware)
SET app.company_id = '...';
SET app.plant_id = '...';
SET app.user_id = '...';
SET app.scope = 'plant'; -- or 'company', 'group'
```

### 4.2 Application-Level Data Scoping

```php
// Laravel Global Scope — auto-applied to all queries
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $context = app(WorkContext::class);
        
        $builder->where('company_id', $context->company_id);
        
        if ($context->scope === 'plant' && $model->hasPlantScope()) {
            $builder->where('plant_id', $context->plant_id);
        }
    }
}

// Applied automatically:
WorkOrder::all();  // → WHERE company_id = ? AND plant_id = ?
```

### 4.3 Financial Data Scoping

```php
// Finance data: must explicitly check financial permission
class FinancialDataPolicy
{
    public function viewDetail(User $user, FinancialRecord $record): bool
    {
        return $user->hasPermission('finance.view_sensitive')
            && $user->canAccessCompany($record->company_id)
            && $user->canAccessCostCenter($record->cost_center_id);
    }
}
```

---

## 5. Audit Trail System

### 5.1 Audit Log Architecture

```sql
-- Append-only audit log (PostgreSQL trigger-based)
CREATE TABLE audit_logs (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id      UUID NOT NULL,
    table_name      VARCHAR(100) NOT NULL,
    record_id       UUID NOT NULL,
    action          VARCHAR(10) NOT NULL,
    -- INSERT, UPDATE, DELETE
    changed_by      UUID NOT NULL,
    changed_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    user_context    JSONB NOT NULL,
    -- {company_id, plant_id, role, ip_address, user_agent}
    old_values      JSONB,
    new_values      JSONB,
    diff            JSONB,
    -- Only changed fields
    session_id      VARCHAR(100),
    request_id      VARCHAR(100)
);

-- Make immutable
CREATE RULE no_update_audit AS ON UPDATE TO audit_logs DO INSTEAD NOTHING;
CREATE RULE no_delete_audit AS ON DELETE TO audit_logs DO INSTEAD NOTHING;
```

### 5.2 What Gets Audited

```
ALWAYS audited (no exceptions):
  ✅ All financial transactions (journal_entries, payments, receipts)
  ✅ All inventory transactions (every movement)
  ✅ Work order status changes
  ✅ QC decisions (pass/fail/hold)
  ✅ Payroll runs and approvals
  ✅ User login / logout / context switch
  ✅ Permission changes
  ✅ AI actions and approvals
  ✅ Decision log entries
  ✅ Any approval/rejection

AUDITED by trigger:
  ✅ Master data changes (products, BOMs, routings, pricing)
  ✅ Configuration changes
  ✅ Role and permission assignments

NOT audited (to reduce noise):
  ❌ Read operations (viewing data)
  ❌ Failed login attempts (separate security log)
  ❌ Cache operations
```

### 5.3 Audit Log Retention

| Category | Retention | Notes |
|----------|-----------|-------|
| Financial | 10 năm | Theo luật kế toán |
| HR/Payroll | 7 năm | Theo luật lao động |
| Production | 5 năm | Theo yêu cầu khách hàng |
| Security | 3 năm | |
| AI actions | 7 năm | Trách nhiệm AI |
| System config | 3 năm | |

---

## 6. Data Encryption

### 6.1 At Rest

```
Database: PostgreSQL Transparent Data Encryption (TDE)
Sensitive fields: AES-256 column-level encryption
  - Employee ID numbers
  - Bank accounts
  - Tax codes
  - Salary details
File storage: S3 server-side encryption (AES-256)
Backups: Encrypted before leaving server
```

### 6.2 In Transit

```
All traffic: TLS 1.3 minimum
Certificate: Wildcard *.thuanduc.com
HSTS: max-age=31536000; includeSubDomains
Certificate pinning: Mobile apps

Internal service-to-service: mTLS
  - Laravel ↔ FastAPI AI Service
  - Laravel ↔ Redis (TLS)
  - Laravel ↔ PostgreSQL (SSL)
```

### 6.3 Key Management

```
Master encryption key: Stored in HSM or cloud KMS
  (AWS KMS / Azure Key Vault — not in application config)
Rotation: Every 12 months
  (Old records re-encrypted with new key)
Access to keys: Only application service accounts
Backup: Shamir's Secret Sharing (3-of-5 threshold)
```

---

## 7. AI-Specific Security

### 7.1 AI Guardrails (Hard Limits)

```python
BLOCKED_AI_ACTIONS = [
    "execute_payment",
    "approve_transaction",
    "delete_data",
    "modify_audit_log",
    "change_user_permissions",
    "access_other_tenant_data",
    "generate_employee_documents",
    "send_external_communications",
    # AI can DRAFT emails, but CANNOT SEND
]
```

### 7.2 Prompt Injection Prevention

```python
def sanitize_user_input(user_message: str) -> str:
    # Remove potential injection patterns
    dangerous_patterns = [
        r"ignore previous instructions",
        r"you are now",
        r"system prompt",
        r"forget everything",
        r"\<\|.*?\|\>",  # Token manipulation
    ]
    
    for pattern in dangerous_patterns:
        if re.search(pattern, user_message, re.IGNORECASE):
            raise SecurityException("Suspicious input detected")
    
    # Escape special characters
    return escape_llm_special_chars(user_message)
```

### 7.3 AI Output Validation

```python
async def validate_ai_output(output: str, context: WorkContext) -> str:
    # 1. Check for PII leakage
    if contains_employee_pii(output) and not context.can_view_pii:
        output = redact_pii(output)
    
    # 2. Check for cross-tenant data
    if contains_other_tenant_data(output, context.company_id):
        raise SecurityException("AI output contains cross-tenant data")
    
    # 3. Check for hallucinated financial figures
    financial_figures = extract_financial_figures(output)
    for figure in financial_figures:
        if not can_verify_figure(figure, context):
            output = add_disclaimer(output, "Số liệu cần được xác minh từ hệ thống")
    
    return output
```

### 7.4 AI Access Control

```
AI agents have scoped access:
  - Can ONLY call registered Tools
  - Tools are scoped to agent's company_id and plant_id
  - AI cannot escalate its own permissions
  - AI actions are recorded with agent_id, not user_id
  - Rate limit: 100 tool calls per run
  - Token budget per agent per day (configurable)
```

---

## 8. API Security

### 8.1 Input Validation

```php
// All API input validated before processing
class CreateWorkOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'plant_id'         => ['required', 'uuid', 'exists:plants,id'],
            'product_id'       => ['required', 'uuid', 'exists:products,id'],
            'planned_quantity' => ['required', 'numeric', 'min:0.0001', 'max:9999999'],
            'planned_start'    => ['required', 'date', 'after:now'],
            'planned_end'      => ['required', 'date', 'after:planned_start'],
            'priority'         => ['sometimes', 'integer', 'in:0,25,50,75'],
        ];
    }
    
    // Additional business rule validation
    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $this->validatePlantBelongsToCompany($v);
            $this->validateProductAvailable($v);
            $this->validateBomAndRouting($v);
        });
    }
}
```

### 8.2 SQL Injection Prevention

```php
// Always use Eloquent / Query Builder parameterized queries
// NEVER:
$users = DB::select("SELECT * FROM users WHERE name = '{$name}'");

// ALWAYS:
$users = User::where('name', $name)->get();
$users = DB::select("SELECT * FROM users WHERE name = ?", [$name]);
```

### 8.3 CSRF Protection

```
Web UI (Filament): Laravel CSRF token on all forms
API: Stateless JWT — no CSRF needed
Webhook endpoints: HMAC signature validation
```

---

## 9. Infrastructure Security

### 9.1 Network Security

```
Production Architecture:
  Internet → WAF (Web Application Firewall)
           → Load Balancer (TLS termination)
           → Application Servers (private network)
           → Database (private subnet, no public access)
           → Redis (private subnet)
           → AI Service (private network)

Firewall Rules:
  - Ingress: 443 (HTTPS) only from WAF
  - Database: Only from app servers (port 5432)
  - Redis: Only from app servers (port 6379)
  - AI Service: Only from app servers (port 8000)
```

### 9.2 WAF Rules

```
Block:
  - SQL injection patterns
  - XSS patterns
  - Path traversal attacks
  - Known malicious IPs
  - Countries not in allowlist (if applicable)

Rate limit at WAF:
  - 500 req/min per IP
  - 100 req/min for auth endpoints
```

### 9.3 Vulnerability Management

```
Dependency scanning: Daily (composer audit, npm audit)
SAST: On every PR (PHPStan, Psalm)
DAST: Weekly (OWASP ZAP)
Penetration test: Annual
Patch policy: Critical patches within 48h
```

---

## 10. Incident Response

### 10.1 Security Incident Classification

| Level | Ví dụ | Response Time |
|-------|-------|--------------|
| P1 Critical | Data breach, ransomware | 1 hour |
| P2 High | Unauthorized access, data leak | 4 hours |
| P3 Medium | Suspicious activity, failed auth spike | 24 hours |
| P4 Low | Policy violation, outdated dependencies | 1 week |

### 10.2 Incident Response Steps

```
1. DETECT    → Alert from monitoring / SIEM / user report
2. CONTAIN   → Isolate affected systems
3. ERADICATE → Remove threat
4. RECOVER   → Restore from clean backup
5. ANALYZE   → Root cause analysis
6. IMPROVE   → Update controls to prevent recurrence
7. REPORT    → Notify stakeholders, regulators if required
```
