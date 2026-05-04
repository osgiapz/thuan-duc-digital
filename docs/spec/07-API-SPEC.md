# PHẦN 7 — API SPECIFICATION
## REST API + Event API Chuẩn Enterprise

---

## 1. API Design Principles

### 1.1 Core Standards

```
Standard: RESTful + JSON
Versioning: URI-based (/api/v1/, /api/v2/)
Auth: JWT Bearer Token + Context Claims
Rate Limiting: Per tenant + Per user
Response Format: Consistent JSON envelope
Error Format: RFC 7807 Problem Details
Pagination: Cursor-based (large datasets) + Offset (standard)
Filtering: Query parameters with validation
Sorting: sort_by + sort_direction
```

### 1.2 Base URL

```
Production:  https://api.factorymind.thuanduc.com/v1/
Staging:     https://api.staging.thuanduc.com/v1/
Local:       http://localhost:8000/api/v1/
```

---

## 2. Authentication

### 2.1 Login

```http
POST /api/v1/auth/login

Request:
{
    "email": "user@thuanduc.com",
    "password": "secret",
    "device_name": "Chrome/Windows"
}

Response 200:
{
    "access_token": "eyJ...",
    "refresh_token": "eyJ...",
    "token_type": "Bearer",
    "expires_in": 900,
    "user": {
        "id": "uuid",
        "display_name": "Nguyễn Văn A",
        "email": "user@thuanduc.com",
        "available_contexts": [
            {
                "company_id": "uuid",
                "company_name": "Thuận Đức Group",
                "plant_id": "uuid",
                "plant_name": "NM4",
                "role": "plant_manager",
                "permissions": ["work_orders.read", "work_orders.write"]
            }
        ]
    }
}
```

### 2.2 Switch Context

```http
POST /api/v1/auth/switch-context

Request:
{
    "company_id": "uuid",
    "plant_id": "uuid",
    "role": "plant_manager"
}

Response 200:
{
    "access_token": "eyJ...",
    "context": {
        "company_id": "uuid",
        "plant_id": "uuid",
        "role": "plant_manager"
    }
}
```

### 2.3 JWT Claims

```json
{
    "sub": "user-uuid",
    "email": "user@thuanduc.com",
    "ctx": {
        "cid": "company-uuid",
        "pid": "plant-uuid",
        "did": "department-uuid",
        "role": "plant_manager",
        "perms": ["work_orders.*", "inventory.read"]
    },
    "iat": 1746345600,
    "exp": 1746346500
}
```

---

## 3. API Naming Convention

### 3.1 URL Patterns

```
GET    /api/v1/{resources}                    List
POST   /api/v1/{resources}                    Create
GET    /api/v1/{resources}/{id}               Get One
PUT    /api/v1/{resources}/{id}               Full Update
PATCH  /api/v1/{resources}/{id}               Partial Update
DELETE /api/v1/{resources}/{id}               Delete (soft)

GET    /api/v1/{resources}/{id}/{sub-resources}         Sub-list
POST   /api/v1/{resources}/{id}/{action}                Action

Examples:
GET    /api/v1/work-orders
GET    /api/v1/work-orders/{id}
POST   /api/v1/work-orders
PATCH  /api/v1/work-orders/{id}
POST   /api/v1/work-orders/{id}/release
POST   /api/v1/work-orders/{id}/complete
GET    /api/v1/work-orders/{id}/operations
GET    /api/v1/work-orders/{id}/material-issues
```

### 3.2 Resource Naming

| Domain | Resource | URL Prefix |
|--------|---------|-----------|
| Organization | companies, plants, departments | /api/v1/ |
| Production | work-orders, production-plans | /api/v1/ |
| Inventory | lots, inventory-transactions, bins | /api/v1/ |
| Quality | qc-events, defects, capas | /api/v1/ |
| Finance | journal-entries, invoices, payments | /api/v1/ |
| HR | employees, attendance, payroll-runs | /api/v1/ |
| AI | ai-recommendations, ai-runs | /api/v1/ai/ |
| Control Tower | alerts, action-items, dashboards | /api/v1/ct/ |

---

## 4. Standard Response Format

### 4.1 Success Response

```json
{
    "success": true,
    "data": { ... },
    "meta": {
        "timestamp": "2026-05-04T08:00:00Z",
        "request_id": "req-uuid",
        "version": "1.0"
    }
}
```

### 4.2 List Response

```json
{
    "success": true,
    "data": [ ... ],
    "pagination": {
        "current_page": 1,
        "per_page": 25,
        "total": 248,
        "total_pages": 10,
        "next_cursor": "eyJ...",
        "prev_cursor": null
    },
    "meta": {
        "timestamp": "2026-05-04T08:00:00Z",
        "request_id": "req-uuid"
    }
}
```

### 4.3 Error Response (RFC 7807)

```json
{
    "success": false,
    "error": {
        "type": "https://factorymind.thuanduc.com/errors/validation",
        "title": "Validation Failed",
        "status": 422,
        "detail": "The request contains invalid fields",
        "instance": "/api/v1/work-orders",
        "errors": {
            "product_id": ["Product not found"],
            "planned_quantity": ["Must be greater than 0"]
        }
    },
    "meta": {
        "request_id": "req-uuid",
        "timestamp": "2026-05-04T08:00:00Z"
    }
}
```

### 4.4 HTTP Status Codes

| Status | Meaning |
|--------|---------|
| 200 | Success |
| 201 | Created |
| 204 | No Content (delete) |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden (no permission) |
| 404 | Not Found |
| 409 | Conflict (duplicate, state conflict) |
| 422 | Validation Error |
| 429 | Too Many Requests |
| 500 | Server Error |
| 503 | Service Unavailable |

---

## 5. Core API Endpoints

### 5.1 Work Orders API

```http
# List Work Orders
GET /api/v1/work-orders?
    status=in_progress,released&
    plant_id=uuid&
    date_from=2026-05-01&
    date_to=2026-05-07&
    sort_by=planned_start&
    sort_direction=asc&
    per_page=25

Response:
{
    "data": [
        {
            "id": "uuid",
            "wo_number": "WO-2026-089",
            "product": {
                "id": "uuid",
                "code": "P001",
                "name": "Tấm tráng 120cm"
            },
            "plant": {"id": "uuid", "name": "NM4"},
            "planned_quantity": 10000,
            "completed_quantity": 7800,
            "completion_pct": 78.0,
            "status": "in_progress",
            "planned_start": "2026-05-04T06:00:00Z",
            "planned_end": "2026-05-05T22:00:00Z",
            "actual_start": "2026-05-04T06:15:00Z",
            "is_delayed": false,
            "delay_hours": null,
            "linked_sales_order": {
                "id": "uuid",
                "so_number": "SO-2026-234",
                "customer": "Công ty XYZ",
                "delivery_date": "2026-05-06"
            }
        }
    ]
}

# Create Work Order
POST /api/v1/work-orders
{
    "plant_id": "uuid",
    "product_id": "uuid",
    "bom_id": "uuid",
    "routing_id": "uuid",
    "planned_quantity": 10000,
    "uom": "tấm",
    "planned_start": "2026-05-04T06:00:00",
    "planned_end": "2026-05-05T22:00:00",
    "sales_order_line_id": "uuid",
    "priority": 25,
    "notes": ""
}

# Release Work Order
POST /api/v1/work-orders/{id}/release
{}

# Record Production Output
POST /api/v1/work-orders/{id}/output
{
    "operation_id": "uuid",
    "completed_quantity": 500,
    "scrap_quantity": 5,
    "shift_id": "uuid",
    "machine_id": "uuid",
    "operator_id": "uuid",
    "recorded_at": "2026-05-04T10:00:00"
}

# Complete Work Order
POST /api/v1/work-orders/{id}/complete
{
    "completed_quantity": 9980,
    "scrap_quantity": 20,
    "notes": ""
}
```

### 5.2 Inventory API

```http
# Get Lot Details
GET /api/v1/lots/{id}

Response:
{
    "data": {
        "id": "uuid",
        "lot_number": "LOT-2026-1234",
        "product": { "code": "P001", "name": "Tấm tráng 120cm" },
        "quantity": 500,
        "available_qty": 380,
        "reserved_qty": 120,
        "uom": "tấm",
        "qc_status": "pass",
        "warehouse": { "code": "KHO-WIP", "name": "Kho WIP NM4" },
        "bin": { "code": "BIN-A05", "qr_code": "QR..." },
        "work_order_id": "uuid",
        "production_date": "2026-05-04",
        "transactions": [ ... ]
    }
}

# Move Lot (Transfer)
POST /api/v1/inventory/transfer
{
    "lot_id": "uuid",
    "quantity": 100,
    "from_bin_id": "uuid",
    "to_bin_id": "uuid",
    "notes": ""
}

# Issue Material to Work Order
POST /api/v1/inventory/issue
{
    "work_order_id": "uuid",
    "slip_id": "uuid",
    "lines": [
        {
            "lot_id": "uuid",
            "quantity": 50,
            "slip_line_id": "uuid"
        }
    ]
}
```

### 5.3 QC API

```http
# Record QC Result
POST /api/v1/qc-events
{
    "qc_type": "IPQC",
    "lot_id": "uuid",
    "work_order_id": "uuid",
    "inspected_at": "2026-05-04T09:00:00",
    "sample_size": 50,
    "pass_quantity": 48,
    "fail_quantity": 2,
    "result": "fail",
    "defects": [
        {
            "defect_type_id": "uuid",
            "quantity": 2,
            "severity": "major",
            "description": "Skew > 2mm"
        }
    ],
    "notes": ""
}

# Update Lot QC Status
PATCH /api/v1/lots/{id}/qc-status
{
    "qc_status": "fail",
    "reason": "Skew vượt mức cho phép",
    "qc_event_id": "uuid"
}
```

### 5.4 Finance API

```http
# Get P&L Summary
GET /api/v1/finance/pl-summary?
    company_id=uuid&
    period=2026-05&
    compare_to=budget

Response:
{
    "data": {
        "period": "2026-05",
        "revenue": {
            "actual": 4200000000,
            "budget": 4000000000,
            "variance": 200000000,
            "variance_pct": 5.0,
            "status": "green"
        },
        "cogs": { ... },
        "gross_profit": { ... },
        "operating_expenses": { ... },
        "ebitda": { ... },
        "net_profit": { ... }
    }
}

# Get Cashflow Forecast
GET /api/v1/finance/cashflow-forecast?
    company_id=uuid&
    weeks=13

# Get Cost Variances
GET /api/v1/finance/cost-variances?
    company_id=uuid&
    plant_id=uuid&
    period=2026-05&
    type=MPV,MUV
```

### 5.5 AI API

```http
# Trigger AI Analysis
POST /api/v1/ai/analyze
{
    "agent": "ceo_copilot",
    "trigger_type": "on_demand",
    "task": "daily_briefing",
    "context": {
        "date": "2026-05-04",
        "focus_areas": ["cashflow", "production", "top_risks"]
    }
}

Response:
{
    "data": {
        "run_id": "uuid",
        "status": "running",
        "estimated_seconds": 8,
        "result_url": "/api/v1/ai/runs/{run_id}"
    }
}

# Get AI Recommendations
GET /api/v1/ai/recommendations?
    domain=operations&
    status=pending&
    priority=high,critical&
    sort_by=created_at&
    sort_direction=desc

# Accept/Reject Recommendation
PATCH /api/v1/ai/recommendations/{id}
{
    "status": "accepted",
    "notes": "Đồng ý, đã tạo ticket WO-089"
}

# Ask AI (conversational)
POST /api/v1/ai/chat
{
    "agent": "ceo_copilot",
    "message": "Tại sao OEE NM4 giảm tuần này?",
    "session_id": "uuid"
}
```

### 5.6 Control Tower API

```http
# Get Dashboard Data
GET /api/v1/control-tower/dashboard?
    plant_id=uuid&
    period=today

Response:
{
    "data": {
        "kpis": {
            "revenue_mtd": { "value": 4200000000, "status": "green", "trend": "up", "pct": 5.0 },
            "oee": { "value": 0.783, "status": "amber", "trend": "down", "pct": -6.7 },
            "otif": { "value": 0.912, "status": "green", "trend": "flat", "pct": -3.8 },
            "cash_days": { "value": 42, "status": "amber", "trend": "down", "pct": -15.0 },
            "scrap_rate": { "value": 0.032, "status": "red", "trend": "up", "pct": 60.0 }
        },
        "open_alerts": 8,
        "critical_alerts": 2,
        "open_tickets": 12,
        "overdue_tickets": 3,
        "ai_recommendations_pending": 5
    }
}

# List Alerts
GET /api/v1/control-tower/alerts?
    status=open&
    severity=critical,high&
    sort_by=triggered_at

# Create Action Item
POST /api/v1/control-tower/action-items
{
    "title": "Sửa máy tráng số 2",
    "description": "...",
    "owner_id": "uuid",
    "priority": "critical",
    "due_at": "2026-05-04T17:30:00",
    "sla_hours": 4,
    "source_type": "alert",
    "source_id": "uuid"
}

# Log Decision
POST /api/v1/control-tower/decisions
{
    "title": "Chuyển WO-089 sang Line 4",
    "description": "...",
    "context_data": { "incident_id": "uuid", "options_evaluated": 3 },
    "decision_made": "Chuyển sang Line 4, chấp nhận delay 8h",
    "expected_outcome": "SO-234 giao trễ 1 ngày (06/05 → 07/05)",
    "decided_at": "2026-05-04T15:45:00"
}
```

---

## 6. Webhooks (Outbound)

### 6.1 Webhook Config

```http
POST /api/v1/webhooks
{
    "url": "https://partner.example.com/webhook",
    "events": ["work_order.completed", "sales_order.created"],
    "secret": "webhook-signing-secret",
    "is_active": true
}
```

### 6.2 Webhook Payload

```json
{
    "webhook_id": "uuid",
    "event": "work_order.completed",
    "occurred_at": "2026-05-04T14:30:00Z",
    "company_id": "uuid",
    "data": {
        "work_order_id": "uuid",
        "wo_number": "WO-2026-089",
        "product_id": "uuid",
        "completed_quantity": 9980,
        "scrap_quantity": 20,
        "completed_at": "2026-05-04T14:28:00Z"
    }
}
```

### 6.3 Webhook Signature

```
X-FactoryMind-Signature: sha256=abc123...
(HMAC-SHA256 of raw body using webhook secret)
```

---

## 7. Rate Limiting

```
Default limits:
  - 1000 requests/minute per tenant
  - 100 requests/minute per user
  - 10 requests/minute for AI endpoints

Headers returned:
  X-RateLimit-Limit: 1000
  X-RateLimit-Remaining: 987
  X-RateLimit-Reset: 1746345660

Rate limit exceeded → 429 Too Many Requests
  Retry-After: 45
```

---

## 8. Filtering & Pagination

### 8.1 Filter Syntax

```
Simple equality:        ?status=active
Multiple values:        ?status=active,inactive
Range:                  ?created_at_from=2026-05-01&created_at_to=2026-05-31
Contains (search):      ?name_contains=Thuận
Greater than:           ?oee_gte=0.8
Less than:              ?oee_lte=0.9
Nested:                 ?product.code=P001
```

### 8.2 Cursor Pagination (Large Datasets)

```
GET /api/v1/inventory-transactions?
    cursor=eyJ...&
    per_page=100

Response includes:
    "pagination": {
        "next_cursor": "eyJ...",
        "prev_cursor": "eyJ...",
        "has_more": true
    }
```

### 8.3 Include (Eager Loading)

```
GET /api/v1/work-orders/{id}?include=operations,material-issues,qc-events
```

---

## 9. API Security

### 9.1 Headers Required

```
Authorization: Bearer {access_token}
Content-Type: application/json
Accept: application/json
X-Request-ID: {client-generated-uuid}
X-App-Version: 1.0.0
```

### 9.2 CORS Policy

```
Allowed Origins: [configured per environment]
Allowed Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS
Allowed Headers: Authorization, Content-Type, Accept, X-Request-ID
Max Age: 86400
```

### 9.3 Field-Level Security

```
Sensitive fields (masked based on role):
  - Employee salary: only HR and above
  - Customer pricing: only sales and above
  - Financial data: only finance and above
  - AI prompt content: only system admin
```

---

## 10. API Versioning Strategy

```
/api/v1/ → Current stable
/api/v2/ → Next version (if breaking changes needed)

Deprecation policy:
  - Announce 6 months before deprecation
  - Keep v1 running 12 months after v2 launch
  - Sunset header: Sunset: Tue, 01 Jun 2027 00:00:00 GMT
```
