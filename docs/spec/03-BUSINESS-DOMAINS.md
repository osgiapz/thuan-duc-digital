# PHẦN 3 — BUSINESS DOMAIN SPECIFICATION
## Đặc tả Chi tiết 8 Khối Nghiệp vụ Lõi

---

## Domain Map

```
┌─────────────────────────────────────────────────────────────────┐
│                    FactoryMind AI OS                            │
├──────────┬──────────┬──────────┬──────────┬────────────────────┤
│   OPS    │  PEOPLE  │  MONEY   │  MARKET  │     GOVERNANCE     │
│Operations│   HR+KPI │ Finance  │   CRM    │  Strategy + Risk   │
├──────────┴──────────┴──────────┴──────────┴────────────────────┤
│              AUDIT          │           ESG                    │
│     Kiểm toán nội bộ        │  Carbon + Compliance             │
├─────────────────────────────┴──────────────────────────────────┤
│                    AI AGENTIC LAYER                            │
│              Multi-Agent Workforce                             │
└─────────────────────────────────────────────────────────────────┘
```

---

## DOMAIN 1: OPERATIONS (OPS)
### Sản xuất, Kho vận, Chuỗi cung ứng

---

### 1.1 Mục tiêu

Quản trị toàn bộ vòng đời sản xuất từ kế hoạch đến giao hàng:

```
Sales Demand → Planning → Work Order → Production → QC → Warehouse → Delivery
```

### 1.2 Sub-modules

| Module | Mô tả | Priority |
|--------|-------|----------|
| Production Planning | Lập kế hoạch sản xuất (APS + MRP) | P0 |
| MES | Manufacturing Execution System | P0 |
| WIP QR Bin Ledger | Quản lý bán thành phẩm bằng QR | P0 |
| Inventory Management | Kho nguyên liệu + thành phẩm | P0 |
| Quality Control | Kiểm soát chất lượng + QC Gate | P0 |
| Subcontract | Quản lý gia công ngoài | P1 |
| OEE Tracking | Đo hiệu suất máy móc | P1 |

---

### 1.3 Production Planning

#### 1.3.1 Production Modes

| Mode | Khi nào dùng | Đặc điểm |
|------|-------------|----------|
| Make-to-Order (MTO) | Đơn hàng đặc chủng | Sản xuất theo đơn, tồn kho thấp |
| Make-to-Stock (MTS) | Hàng standard | Sản xuất đại trà, tồn kho cao |
| Hybrid | Phổ biến tại Thuận Đức | Gom đơn + MTS cho hàng phổ thông |

#### 1.3.2 MRP Process

```
Sales Orders + Forecast
        │
        ▼
   Nổ BOM (Bill of Materials)
        │
        ▼
   Kiểm tra tồn kho hiện có
        │
        ▼
   Tính nhu cầu thuần
        │
        ├── Nguyên liệu còn thiếu → Purchase Request
        └── Năng lực máy kiểm tra (APS)
                │
                ▼
           Work Order Schedule
```

#### 1.3.3 APS (Advanced Planning & Scheduling)

- Kiểm tra tải máy (machine capacity) theo từng ca
- Kiểm tra nhân lực theo shift
- Tối ưu lịch sản xuất để giảm changeover time
- Cảnh báo bottleneck trước khi xảy ra
- ATP (Available-to-Promise): cam kết ngày giao hàng thực tế
- CTP (Capable-to-Promise): tính ngày giao dựa trên năng lực thực

#### 1.3.4 Planning Outputs

```
Production Plan
  └── Work Orders
        ├── Material Reservations
        ├── Machine Allocations
        └── Labor Allocations
```

---

### 1.4 MES — Manufacturing Execution System

#### 1.4.1 Work Order Lifecycle

```
planned → released → in_progress → paused → completed → closed
                                       │
                               (material shortage
                                or machine down)
```

#### 1.4.2 Work Order Structure

```
Work Order
├── Product + Quantity
├── BOM (snapshot tại thời điểm tạo)
├── Routing (snapshot)
├── Operations[]
│   ├── Operation 1 (Dệt)
│   │   ├── Machine: Line 01
│   │   ├── Duration: 8h
│   │   └── Output: BTP-DT
│   ├── Operation 2 (Tráng)
│   └── Operation 3 (In)
├── Material Issues[]
├── QC Events[]
└── Output Records[]
```

#### 1.4.3 Machine & OEE Tracking

| Chỉ số OEE | Công thức | Mục tiêu |
|-----------|----------|---------|
| Availability | Runtime / Planned Time | ≥ 90% |
| Performance | Actual Output / Theoretical Output | ≥ 95% |
| Quality | Good Output / Total Output | ≥ 98% |
| **OEE** | **A × P × Q** | **≥ 85%** |

#### 1.4.4 Downtime Classification

| Code | Loại | Ví dụ |
|------|------|-------|
| DT-MECH | Mechanical | Hỏng cơ khí |
| DT-ELEC | Electrical | Hỏng điện |
| DT-SETUP | Setup/Changeover | Thay khuôn |
| DT-MAT | Material Wait | Chờ nguyên liệu |
| DT-PLAN | Planned | Bảo trì định kỳ |
| DT-QUAL | Quality Issue | Điều chỉnh lỗi |

---

### 1.5 WIP QR BIN LEDGER *(Critical Module)*

#### 1.5.1 Nguyên lý cốt lõi

> **Không scan QR = Không tồn tại trong hệ thống**

Mọi bán thành phẩm phải:
1. Có QR Code dán trên thùng/tấm/cuộn
2. Mọi di chuyển phải scan QR
3. QC Gate phải PASS trước khi đi tiếp

#### 1.5.2 Bin Structure

```
Warehouse (NM4-KHO-01)
  └── Zone (ZONE-WIP)
        └── Rack (RACK-A)
              └── Bin (BIN-A01) ← QR Code
                    └── Lot(s) ← QR Code
```

#### 1.5.3 WIP Flow

```
RECEIPT (từ công đoạn trước hoặc nhập kho)
    │
    ▼
QC INSPECTION
    ├── FAIL  → QUARANTINE BIN → CAPA
    ├── HOLD  → HOLD BIN → Re-inspection
    └── PASS  ↓
              │
         STORE IN BIN
              │
              ▼
         TRANSFER (di chuyển giữa Bin/Zone)
              │
              ▼
         ISSUE (cấp phát cho lệnh sản xuất)
              │
              ▼
         CONSUMED (đã dùng vào sản phẩm)
```

#### 1.5.4 QC Gate — Hard Block

```php
// Rule: Không được phép ISSUE nếu QC chưa PASS
if ($lot->qc_status !== 'pass') {
    throw new QCBlockException("Lot {$lot->code} chưa qua QC Gate");
}
```

| QC Status | Được phép đi tiếp | Hành động |
|-----------|------------------|-----------|
| `pending` | ❌ Blocked | Chờ kiểm tra |
| `in_inspection` | ❌ Blocked | Đang kiểm tra |
| `hold` | ❌ Blocked | Re-inspection |
| `fail` | ❌ Blocked | CAPA bắt buộc |
| `pass` | ✅ Allowed | Tiếp tục quy trình |

#### 1.5.5 Inventory Transaction Types

| Type | Mô tả | Direction |
|------|-------|-----------|
| `receipt` | Nhận hàng | IN |
| `transfer` | Di chuyển nội bộ | IN/OUT |
| `issue` | Cấp phát cho WO | OUT |
| `return` | Trả lại kho | IN |
| `adjustment` | Điều chỉnh kiểm kê | +/- |
| `scrap` | Ghi nhận phế phẩm | OUT |
| `sub_out` | Xuất gia công | OUT |
| `sub_in` | Nhận về sau gia công | IN |

---

### 1.6 Subcontract Management

#### 1.6.1 Flow

```
SUB_OUT Order Created
    │
    ▼
Material Issued to Vendor
    │
    ▼
Vendor Processes
    │
    ▼
SUB_IN Receipt (QR scan)
    │
    ▼
QC Inspection
    │
    ▼
RECONCILE (hao hụt, chênh lệch)
    │
    ▼
Cost Allocation
```

#### 1.6.2 Kiểm soát gia công

- Hao hụt định mức vs thực tế
- Chất lượng đầu vào / đầu ra
- Lead time cam kết vs thực tế
- Chi phí gia công theo lô
- Vendor performance score

---

### 1.7 Quality Control

#### 1.7.1 QC Levels

| Level | Điểm kiểm tra | Ai kiểm |
|-------|--------------|---------|
| IQC | Incoming (nhận NVL) | QC Inbound |
| IPQC | In-process (đang sản xuất) | QC Line |
| OQC | Outgoing (trước giao hàng) | QC Final |
| FQC | Final (kho thành phẩm) | QC Final |

#### 1.7.2 Defect Management

```
Defect Recorded
    │
    ├── Severity: Minor → Log only
    ├── Severity: Major → Stop Line + Alert
    └── Severity: Critical → Work Order Hold + Escalate
                                    │
                                    ▼
                               CAPA Created (mandatory)
```

---

## DOMAIN 2: PEOPLE
### Nhân sự, Chấm công, KPI, Lương 3P

---

### 2.1 Mục tiêu

- Quản lý nhân sự toàn tập đoàn từ tuyển dụng đến nghỉ việc
- Gắn KPI trực tiếp với hiệu suất sản xuất và tài chính
- Tính lương realtime dựa trên hiệu suất thực tế (Payroll 3P)

### 2.2 HR Core

#### Employee Lifecycle

```
Recruit → Onboard → Active → Transfer → Promote → Terminate
```

#### Employee Master Data

```
Employee
├── Personal Info
├── Position + Job Title
├── Company + Plant + Department
├── Contract Type (full-time, contract, probation)
├── Shift Assignment
├── KPI Assignments
└── Payroll Config (P1 + P2 + P3 setup)
```

### 2.3 Attendance System

#### Multi-source Integration

| Nguồn | Phương thức |
|-------|------------|
| Máy chấm công biometric | SDK / API |
| App Mobile | GPS + Face ID |
| QR Code gate | Scan at entry/exit |
| Manual override | Supervisor approval |

#### Attendance Processing

```
Raw Attendance Events
    │
    ▼
Shift Matching (so sánh với lịch ca)
    │
    ▼
Exception Detection
    ├── Late arrival
    ├── Early departure
    ├── Absent
    └── Overtime
    │
    ▼
Daily Attendance Record (locked after 24h)
    │
    ▼
Monthly Summary (input for Payroll)
```

### 2.4 KPI Engine

#### 2.4.1 KPI Hierarchy

```
Company KPI
    └── Plant KPI
            └── Department KPI
                    └── Workshop KPI
                            └── Individual KPI
```

#### 2.4.2 KPI Types

| Type | Ví dụ | Source |
|------|-------|--------|
| Production | OEE, Output, Scrap Rate | MES |
| Quality | Defect Rate, COPQ | QC System |
| Delivery | OTIF (On-Time In-Full) | WMS / Sales |
| Finance | Cost per Unit, Variance | Costing Engine |
| HR | Attendance Rate, Turnover | HR System |
| Safety | Near Miss, Incident | Safety Module |

#### 2.4.3 KPI Calculation Frequency

| Frequency | Trigger | Audience |
|-----------|---------|---------|
| Realtime | Machine event | Supervisor |
| Hourly | Cron job | Line Manager |
| Daily | EOD batch | Plant Manager |
| Weekly | Weekly batch | Director |
| Monthly | Month-end | C-Level |

#### 2.4.4 KPI Status Logic

```
Actual vs Target:
  ≥ 100%      → 🟢 Green
  80% – 99%   → 🟡 Amber (Warning)
  < 80%       → 🔴 Red   (Alert + Action Required)
```

### 2.5 Payroll 3P Engine

#### 2.5.1 Formula

```
Monthly Salary = P1 + P2 + P3

P1 = Position Salary       (Fixed — theo chức danh)
P2 = Person Coefficient    (Semi-fixed — theo năng lực cá nhân)
P3 = Performance Payment   (Variable — theo KPI tháng)
```

#### 2.5.2 P3 Calculation

```
P3_Base = Salary_Fund × Department_KPI_Achievement

Individual_P3 = P3_Base × (Individual_KPI_Score / Team_KPI_Average)

Các KPI đầu vào cho P3:
  - Sản lượng đạt (%)
  - Tỷ lệ phế phẩm (%)
  - OTIF (%)
  - Chấm công (%)
  - An toàn lao động (%)
```

#### 2.5.3 Payroll Processing

```
KPI Data + Attendance Data
    │
    ▼
Payroll Calculation Engine
    │
    ▼
Deductions (BHXH, BHYT, BHTN, PIT)
    │
    ▼
Net Pay Calculation
    │
    ▼
Manager Review + Approval
    │
    ▼
Payment Processing (bank file)
    │
    ▼
Payslip Generation
    │
    ▼
Tax Report (PIT)
```

---

## DOMAIN 3: MONEY
### Tài chính, Giá thành, Dòng tiền

---

### 3.1 Mục tiêu

- Tính giá thành thực tế theo thời gian thực (không chờ cuối tháng)
- Kiểm soát dòng tiền 13 tuần rolling
- Kết nối trực tiếp với dữ liệu sản xuất (không nhập liệu tay)

### 3.2 Chart of Accounts

```
Assets (1xx)
  ├── 111: Cash
  ├── 112: Bank
  ├── 131: AR
  ├── 152: Raw Materials
  ├── 154: WIP
  └── 155: Finished Goods

Liabilities (3xx)
  ├── 331: AP
  └── 341: Loans

Equity (4xx)

Revenue (5xx)
  └── 511: Sales Revenue

COGS (6xx)
  ├── 621: Direct Material
  ├── 622: Direct Labor
  └── 627: Manufacturing Overhead

Expenses (6xx/8xx)
```

### 3.3 Dynamic Costing Engine

#### 3.3.1 Cost Components

| Component | Mã | Source | Frequency |
|-----------|-----|--------|-----------|
| Direct Material | DM | Inventory Issues | Realtime |
| Direct Labor | DL | Attendance + KPI | Daily |
| Machine Cost | MC | Runtime Logs | Daily |
| Overhead | OH | Allocation Formula | Monthly |
| Subcontract | SC | Sub Invoices | Per batch |
| Quality Cost | QC | Defect + CAPA | Realtime |

#### 3.3.2 Cost Flow

```
Material Receipt (giá PO) → Inventory Valuation
    │
    ▼
Material Issue to Work Order → WIP Cost
    │
    ▼
Production Completion → Cost of Goods Manufactured
    │
    ▼
Product Transfer to FG → Inventory Valuation (COGM)
    │
    ▼
Sales Order Delivery → Cost of Goods Sold (COGS)
    │
    ▼
P&L Impact
```

#### 3.3.3 Variance Analysis

| Variance | Công thức | Ý nghĩa |
|----------|----------|---------|
| **MPV** — Material Price Variance | (Actual Price − Standard Price) × Actual Qty | Mua đắt/rẻ hơn định mức |
| **MUV** — Material Usage Variance | (Actual Qty − Standard Qty) × Standard Price | Dùng nhiều/ít hơn định mức |
| **LEV** — Labor Efficiency Variance | (Actual Hours − Standard Hours) × Standard Rate | Lao động hiệu quả hơn/kém |
| **LRV** — Labor Rate Variance | (Actual Rate − Standard Rate) × Actual Hours | Trả lương nhiều/ít hơn |
| **OHV** — Overhead Variance | Actual OH − Absorbed OH | Phân bổ overhead |

#### 3.3.4 Standard Cost Update Cycle

- Standard cost review: **Monthly** (hoặc khi giá NVL biến động > 5%)
- Variance booking: **Daily** (tự động từ production data)
- Variance report: **Weekly** to Plant Manager, **Monthly** to CFO

### 3.4 Accounts Receivable (AR)

```
Sales Order Confirmed
    │
    ▼
Delivery Completed
    │
    ▼
Invoice Generated (auto từ SO)
    │
    ▼
Invoice Approved + Sent to Customer
    │
    ▼
Payment Due Date Tracking
    │
    ├── Overdue → Alert + Collection Workflow
    └── Received → Cash Application + Bank Reconciliation
```

### 3.5 Accounts Payable (AP)

```
Purchase Order Approved
    │
    ▼
Goods Receipt Confirmed
    │
    ▼
Vendor Invoice Matched (3-way matching: PO + GR + Invoice)
    │
    ├── Matched → Approve for Payment
    └── Mismatch → Hold + Investigation
          │
          ▼
Payment Scheduling (theo điều khoản)
    │
    ▼
Payment Run + Bank File
```

### 3.6 Cashflow Forecast (13 Weeks)

#### 3.6.1 Inputs

```
Inflow Forecast:
  - AR due schedule (confirmed invoices)
  - Expected sales (sales pipeline × conversion rate)
  - Other income

Outflow Forecast:
  - AP due schedule (confirmed POs)
  - Payroll schedule
  - Tax payments
  - Loan repayments
  - Capex
  - Operating expenses
```

#### 3.6.2 Stress Testing

| Scenario | Điều chỉnh |
|----------|-----------|
| Base Case | Dự báo chuẩn |
| Pessimistic | AR chậm 30 ngày, Sales giảm 20% |
| Optimistic | AR đúng hạn, Sales tăng 10% |
| Stress | AR chậm 60 ngày, Sales giảm 40% |

#### 3.6.3 Cashflow Alert Thresholds

```
Available Cash < 30 ngày operating expense → 🔴 Critical
Available Cash < 60 ngày operating expense → 🟡 Warning
Available Cash > 90 ngày operating expense → 🟢 Healthy
```

---

## DOMAIN 4: MARKET
### CRM Đa kênh, Bán hàng, Xuất khẩu

---

### 4.1 Sales Process

```
Lead (tiềm năng)
    │
    ▼
Opportunity (cơ hội)
    │
    ▼
Quotation (báo giá)
    │
    ├── Rejected → Close Lost
    └── Accepted ↓
              │
              ▼
         Sales Order (xác nhận đơn)
              │
              ▼
         ATP/CTP Check (ngày giao hàng)
              │
              ▼
         Production Plan Trigger
              │
              ▼
         Delivery
              │
              ▼
         Invoice + AR
```

### 4.2 CRM Core Features

#### Lead Management
- Lead scoring by AI (probability of conversion)
- Lead source tracking (website, referral, exhibition, zalo)
- Auto-assign to sales rep by territory/product

#### Opportunity Management
- Stage tracking (Qualification → Proposal → Negotiation → Closed)
- Expected value, close date, win probability
- Competitor tracking
- Activity log (calls, meetings, emails)

#### Quotation Engine
- Template-based quotation
- Auto-pull pricing from price list
- Volume discount rules
- Margin calculator (cost + margin → price)
- Approval workflow for discounts

### 4.3 ATP / CTP

#### ATP (Available-to-Promise)

```
Requested Quantity
    │
    ▼
Check Finished Goods Inventory
    │
    ├── Sufficient → Confirm delivery date
    └── Insufficient ↓
              │
              ▼
         Check In-Progress WOs
              │
              ├── Will be available → Commit date from WO
              └── Need to produce ↓
                        │
                        ▼
                   CTP Calculation
```

#### CTP (Capable-to-Promise)

```
Check Machine Capacity Available
    │
    ▼
Check Material Availability (MRP)
    │
    ▼
Calculate Lead Time
    │
    ▼
Confirm Delivery Date + Risk Level
```

### 4.4 Multi-channel Integration

| Kênh | Tích hợp | Dùng cho |
|------|---------|---------|
| Zalo OA | Zalo API | Khách hàng nội địa SME |
| Facebook | Meta API | Marketing + B2C |
| Website | REST API | Portal B2B |
| Email | SMTP/IMAP | Khách hàng xuất khẩu |
| EDI | EDIFACT/X12 | Khách hàng lớn |

### 4.5 Export Management

- LC (Letter of Credit) tracking
- Packing list generation
- CO (Certificate of Origin) tracking
- Customs declaration docs
- INCOTERMS management
- Foreign currency AR

---

## DOMAIN 5: GOVERNANCE
### Chiến lược, OKR, Rủi ro, Quyết định

---

### 5.1 Strategy Management

```
Vision (5-10 năm)
    └── Strategic Pillars (3-5 trụ cột)
              └── Strategic Objectives (OKR cấp công ty)
                        └── Department OKRs
                                  └── Individual Goals
```

### 5.2 OKR Engine

```
Objective: Tăng hiệu suất sản xuất NM4 lên 85% OEE
Key Results:
  KR1: OEE đạt 85% trung bình Q3 (hiện tại: 72%)
  KR2: Downtime < 10% trong Q3
  KR3: Scrap rate < 2% trong Q3
  KR4: OTIF ≥ 95% trong Q3
```

### 5.3 Risk Register

| Trường | Mô tả |
|-------|-------|
| Risk ID | Mã rủi ro |
| Category | Financial / Operational / Compliance / Strategic |
| Description | Mô tả rủi ro |
| Likelihood | 1-5 (Very Low → Very High) |
| Impact | 1-5 (Minor → Catastrophic) |
| Risk Score | Likelihood × Impact |
| Owner | Người chịu trách nhiệm |
| Mitigation | Kế hoạch giảm thiểu |
| Status | Active / Mitigated / Accepted / Closed |

### 5.4 Decision Log

Mọi quyết định quan trọng phải được ghi nhận:

```
Decision Record:
  - What: Quyết định gì
  - Why: Cơ sở quyết định (data + context)
  - Who: Ai quyết định
  - When: Khi nào
  - Alternatives: Các phương án đã xét
  - Expected outcome: Kết quả mong đợi
  - Actual outcome: Kết quả thực tế (cập nhật sau)
  - AI recommendation: Đề xuất AI (nếu có)
```

### 5.5 Board Meeting Support

AI Board Secretary:
- Tổng hợp dữ liệu trước cuộc họp
- Chuẩn bị Board Pack tự động
- Ghi nhận quyết định trong họp
- Theo dõi action items sau họp
- Gửi reminder trước deadline

---

## DOMAIN 6: AUDIT
### Kiểm toán Nội bộ, CAPA, Tuân thủ

---

### 6.1 Audit Lifecycle

```
PLAN
  └── Audit Program (kế hoạch kiểm toán năm)
        └── Audit Assignment (phân công kiểm toán viên)
              │
              ▼
EXECUTE
  └── Fieldwork (thu thập bằng chứng)
        └── Working Papers
              │
              ▼
REPORT
  └── Draft Findings
        └── Management Response
              │
              ▼
REMEDIATE
  └── CAPA (Corrective & Preventive Action)
        └── Implementation Tracking
              │
              ▼
CLOSE
  └── Effectiveness Verification
        └── Audit Close
```

### 6.2 CAPA Management

```
Finding / Defect / Non-conformance
    │
    ▼
Root Cause Analysis (5 Whys / Fishbone)
    │
    ▼
Corrective Action Plan
    ├── What to do
    ├── Who is responsible
    └── When to complete
    │
    ▼
Preventive Action Plan
    │
    ▼
Implementation + Evidence Upload
    │
    ▼
Effectiveness Check (30/60/90 ngày)
    │
    ├── Effective → Close CAPA
    └── Not Effective → New CAPA
```

### 6.3 Immutable Audit Trail

- **Không thể xóa** bất kỳ audit record nào
- Mọi thao tác hệ thống đều có log: who, what, when, context, before/after values
- Lưu trữ 7 năm
- Export được để phục vụ kiểm toán độc lập

---

## DOMAIN 7: ESG
### Môi trường, Xã hội, Quản trị Bền vững

---

### 7.1 Environmental Tracking

#### Energy Management

```
Energy Sources:
  - Electricity (kWh)
  - Gas (m³)
  - Fuel Oil (liters)

Per Metrics:
  - Per unit produced
  - Per machine hour
  - Per plant
  - Per product line

Targets:
  - Annual reduction target (%)
  - Monthly baseline
```

#### Carbon Footprint

```
Scope 1: Direct emissions (fuel combustion, vehicles)
Scope 2: Indirect emissions (purchased electricity)
Scope 3: Value chain (materials, transport, waste)
```

#### Water & Waste

- Water consumption (m³) per unit produced
- Waste generation (kg) by type (hazardous / non-hazardous)
- Recycling rate (%)

### 7.2 Social Compliance

#### BSCI / SMETA Audit Support

- Worker welfare checklist
- Working hours tracking (overtime limits)
- Wage compliance
- Child labor verification
- Health & Safety records
- Training records

### 7.3 International Compliance

| Standard | Mô tả | Dữ liệu cần |
|----------|-------|------------|
| **CBAM** | EU Carbon Border Adjustment Mechanism | Carbon per product, Scope 1+2 |
| **BSCI** | Business Social Compliance Initiative | Labor, Safety, Environment |
| **SMETA** | Sedex Members Ethical Trade Audit | 4-pillar: Labor, H&S, Env, Ethics |
| **ISO 14001** | Environmental Management | Energy, Water, Waste, Incidents |
| **ISO 45001** | Occupational H&S | Near miss, Incidents, Training |

### 7.4 ESG Reporting

- Monthly ESG dashboard
- Annual Sustainability Report (GRI standards)
- CBAM declaration (quarterly to EU)
- Customer ESG questionnaire auto-fill

---

## DOMAIN 8: AI AGENTIC LAYER
### Lực lượng Lao động Số

---

### 8.1 Agent Workforce

Xem chi tiết tại [05-AI-SYSTEM.md](./05-AI-SYSTEM.md)

#### Quick Reference

| Agent | Vai trò | Domain |
|-------|---------|--------|
| CEO Copilot | Báo cáo tổng hợp, chuẩn bị họp | Executive |
| CFO Agent | Dòng tiền, variance, forecast | Money |
| Production Planner AI | Tối ưu lịch SX, gợi ý bottleneck | Operations |
| Quality Agent | Phân tích pattern lỗi, CAPA gợi ý | Operations |
| Inventory Agent | Dự báo shortage, reorder suggestion | Operations |
| Sales Copilot | Lead scoring, deal risk, quotation draft | Market |
| Risk Agent | Cross-domain risk detection | Governance |
| Board Secretary AI | Board pack, meeting prep, follow-up | Governance |

### 8.2 AI Governance Principles

```
1. Human-in-the-loop: Bắt buộc cho mọi hành động có ảnh hưởng thực tế
2. Audit log: Mọi AI action đều có log không thể xóa
3. Tool-based access: AI chỉ truy cập data qua Tools, không query DB trực tiếp
4. Confidence threshold: Chỉ đề xuất khi confidence ≥ ngưỡng cấu hình
5. Explainability: Mọi đề xuất AI phải có giải thích ngắn gọn
6. Reversibility: AI chỉ được phép thực hiện action có thể undo
```

### 8.3 AI ↔ Business Domain Integration

```
OPS Domain  ←→  Production Planner AI + Quality Agent + Inventory Agent
MONEY Domain ←→  CFO Agent + Costing Agent
MARKET Domain ←→  Sales Copilot + Deal Risk Agent
GOV Domain   ←→  Risk Agent + Board Secretary AI
AUDIT Domain ←→  Audit Assistant
ESG Domain   ←→  ESG Report Generator
```
