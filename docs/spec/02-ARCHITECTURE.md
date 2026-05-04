# PHẦN 2 — SYSTEM ARCHITECTURE
## Kiến trúc Hệ thống 6 Tầng

---

## 1. Architecture Overview

### 1.1 Core Data Flow

```
External Data Sources
        │
        ▼
┌─────────────────────────────────────────────────────────┐
│  Layer 1: DATA FOUNDATION                               │
│  PostgreSQL + Redis + S3                                │
│  Single Source of Truth                                 │
└─────────────────────────┬───────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────┐
│  Layer 2: INTEGRATION & EVENT                           │
│  API Gateway + Event Bus + Message Queue                │
│  Webhook + Data Pipeline + Quality Gate                 │
└─────────────────────────┬───────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────┐
│  Layer 3: BUSINESS OPERATIONS                           │
│  Laravel 13 + Domain Services                           │
│  8 Business Domains (OPS/PEOPLE/MONEY/MARKET/...)       │
└──────────────┬──────────┴──────────────────────────────┘
               │                    │
               ▼                    ▼
┌──────────────────────┐  ┌──────────────────────────────┐
│  Layer 4: AI ENGINE  │  │  Layer 5: CONTROL TOWER      │
│  FastAPI + LLM       │  │  Filament v5 + Dashboards    │
│  Agent Workforce     │  │  KPI + Alerts + War Room     │
└──────────┬───────────┘  └──────────────┬───────────────┘
           │                             │
           └──────────────┬──────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────┐
│  Layer 6: ACTION & EXECUTION                            │
│  Workflow Engine + Ticket + SLA + CAPA + Audit          │
└─────────────────────────────────────────────────────────┘
```

### 1.2 Extended Data Flow

```
Data → Event → Processing → AI Analysis → Signal → Decision → Action → Feedback → Learning
```

---

## 2. Layer 1 — Data Foundation

### 2.1 Vai trò
Tạo **Single Source of Truth** cho toàn bộ hệ thống. Không có dữ liệu lõi nào tồn tại ở nhiều nơi.

### 2.2 Nguồn dữ liệu đầu vào

| Nguồn | Phương thức |
|-------|------------|
| ERP hiện hữu | API Sync / DB Connector |
| MES | REST API / MQTT |
| WMS | REST API |
| CRM | REST API |
| HRM | REST API |
| Kế toán | REST API / File Import |
| Máy chấm công | SDK / API |
| IoT / Cảm biến máy | MQTT / Webhook |
| File Excel | Import Pipeline |
| API đối tác | REST / EDI |
| Nhập liệu thủ công | UI Forms |
| AI tạo ra | Internal API |
| Audit / Compliance | Internal API |

### 2.3 Storage Architecture

```
PostgreSQL 16
  ├── Transactional Data (OLTP)
  ├── JSONB for flexible attributes
  ├── UUID primary keys
  └── Append-only ledger tables

Redis 7
  ├── Session store
  ├── Cache layer
  ├── Queue backend (Laravel Horizon)
  └── Realtime pub/sub

S3 / Object Storage
  ├── File attachments
  ├── QR code images
  ├── Reports
  └── AI embeddings backup
```

### 2.4 Data Principles

```
✅ Không trùng dữ liệu lõi — Master data có 1 nguồn
✅ Mã định danh chuẩn — UUID toàn hệ thống
✅ Nguồn gốc dữ liệu — Mọi record biết đến từ đâu
✅ Lịch sử thay đổi — Audit trail bất biến
✅ Trạng thái xác thực — data_quality_status
✅ Người chịu trách nhiệm — data_owner_id
✅ Versioning — Cho dữ liệu quan trọng (BOM, Routing, Pricing)
✅ Soft delete — Chỉ áp dụng cho Master Data
✅ Append-only — Bắt buộc cho Ledger / Transaction tables
```

---

## 3. Layer 2 — Integration & Event

### 3.1 Thành phần

```
API Gateway
  ├── Rate limiting
  ├── Auth validation (JWT)
  ├── Request routing
  └── Response caching

Webhook Gateway
  ├── Inbound webhook (nhận từ bên ngoài)
  ├── Outbound webhook (gửi ra bên ngoài)
  └── Retry + Dead Letter Queue

Event Bus (Laravel Events / Redis Pub-Sub)
  ├── Domain events
  ├── Integration events
  └── AI trigger events

Data Import Pipeline
  ├── Excel / CSV processor
  ├── Validation + transformation
  └── Staging → Production

Data Quality Gate
  ├── Schema validation
  ├── Business rule validation
  └── Duplicate detection
```

### 3.2 Event Catalog (Chuẩn)

#### Production Events
```
production.plan.created
production.plan.approved
production.plan.released
work_order.created
work_order.released
work_order.started
work_order.paused
work_order.completed
work_order.closed
machine.started
machine.stopped
machine.downtime.started
machine.downtime.ended
production.output.recorded
scrap.recorded
```

#### Quality Events
```
qc.inspection.started
qc.inspection.completed
qc.result.pass
qc.result.fail
qc.result.hold
qc.capa.created
qc.capa.closed
```

#### Inventory Events
```
inventory.receipt.created
inventory.receipt.confirmed
inventory.move.created
inventory.move.completed
inventory.issue.created
inventory.issue.confirmed
inventory.adjustment.created
lot.qc_status.changed
material.shortage.detected
```

#### Sales Events
```
lead.created
opportunity.created
quotation.created
quotation.approved
sales_order.created
sales_order.confirmed
sales_order.delivery.delayed
sales_order.completed
```

#### Finance Events
```
invoice.created
invoice.approved
payment.received
payment.made
cashflow.risk.detected
cost.variance.detected
```

#### AI Events
```
ai.analysis.completed
ai.recommendation.created
ai.recommendation.approved
ai.recommendation.rejected
ai.action.executed
ai.alert.raised
```

#### System Events
```
user.login
user.context.switched
approval.requested
approval.approved
approval.rejected
ticket.created
ticket.assigned
ticket.completed
sla.warning
sla.breached
escalation.triggered
```

### 3.3 Event Structure (Chuẩn)

```json
{
  "event_id": "uuid",
  "event_type": "work_order.completed",
  "version": "1.0",
  "occurred_at": "2026-05-04T08:00:00Z",
  "source": "mes-service",
  "tenant": {
    "company_id": "uuid",
    "plant_id": "uuid"
  },
  "actor": {
    "user_id": "uuid",
    "role": "production_supervisor"
  },
  "payload": {
    "work_order_id": "uuid",
    "product_id": "uuid",
    "completed_quantity": 1000.0,
    "scrap_quantity": 5.0
  },
  "metadata": {
    "correlation_id": "uuid",
    "causation_id": "uuid"
  }
}
```

---

## 4. Layer 3 — Business Operations

### 4.1 Domain Services Architecture

```
Laravel 13 Application
├── app/
│   ├── Domains/
│   │   ├── Operations/          ← OPS Domain
│   │   │   ├── Models/
│   │   │   ├── Services/
│   │   │   ├── Events/
│   │   │   └── Contracts/
│   │   ├── People/              ← PEOPLE Domain
│   │   ├── Money/               ← MONEY Domain
│   │   ├── Market/              ← MARKET Domain
│   │   ├── Governance/          ← GOV Domain
│   │   ├── Audit/               ← AUDIT Domain
│   │   ├── ESG/                 ← ESG Domain
│   │   └── AI/                  ← AI Domain
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Middleware/
│   ├── Filament/                ← Admin UI
│   └── Console/
```

### 4.2 Bounded Context Rules

Mỗi domain:
- Sở hữu dữ liệu của mình
- Giao tiếp qua Events hoặc Service Contracts
- Không query trực tiếp bảng của domain khác
- Có Repository riêng
- Có Service Layer riêng

---

## 5. Layer 4 — AI Intelligence

### 5.1 AI Architecture

```
FastAPI AI Service
├── Agent Runtime (LangChain / LangGraph)
├── Prompt Engine
├── Memory Engine (Vector DB)
├── Tool Registry (MCP Tools)
├── Guardrails Engine
└── Audit Logger
```

### 5.2 AI ↔ Backend Communication

```
Laravel Backend ←──REST API──→ FastAPI AI Service
      │                              │
      │ Events (Redis)               │ Tool Calls
      ▼                              ▼
  Event Bus                    Tool Registry
      │                              │
      └──────────── Trigger ─────────┘
```

---

## 6. Layer 5 — Control Tower

### 6.1 Dashboard Architecture

```
Filament v5 Application
├── Panels/
│   ├── ExecutivePanel       ← CEO/CFO/COO
│   ├── OperationsPanel      ← Plant Manager
│   ├── FinancePanel         ← Finance Director
│   ├── HRPanel              ← HR Manager
│   └── SystemPanel          ← Admin
├── Widgets/
│   ├── KPI Cards
│   ├── Alert List
│   ├── AI Recommendations
│   ├── Trend Charts
│   └── Drill-down Tables
└── Pages/
    ├── ControlTower
    ├── WarRoom
    └── DecisionLog
```

### 6.2 Real-time Updates

```
Browser ←──── WebSocket ─────← Laravel Reverb
                                      │
                              Redis Pub/Sub
                                      │
                              Event Processors
```

---

## 7. Layer 6 — Action & Execution

### 7.1 Workflow Engine

```
Trigger
  │
  ▼
Condition Evaluation
  │
  ├── True → Action Assignment
  │             │
  │             ▼
  │         Owner + SLA + Priority
  │             │
  │             ▼
  │         Notification
  │             │
  │             ▼
  │         SLA Countdown
  │             │
  │             ├── On Time → Complete
  │             └── Overdue → Escalation
  │
  └── False → No Action
```

### 7.2 Action Types

| Type | Ví dụ | Auto / Manual |
|------|-------|---------------|
| Create Ticket | Tạo phiếu xử lý | AI → Auto |
| Create CAPA | Tạo hành động khắc phục | Human |
| Send Notification | Gửi thông báo | Auto |
| Escalate | Leo thang lên cấp trên | Auto (SLA breach) |
| Log Decision | Ghi nhận quyết định | Human |
| Trigger Workflow | Kích hoạt quy trình | Auto / Human |

---

## 8. Technology Stack

### 8.1 Full Stack

| Layer | Technology | Version | Vai trò |
|-------|-----------|---------|---------|
| Backend Core | PHP + Laravel | 8.4 / 13.x | API, Business Logic, Queue |
| Admin UI | Filament | v5 | Control Tower, Admin |
| Frontend | Astro + TypeScript | Latest | Customer-facing portal |
| Mobile | Flutter | 3.x | Shopfloor, Sales mobile |
| Database | PostgreSQL | 16 | Primary data store |
| Cache | Redis | 7 | Cache, Queue, Pub/Sub |
| AI Runtime | Python + FastAPI | 3.12 / 0.100+ | AI Agent execution |
| LLM | Claude 4 / GPT-4 | Latest | Language model |
| Vector DB | Qdrant / pgvector | Latest | AI memory, embeddings |
| Realtime | Laravel Reverb | 1.x | WebSocket server |
| Queue | Laravel Horizon | 5.x | Job processing |
| Search | Meilisearch | Latest | Full-text search |
| Deploy | Docker + K8s | Latest | Container orchestration |
| CI/CD | GitHub Actions | - | Automated deployment |
| Monitoring | Grafana + Prometheus | Latest | Observability |
| Log | Loki / ELK | Latest | Log aggregation |

### 8.2 Infrastructure

```
Production Environment
├── Load Balancer (Nginx)
├── Web Servers (Laravel) × N pods
├── Queue Workers (Horizon) × N pods
├── AI Service (FastAPI) × N pods
├── WebSocket Server (Reverb) × N pods
├── PostgreSQL (Primary + Read Replicas)
├── Redis Cluster
└── S3 Compatible Storage
```

### 8.3 Development Stack

```
Local Development
├── Docker Compose
├── Laravel Sail
├── Vite (hot reload)
├── Pint (PHP formatter)
├── PHPStan (static analysis)
├── Pest (testing)
└── Cypress (E2E testing)
```

---

## 9. Security Architecture Overview

### 9.1 Authentication

```
User → Login → JWT Access Token (15min) + Refresh Token (7d)
             → Context Selection (company + plant + role)
             → Scoped JWT with context claims
```

### 9.2 Authorization Layers

```
Layer 1: Route-level (Middleware)
Layer 2: Policy-level (Laravel Policies)
Layer 3: Row-level (company_id / plant_id scoping)
Layer 4: Field-level (JSONB attribute masking)
```

### 9.3 Audit Trail

- Mọi action đều có audit log
- Audit log không thể cập nhật hoặc xóa
- Lưu: who, what, when, before, after, context

---

## 10. Non-Functional Requirements

| NFR | Target |
|-----|--------|
| API Response Time | p95 < 200ms |
| Dashboard Load | < 2 seconds |
| Uptime | 99.9% (8.7h downtime/year) |
| Data Freshness | < 30 seconds |
| Concurrent Users | 500+ |
| AI Response | < 5 seconds |
| Audit Log Retention | 7 năm |
| Backup RPO | 1 hour |
| Backup RTO | 4 hours |
| Data Encryption | AES-256 at rest, TLS 1.3 in transit |
