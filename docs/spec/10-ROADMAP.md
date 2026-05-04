# PHẦN 10 — MVP ROADMAP & GO-LIVE PLAN
## Lộ trình Triển khai, SaaS Packaging, Kế hoạch Go-live

---

## 1. Implementation Philosophy

### 1.1 Nguyên tắc triển khai

```
✅ Deliver value early and often — mỗi phase phải tạo ra giá trị ngay
✅ Data quality first — dữ liệu sai = AI sai = quyết định sai
✅ User adoption = system success — training là bắt buộc
✅ Process before automation — chuẩn hóa quy trình trước khi tự động hóa
✅ Pilot → Validate → Scale — không đổ toàn bộ cùng lúc
✅ KPI gắn với hệ thống — bắt buộc dùng mới tính lương
```

### 1.2 Pilot Strategy

```
Pilot Plant: NM4 (Nhà máy 4)
Lý do chọn NM4:
  - Quy trình tương đối chuẩn hóa
  - Ban quản lý nhà máy ủng hộ
  - Có đủ quy trình: Tráng + In + May + Kho
  - Quy mô đủ để test nhưng không quá lớn để fail

Sau NM4 thành công → Rollout sang NM1A → Toàn nhóm
```

---

## 2. MVP Roadmap — 4 Phases

### 2.1 Phase 1: FOUNDATION (Tuần 1–12)
**Theme: "Dữ liệu sống, Kho hoạt động"**

#### Scope

```
✅ IAM & Organization Setup
✅ Master Data (Product, BOM, Routing, Warehouse, Employee)
✅ WIP QR Bin Ledger (CORE)
✅ Basic Inventory (Receipt, Transfer, Issue)
✅ QC Gate (PASS/FAIL/HOLD)
✅ Basic Dashboard (KPI Cards)
```

#### Deliverables

| # | Deliverable | Week |
|---|-------------|------|
| 1 | Company + Plant + Organization structure setup | 1 |
| 2 | User accounts + Roles + Permissions | 1–2 |
| 3 | Master Data: Products, BOMs, Routings | 2–4 |
| 4 | Master Data: Warehouses, Bins, QR codes | 3–4 |
| 5 | Inventory Receipt + QC Gate | 5–6 |
| 6 | QR scan mobile app (basic) | 5–7 |
| 7 | WIP Transfer + Issue | 7–8 |
| 8 | Basic inventory dashboard | 9–10 |
| 9 | Data migration from current system | 8–11 |
| 10 | Training: Warehouse staff + QC | 10–11 |
| 11 | Go-live NM4 Kho + QC | 12 |

#### Success Criteria Phase 1

```
✅ 100% nguyên liệu vào kho đều có QR code
✅ 100% WIP di chuyển được scan
✅ QC Gate hoạt động — không có BTP FAIL đi qua được
✅ Tồn kho hệ thống vs thực tế sai lệch < 2%
✅ 0 trường hợp nhập liệu tay sau go-live
```

---

### 2.2 Phase 2: OPERATIONS (Tuần 13–24)
**Theme: "Sản xuất minh bạch, Đơn hàng đúng hẹn"**

#### Scope

```
✅ Production Planning (Work Orders + Schedule)
✅ MES — Work Order Execution
✅ Machine OEE Tracking
✅ Downtime Logging
✅ Basic Sales Order + ATP
✅ Delivery Tracking
✅ Production KPI Dashboard (OEE, OTIF, Scrap)
✅ Basic Alerts (Machine down, Scrap spike, Delivery risk)
```

#### Deliverables

| # | Deliverable | Week |
|---|-------------|------|
| 1 | Work Order creation + release | 13–14 |
| 2 | Production execution (Shopfloor app) | 14–16 |
| 3 | Machine runtime + downtime logging | 15–17 |
| 4 | OEE calculation + dashboard | 17–18 |
| 5 | Sales Order + basic ATP | 18–20 |
| 6 | Production Planning (manual + suggestion) | 19–21 |
| 7 | Delivery tracking | 20–22 |
| 8 | Alert rules: machine down, scrap, OTIF risk | 21–23 |
| 9 | Training: Production supervisors + Planning | 22–23 |
| 10 | Go-live NM4 Production | 24 |

#### Success Criteria Phase 2

```
✅ Tất cả WO đều có trạng thái cập nhật theo giờ
✅ OEE tính được theo ngày cho tất cả máy trong NM4
✅ Downtime log đầy đủ (không bỏ sót)
✅ OTIF tracking hoạt động cho 100% đơn hàng
✅ Cảnh báo delivery risk xuất hiện trước khi trễ ≥ 48h
```

---

### 2.3 Phase 3: FINANCE + HR (Tuần 25–36)
**Theme: "Giá thành thật, Lương minh bạch"**

#### Scope

```
✅ Dynamic Costing (từ production data → giá thành)
✅ Cost Variance Analysis (MPV, MUV, LEV)
✅ GL / Journal Entries (tự động từ production + inventory)
✅ AR / AP tracking
✅ Cashflow Forecast (13W)
✅ Employee Master + Attendance
✅ KPI Engine (gắn với sản xuất thật)
✅ Payroll 3P (P1+P2+P3)
✅ Finance + HR Dashboard
```

#### Deliverables

| # | Deliverable | Week |
|---|-------------|------|
| 1 | Chart of Accounts + Cost Centers setup | 25–26 |
| 2 | Inventory → Journal Entry auto-posting | 26–28 |
| 3 | Standard cost setup + WO cost calculation | 27–29 |
| 4 | Cost variance engine | 29–31 |
| 5 | AR tracking + aging | 30–32 |
| 6 | AP tracking + 3-way matching | 30–32 |
| 7 | 13-week cashflow forecast | 31–33 |
| 8 | Employee import + attendance integration | 26–28 |
| 9 | KPI definitions + assignment | 28–30 |
| 10 | Payroll 3P calculation engine | 30–33 |
| 11 | Finance + HR Dashboard | 33–35 |
| 12 | Training: Finance team + HR team | 34–35 |
| 13 | Go-live Finance + HR | 36 |

#### Success Criteria Phase 3

```
✅ Giá thành tính được trong vòng 24h sau khi WO complete
✅ Cost variance report available cuối mỗi tuần
✅ Cashflow forecast cập nhật hàng ngày
✅ Bảng lương P3 tính từ KPI hệ thống (không Excel)
✅ AR aging report tự động
```

---

### 2.4 Phase 4: AI + CONTROL TOWER (Tuần 37–52)
**Theme: "Điều hành bằng tín hiệu, AI tham gia vận hành"**

#### Scope

```
✅ Control Tower Dashboard (full)
✅ AI Agent Workforce (6 agents)
✅ Alert System (full rule engine)
✅ Workflow Engine + SLA + Escalation
✅ War Room Mode
✅ Decision Log
✅ Drill-down System
✅ Governance (OKR, Strategy, Risk Register)
✅ ESG Module (basic)
✅ Executive mobile app
```

#### Deliverables

| # | Deliverable | Week |
|---|-------------|------|
| 1 | Control Tower Dashboard | 37–40 |
| 2 | Alert Rule Engine | 38–41 |
| 3 | CEO Copilot Agent | 40–43 |
| 4 | CFO Agent + Costing Agent | 40–43 |
| 5 | Production Planner AI | 41–44 |
| 6 | Quality Agent + Inventory Agent | 42–45 |
| 7 | Workflow Engine + SLA + Escalation | 44–47 |
| 8 | Drill-down System | 46–48 |
| 9 | War Room Mode | 47–49 |
| 10 | OKR + Risk Register | 47–49 |
| 11 | ESG: Energy + Carbon basic | 48–50 |
| 12 | Executive Mobile App | 48–51 |
| 13 | Training: C-Level + Directors | 50–51 |
| 14 | Go-live: Full Control Tower | 52 |

#### Success Criteria Phase 4

```
✅ CEO dùng dashboard mỗi sáng thay vì báo cáo Excel
✅ AI recommendations acceptance rate > 60%
✅ Alert response time < 30 phút (vs > 2h hiện tại)
✅ SLA breach rate < 10%
✅ Drill-down trong ≤ 3 clicks hoạt động 100%
✅ War Room được dùng cho ít nhất 5 sự kiện thực tế
```

---

## 3. Post Go-live: Expansion

### 3.1 Rollout Plan

```
Phase 4 complete (NM4) → Q1 Year 2
  - NM1A: Phase 1 + 2 (2 months fast-track using NM4 learnings)

Q2 Year 2:
  - NM1A: Phase 3 + 4
  - Other plants: Phase 1

Q3 Year 2:
  - Full group rollout
  - Group-level Control Tower (consolidated)
  - Inter-company transactions

Q4 Year 2:
  - SaaS: First external pilot customer
  - ESG full module
  - Advanced AI (predictive maintenance, demand forecast)
```

---

## 4. Data Migration Strategy

### 4.1 Migration Approach

```
Phase 1 Migration:
  Source: Excel files + current system exports
  Target: FactoryMind Master Data + Opening Balances

Steps:
  1. DATA AUDIT
     - Inventory current system → spreadsheet
     - Products + BOMs → standardize codes
     - Employees → import from HR system
     
  2. DATA CLEANSING
     - Remove duplicates
     - Standardize codes (product, material, customer, supplier)
     - Validate BOMs completeness
     - Verify inventory counts (physical count before go-live)
     
  3. DATA TRANSFORMATION
     - Map old codes to new codes
     - Convert units to standard UoM
     - Calculate opening balances
     
  4. LOAD
     - Import via import pipelines (Excel upload)
     - Validate after import (auto-check scripts)
     - Parallel run 1 week (old system + new system)
     
  5. CUTOVER
     - Physical inventory count on cutover day
     - Enter opening balances
     - Switch to new system
     - Keep old system read-only for 3 months
```

### 4.2 Data Quality Gates

```
Before Phase 1 go-live:
  ✅ Product codes: unique, no duplicates
  ✅ BOM completeness: all active products have BOM
  ✅ Routing completeness: all active products have routing
  ✅ Warehouse structure: all bins have QR codes
  ✅ Employee data: all active employees imported
  ✅ Inventory: physical count matches system
  
Threshold:
  > 98% accuracy required before go-live
```

---

## 5. Change Management

### 5.1 Resistance Risk Assessment

| User Group | Resistance Level | Mitigation |
|-----------|-----------------|-----------|
| Warehouse staff | High (new process, QR scan) | Intensive training + buddy system |
| QC team | Medium (more visible, accountable) | Show how QC data protects them |
| Production supervisors | Medium (more transparent) | Tie KPI to system use |
| Finance team | Low (less manual work) | Early engagement, co-design |
| HR team | Low | Early engagement |
| C-Level | Low (they benefit most) | Demo early wins |

### 5.2 Training Plan

```
Training Approach: Role-based, hands-on, in Vietnamese

Warehouse Staff (QR Operations):
  - 1-day hands-on workshop
  - QR scan simulation exercises
  - "What to do when..." playbook

Production Supervisors:
  - 1-day workshop: Work Orders, Downtime Log, Shopfloor App
  - 1-day workshop: KPI understanding, P3 Payroll impact

QC Team:
  - Half-day: QC Gate process, recording defects
  - Half-day: CAPA process

Finance Team:
  - 2-day workshop: Costing, AR/AP, Cashflow
  - 1-day: Reporting and exports

Plant Manager / Directors:
  - 1-day: Control Tower, Drill-down, AI Recommendations
  - 1-day: War Room simulation exercise

C-Level (CEO, CFO, etc.):
  - Half-day: Executive Dashboard, AI Briefing
  - Ongoing: Monthly AI optimization session
```

### 5.3 KPI Linkage (Bắt buộc)

```
Phase 1 go-live requirement:
  - Nhân viên kho: Chấm công + P3 tính từ FactoryMind
  - QC: Kết quả kiểm tra phải trong FactoryMind
    (nếu không có trong hệ thống = không có)
    
Phase 2 go-live requirement:
  - Supervisor: OEE phải cập nhật trong ngày
  - Operator: P3 tính từ sản lượng trong FactoryMind
  
This ensures adoption — if they don't use it, they don't get paid.
```

---

## 6. SaaS Product Packaging

### 6.1 Product Tiers

#### TIER 1: Control Tower Lite
**Dành cho:** Doanh nghiệp vừa muốn bắt đầu với AI-powered dashboard

```
Modules:
  ✅ Organization setup (1 company, 3 plants)
  ✅ Basic KPI Dashboard
  ✅ Alert Rules (up to 20)
  ✅ Action Items + SLA
  ✅ Basic AI Briefing (1 agent: CEO Copilot)
  ✅ Mobile app (read-only)

Limits:
  - 10 users
  - 3 plants
  - 6 months data history

Price: 15,000,000 VND/month
```

#### TIER 2: Factory Ops
**Dành cho:** Nhà máy sản xuất muốn số hóa vận hành

```
Modules:
  ✅ Everything in Lite
  ✅ Master Data (full)
  ✅ WIP QR Bin Ledger
  ✅ MES (Work Orders, Operations)
  ✅ Inventory Management
  ✅ QC Gate + CAPA
  ✅ OEE Tracking
  ✅ Shopfloor Mobile App
  ✅ AI: Production Planner + Quality Agent

Limits:
  - 50 users
  - 5 plants
  - 2 years data history

Price: 45,000,000 VND/month
```

#### TIER 3: AI CFO
**Dành cho:** CFO + Finance team muốn AI-powered financial control

```
Modules:
  ✅ Everything in Factory Ops
  ✅ Dynamic Costing Engine
  ✅ GL / AR / AP
  ✅ 13-Week Cashflow Forecast
  ✅ Cost Variance Analysis
  ✅ Payroll 3P
  ✅ AI: CFO Agent + Costing Agent + Risk Agent

Limits:
  - 100 users
  - 10 plants
  - 5 years data history

Price: 85,000,000 VND/month
```

#### TIER 4: Full Industrial AI OS
**Dành cho:** Tập đoàn sản xuất muốn full digital transformation

```
Modules:
  ✅ Everything
  ✅ All AI Agents (8 agents)
  ✅ Control Tower (full)
  ✅ Governance (OKR, Risk, Board Pack)
  ✅ Audit Module
  ✅ ESG + CBAM + BSCI
  ✅ Multi-company (tập đoàn)
  ✅ API access (full)
  ✅ Custom integrations
  ✅ Dedicated Customer Success Manager
  ✅ SLA 99.9%

Limits:
  - Unlimited users
  - Unlimited plants
  - Unlimited data history (tiered storage)

Price: Custom (500M–2B VND/year)
```

### 6.2 Deployment Options

| Option | Mô tả | Dành cho | Premium |
|--------|-------|---------|---------|
| Cloud SaaS | Hosted by Thuận Đức | SME, Mid-market | Included |
| Dedicated Cloud | Single-tenant cloud | Large enterprise | +30% |
| On-premise | Customer's own servers | Government, finance, large MNC | +50% |
| Hybrid | Core in cloud + sensitive data on-premise | Banks, govt partners | Custom |

### 6.3 Target Markets

```
Priority 1 — Domestic Manufacturing (Vietnam):
  - Plastic + packaging manufacturers
  - Textile + garment manufacturers
  - Electronics assembly
  - Food + beverage
  Target: 20 customers Year 1, 100 Year 3

Priority 2 — Regional Expansion:
  - Vietnam → Myanmar, Cambodia, Indonesia
  - Same industry focus
  - Local language support (5 languages)
  Target: Year 3–5

Priority 3 — Vertical Depth:
  - Build deep features for top 2 verticals
  - Become vertical market leader
```

---

## 7. Technology Evolution Roadmap

### 7.1 Year 1 — Foundation

```
✅ Core platform running on NM4
✅ 6 AI agents operational
✅ First 3 external SaaS customers
```

### 7.2 Year 2 — Growth

```
🔲 Predictive Maintenance (IoT + ML)
🔲 Demand Forecasting AI (time series)
🔲 Advanced ESG (full CBAM certification)
🔲 Mobile-first redesign
🔲 Integration marketplace (SAP, Oracle ERP connectors)
🔲 50 SaaS customers
```

### 7.3 Year 3 — Scale

```
🔲 Computer Vision QC (camera-based defect detection)
🔲 Voice interface (shopfloor hands-free)
🔲 Autonomous scheduling (AI auto-schedules with guardrails)
🔲 Industry benchmark database (anonymized)
🔲 Regional offices (Singapore hub for SEA)
🔲 200 SaaS customers
```

---

## 8. Investment & Resource Plan

### 8.1 Team Required for MVP

| Role | Count | Phase 1-2 | Phase 3-4 |
|------|-------|-----------|-----------|
| Solution Architect | 1 | ✅ | ✅ |
| Backend Lead (Laravel) | 1 | ✅ | ✅ |
| Backend Dev | 2 | ✅ | ✅ |
| Frontend Dev (Filament) | 2 | ✅ | ✅ |
| Mobile Dev (Flutter) | 1 | ✅ | ✅ |
| AI Engineer (Python) | 1 | - | ✅ |
| Data Engineer | 1 | ✅ | ✅ |
| QA Engineer | 1 | ✅ | ✅ |
| DevOps | 1 | ✅ | ✅ |
| BA / PM | 1 | ✅ | ✅ |
| UX Designer | 1 | ✅ | ✅ |
| **Total** | **13** | | |

### 8.2 Infrastructure Cost Estimate (Monthly)

| Component | Cost/Month |
|-----------|-----------|
| Kubernetes cluster (production) | ~5,000,000 VND |
| PostgreSQL RDS | ~3,000,000 VND |
| Redis cluster | ~1,500,000 VND |
| Load balancer + CDN | ~500,000 VND |
| AI API costs (LLM) | ~2,000,000 VND |
| Monitoring + logging | ~1,000,000 VND |
| Backup + storage | ~500,000 VND |
| **Total** | **~13,500,000 VND/month** |

---

## 9. Final Assessment

### 9.1 System Completeness

| Layer | Status | Notes |
|-------|--------|-------|
| Business Domains (8) | ✅ Complete | OPS, PEOPLE, MONEY, MARKET, GOV, AUDIT, ESG, AI |
| Architecture (6 layers) | ✅ Complete | Data → Event → Ops → AI → Control Tower → Action |
| Database (120+ tables) | ✅ Complete | 10 migration phases |
| AI Agent System | ✅ Complete | 8 agents, governance, guardrails |
| Control Tower UI | ✅ Complete | Filament v5, drill-down, war room |
| API Specification | ✅ Complete | REST + Events + Webhooks |
| Security | ✅ Complete | RBAC, RLS, AI guardrails, audit |
| Deployment | ✅ Complete | Docker → K8s, CI/CD, observability |
| SaaS Model | ✅ Complete | 4 tiers, pricing, go-to-market |
| Go-live Roadmap | ✅ Complete | 4 phases, 52 weeks, change management |

### 9.2 Conclusion

```
Hệ thống Thuận Đức AI Enterprise Group Control Tower OS

Sau khi hoàn thiện toàn bộ Spec này:

👉 Không còn là ý tưởng hay mô tả khái niệm.

👉 Đây là Enterprise System Specification cấp Production,
   đủ để BA viết BRD, Architect thiết kế solution,
   Dev bắt đầu code, QA viết test cases,
   và PM lập kế hoạch sprint ngay hôm nay.

👉 Hệ thống này đủ điều kiện để:
   ✅ Vận hành nội bộ Thuận Đức (NM4 → toàn tập đoàn)
   ✅ Thương mại hóa thành SaaS B2B cạnh tranh quốc tế
   ✅ Định giá thị trường: 500M – 2B VND/năm/khách hàng enterprise
```

---

## 10. Next Steps — Choose Your Path

### OPTION A — Backend First (Recommended)
```
→ Setup Laravel 13 project với Filament v5
→ Implement Phase 1 migrations (IAM + Org + Master Data)
→ Build WIP QR Bin Ledger service
→ API for QR mobile scanning
→ Basic dashboard

Timeline: 4–6 tuần đến Phase 1 MVP
```

### OPTION B — UI Prototype First
```
→ Build Filament v5 Control Tower UI
→ Seed data NM4 (demo data)
→ Demo cho ban lãnh đạo Thuận Đức
→ Validate UX trước khi code backend đầy đủ

Timeline: 2–3 tuần đến prototype demo
```

### OPTION C — AI Spike First
```
→ Setup FastAPI AI service
→ CEO Copilot prototype với mock data
→ Validate AI approach và model selection
→ Measure cost per recommendation

Timeline: 1–2 tuần đến AI proof-of-concept
```
