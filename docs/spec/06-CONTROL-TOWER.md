# PHẦN 6 — CONTROL TOWER UI/UX + WORKFLOW ENGINE
## Buồng Lái Điều hành Doanh nghiệp

---

## 1. Control Tower Philosophy

### 1.1 Mục tiêu

Control Tower **KHÔNG phải** để xem báo cáo.

Control Tower là **hệ thống điều hành thực chiến** — nơi mọi vấn đề được phát hiện, phân tích, quyết định và theo dõi kết quả trong một vòng lặp liên tục.

### 1.2 Core Loop

```
                    ┌─────────────────┐
                    │   FEEDBACK      │
                    │ (Kết quả đo lại)│
                    └────────▲────────┘
                             │
┌──────────┐    ┌────────────┴──────┐    ┌──────────────┐
│  SIGNAL  │───▶│     DRILL-DOWN    │───▶│   DECISION   │
│(KPI Red) │    │ (Root Cause ≤3 ck)│    │  (War Room)  │
└──────────┘    └───────────────────┘    └──────┬───────┘
                                                │
                                                ▼
                                         ┌──────────────┐
                                         │    ACTION    │
                                         │Ticket+Owner  │
                                         │+SLA+CAPA     │
                                         └──────────────┘
```

### 1.3 The 6 Iron Rules

```
Rule 1: Mọi KPI phải có màu (Red / Amber / Green)
Rule 2: KPI đỏ phải có action (không được để đó)
Rule 3: Mọi action phải có owner (không anonymous)
Rule 4: Mọi owner phải có SLA (không open-ended)
Rule 5: SLA breach phải có escalation (leo thang tự động)
Rule 6: Mọi kết quả phải được đo lại (closed loop)
```

---

## 2. App Shell Architecture (Filament v5)

### 2.1 Layout 4 Vùng

```
┌─────────────────────────────────────────────────────────────────┐
│  GLOBAL HEADER                                                  │
│  [Logo] [Context Switcher] [Command Bar ⌘K] [Alerts🔔] [User]  │
├──────────────┬──────────────────────────────┬───────────────────┤
│              │                              │                   │
│   SIDEBAR    │      MAIN CONTENT            │  RIGHT DRAWER     │
│              │                              │                   │
│  Executive   │  ┌─────────────────────────┐ │  AI Analysis      │
│  Operations  │  │    KPI Cards            │ │  Root Cause       │
│  Inventory   │  │  Revenue OEE Cash OTIF  │ │  Recommendations  │
│  Finance     │  └─────────────────────────┘ │  Quick Actions    │
│  HR          │                              │                   │
│  AI          │  ┌─────────────────────────┐ │  ┌─────────────┐ │
│  System      │  │    Alert Feed           │ │  │ [Approve]   │ │
│              │  │  🔴 Machine Down NM4    │ │  │ [Create Tkt]│ │
│              │  │  🟡 AR Overdue 500M     │ │  │ [Escalate]  │ │
│              │  └─────────────────────────┘ │  └─────────────┘ │
│              │                              │                   │
│              │  ┌─────────────────────────┐ │                   │
│              │  │    Charts / Tables      │ │                   │
│              │  └─────────────────────────┘ │                   │
│              │                              │                   │
└──────────────┴──────────────────────────────┴───────────────────┘
```

### 2.2 Global Header Components

#### Context Switcher (CRITICAL UX)

```
[Thuận Đức Group ▾] → [NM4 - Nhà máy 4 ▾] → [Plant Manager ▾]
      Company              Plant                    Role
```

- Đổi context → toàn bộ UI thay đổi ngay lập tức
- Không reload page (Livewire / Alpine.js)
- Lịch sử context gần nhất (last 5 contexts)

#### Command Bar (⌘K / Ctrl+K)

```
┌─────────────────────────────────────────────────────────┐
│  🔍 Tìm kiếm... (WO-2026-001, SO-123, máy in số 2)     │
├─────────────────────────────────────────────────────────┤
│  📋 Work Orders                    Ctrl+W               │
│  📦 Inventory Lookup               Ctrl+I               │
│  📊 Open Control Tower             Ctrl+T               │
│  🤖 Ask AI                         Ctrl+A               │
│  ⚡ Create Quick Ticket            Ctrl+N               │
└─────────────────────────────────────────────────────────┘
```

#### Notification Center

```
🔔 Notifications (12 unread)
┌────────────────────────────────────────────┐
│ 🔴 CRITICAL (2)                            │
│   • Machine Line 2 down > 2 hours          │
│   • Cashflow risk: < 30 days runway        │
│                                            │
│ 🟡 WARNING (5)                             │
│   • OEE NM4 dưới 75% — Shift 1            │
│   • SO-2026-234 có nguy cơ trễ            │
│   • AR Overdue: Cty ABC 485M > 30 ngày    │
│   ...                                      │
│                                            │
│ ℹ️  INFO (5)                               │
│   • WO-2026-089 đã hoàn thành             │
│   ...                                      │
└────────────────────────────────────────────┘
```

### 2.3 Sidebar Navigation

```
EXECUTIVE
  ├── Control Tower         (tổng quan toàn hệ thống)
  ├── Executive Dashboard   (CEO/CFO/COO view)
  └── Board View            (HĐQT view)

OPERATIONS
  ├── Production Dashboard
  ├── Work Orders
  ├── Planning
  ├── WIP / QR Bin
  └── Quality

INVENTORY
  ├── Stock Levels
  ├── Movements
  └── Inventory Valuation

FINANCE
  ├── P&L Monitor
  ├── Cashflow (13W)
  ├── AR / AP
  └── Cost Variances

PEOPLE
  ├── HR Overview
  ├── Attendance
  ├── KPI Monitor
  └── Payroll

AI
  ├── AI Recommendations
  ├── Agent Activity
  └── AI Audit Log

SYSTEM
  ├── Users & Roles
  ├── Master Data
  └── Configuration
```

---

## 3. Executive Control Tower Dashboard

### 3.1 Layout

```
┌─────────────────────────────────────────────────────────────────────┐
│ CONTROL TOWER — Thuận Đức Group    📅 04/05/2026  [NM4 ▾] [MTD ▾]  │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  FINANCIAL HEALTH                    OPERATIONS HEALTH              │
│  ┌───────────┐ ┌───────────┐        ┌───────────┐ ┌───────────┐   │
│  │ REVENUE   │ │   CASH    │        │    OEE    │ │   OTIF    │   │
│  │  4.2B ✅  │ │  2.1B ⚠️  │        │  78.3% ⚠️ │ │  91.2% ✅  │   │
│  │+12% MoM   │ │ 42 ngày   │        │-6.7% vs T │ │-3.8% vs T │   │
│  └───────────┘ └───────────┘        └───────────┘ └───────────┘   │
│  ┌───────────┐ ┌───────────┐        ┌───────────┐ ┌───────────┐   │
│  │  MARGIN   │ │  AR RISK  │        │  SCRAP    │ │  DOWNTIME │   │
│  │  18.4% ✅  │ │ 850M 🔴   │        │  3.2% 🔴  │ │ 8.5% ⚠️   │   │
│  │+0.8pp MoM │ │>30 ngày   │        │>threshold │ │           │   │
│  └───────────┘ └───────────┘        └───────────┘ └───────────┘   │
│                                                                     │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  AI ALERTS                              ACTION ITEMS (Open: 12)    │
│  ┌─────────────────────────────────┐   ┌──────────────────────────┐│
│  │ 🔴 Máy tráng số 2 down 127 phút │   │ 🔴 OVERDUE (3)           ││
│  │    → SO-234 có nguy cơ trễ     │   │   • Fix máy tráng [Tuấn] ││
│  │    → Est. delay: 2 ngày        │   │   • AR collection [Hùng] ││
│  │    [Xem chi tiết] [Tạo ticket] │   │   • Scrap CAPA [Linh]    ││
│  ├─────────────────────────────────┤   ├──────────────────────────┤│
│  │ 🟡 Dòng tiền: còn 42 ngày      │   │ 🟡 DUE TODAY (4)         ││
│  │    → Dự báo thiếu tiền tuần 6  │   │   ...                    ││
│  │    → Cần thu 1.2B AR           │   └──────────────────────────┘│
│  │    [Xem cashflow] [Assign AR]  │                                │
│  ├─────────────────────────────────┤                                │
│  │ 🟡 Scrap rate Line 3 cao       │                                │
│  │    → 5.8% vs target 2%         │                                │
│  │    → Defect chủ yếu: skew      │                                │
│  │    [Xem phân tích] [CAPA]      │                                │
│  └─────────────────────────────────┘                                │
│                                                                     │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  PRODUCTION TREND (7 ngày)           CASH FLOW FORECAST (8 tuần)   │
│  [Chart: Actual vs Plan per day]     [Chart: Cash waterfall]        │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
```

### 3.2 KPI Card Spec

```php
// Filament Widget
class KpiCard extends Widget
{
    public string $title;
    public string $value;
    public string $status;       // green, amber, red
    public string $trend;        // up, down, flat
    public float  $trend_pct;
    public string $vs_label;     // "vs Target", "vs Last Month"
    public string $drill_url;    // URL when card is clicked
    public bool   $has_alert;
    public ?string $alert_text;
}
```

### 3.3 KPI Color Rules

```php
class KpiColorEngine
{
    public function evaluate(string $metric, float $value, float $target): string
    {
        $achievement = $target > 0 ? ($value / $target) : 0;

        return match(true) {
            $achievement >= 1.00 => 'green',
            $achievement >= 0.80 => 'amber',
            default              => 'red',
        };
    }
    
    // Special metrics (lower is better):
    public function evaluateInverse(string $metric, float $value, float $threshold): string
    {
        return match(true) {
            $value <= $threshold              => 'green',
            $value <= $threshold * 1.25      => 'amber',
            default                          => 'red',
        };
    }
}
```

---

## 4. Drill-Down System

### 4.1 Nguyên lý: Max 3 Clicks to Root Cause

```
Click 1: KPI Card (OEE = 78%, Red)
    ↓
Click 2: Plant Breakdown
    → NM4 Line 3: OEE = 58% (worst)
    ↓
Click 3: Root Cause Panel (Right Drawer opens)
    → Downtime: Máy tráng số 2, 127 phút, cơ khí
    → Scrap: Defect "skew", 45 tấm
    → AI: "Máy này có lịch sử lỗi tương tự 3 lần/tháng.
           Khuyến nghị bảo trì định kỳ mỗi 2 tuần."
    → [Create CAPA] [Create Maintenance Ticket] [View Machine History]
```

### 4.2 Right Drawer Architecture

```
Right Drawer (KHÔNG reload page, mở từ phải sang)
├── Context Header
│   └── "OEE — Line 3 — NM4 — Hôm nay"
├── AI Analysis Section
│   ├── Root cause hypothesis
│   ├── Pattern recognized
│   └── Confidence: 87%
├── Supporting Data
│   ├── Charts (sparklines, mini bar charts)
│   └── Data tables (compact)
├── Recommendations
│   ├── Immediate actions
│   └── Preventive actions
└── Action Buttons
    ├── [✅ Create Ticket]
    ├── [📋 Create CAPA]
    ├── [📊 View Full Analysis]
    └── [↗ Open in New Tab]
```

### 4.3 Drill-down Paths

```
Revenue Alert
  └── By Customer
        └── By Order
              └── By Delivery Status
                    └── Root Cause (production delay / logistics)

OEE Alert
  └── By Plant
        └── By Line
              └── By Machine
                    └── Downtime Events / Defects

Cashflow Alert
  └── By Period
        └── By Source (AR outstanding / AP upcoming)
              └── By Customer/Supplier
                    └── Invoice details

HR KPI Alert
  └── By Department
        └── By Individual KPI
              └── Attendance + Performance breakdown
```

---

## 5. War Room Mode

### 5.1 Định nghĩa

Chế độ **họp nhanh 15 phút** cho ban lãnh đạo khi có sự cố nghiêm trọng.

### 5.2 War Room UI

```
┌──────────────────── WAR ROOM MODE ────────────────────────────┐
│  🚨 INCIDENT: Máy tráng số 2 down — NM4              15:32   │
├────────────────────────────────────────────────────────────────┤
│                                                                │
│  SITUATION (AI Generated)                                      │
│  ─────────────────────────                                     │
│  • Máy dừng lúc 13:25, hiện đã 127 phút                      │
│  • Ảnh hưởng: WO-089 (SO-234 Cty XYZ, 10,000 tấm)           │
│  • Ngày giao cam kết: 06/05, nguy cơ trễ 2 ngày              │
│  • Thiệt hại ước tính: 45M VND (downtime + late penalty)     │
│                                                                │
│  ROOT CAUSE (AI Analysis)                                      │
│  ─────────────────────────                                     │
│  Hypothesis 1 (75%): Hỏng trục cuốn — pattern tương tự       │
│                       tháng 3 và tháng 1                      │
│  Hypothesis 2 (20%): Lỗi điện — không có log bất thường      │
│  Hypothesis 3 (5%):  Lỗi vận hành                            │
│                                                                │
│  OPTIONS FOR DECISION                                          │
│  ─────────────────────────                                     │
│  Option A: Chuyển WO sang Line 4 (+8h delay, cost: 12M)      │
│  Option B: Sửa khẩn cấp (~4h, cost: 8M, uncertainty: 30%)   │
│  Option C: Subcontract (3 ngày, cost: 25M)                   │
│                                                                │
│  PARTICIPANTS: Anh Tuấn (Plant Mgr) | Anh Hùng (Prod Mgr)    │
│               + 2 người khác                                   │
│                                                                │
├────────────────────────────────────────────────────────────────┤
│  DECISION: [Option A ▾]   OWNER: [Anh Hùng ▾]  BY: [16:00]   │
│                                                                │
│  [📝 Log Decision]  [📋 Create Tickets]  [📧 Notify Customer]  │
└────────────────────────────────────────────────────────────────┘
```

### 5.3 War Room Output

Sau khi quyết định:
1. Decision logged (immutable) với full context
2. Action tickets tự động tạo (AI điền nội dung, human confirm)
3. Customer notification draft (AI soạn, human gửi)
4. Follow-up reminder set (T+4h: check progress)

---

## 6. Workflow Engine

### 6.1 Workflow Definition

```php
class WorkflowDefinition
{
    public string $code;
    public string $name;
    public string $domain;

    public array $triggers;
    // [{"type": "alert", "severity": "critical"},
    //  {"type": "event", "name": "machine.downtime.started"}]

    public array $conditions;
    // [{"field": "duration_minutes", "operator": ">=", "value": 60}]

    public array $steps;
    // Each step: assign, notify, wait, branch, escalate

    public array $sla_rules;
    public array $escalation_rules;
}
```

### 6.2 Built-in Workflow Templates

#### Machine Downtime Response

```yaml
workflow: machine_downtime_response
trigger:
  event: downtime.started
  condition: duration > 30 minutes

steps:
  1. alert:
     severity: high
     notify: [supervisor, plant_manager]
     
  2. create_ticket:
     title: "Máy {machine_name} dừng — {duration}m"
     owner: maintenance_lead
     sla: 4 hours
     
  3. ai_analysis:
     agent: quality_agent
     task: root_cause_analysis
     
  4. wait: 2 hours
  
  5. branch:
     condition: ticket.status == "resolved"
     if_true: close_workflow
     if_false: escalate_to_plant_manager
     
escalation:
  level_1: supervisor (0h)
  level_2: plant_manager (2h)
  level_3: operations_director (4h)
  level_4: ceo (8h)
```

#### Quality Defect Response

```yaml
workflow: quality_defect_response
trigger:
  event: qc.result.fail
  condition: defect_rate > threshold

steps:
  1. block_wip_lot (QC Gate — hard block)
  
  2. alert:
     severity: medium
     notify: [qc_manager, line_supervisor]
     
  3. create_capa:
     auto_populate: true (AI fills root cause draft)
     owner: qc_manager
     due: 48 hours
     
  4. notify_production_planner:
     impact: "Lot {lot_id} bị hold — ảnh hưởng WO {wo_id}"
```

#### AR Collection Workflow

```yaml
workflow: ar_collection
trigger:
  schedule: daily_0800
  condition: invoice.overdue_days > 30

steps:
  1. ai_analysis:
     assess: collection_risk_score
     
  2. create_action_item:
     title: "Thu hồi công nợ: {customer} {amount}M"
     owner: ar_accountant
     sla: 3 days
     
  3. draft_collection_email:
     agent: cfo_agent
     template: overdue_reminder
     
  4. notify_sales_rep:
     "Khách hàng {customer} quá hạn {days} ngày"
     
escalation:
  overdue_60: sales_manager + finance_manager
  overdue_90: cfo + ceo + legal
```

---

## 7. SLA Engine

### 7.1 SLA Configuration

```php
SLA_CONFIGS = [
    'critical_machine_down' => [
        'response_sla' => 30,    // minutes to acknowledge
        'resolution_sla' => 240, // minutes to resolve
        'escalation_1' => 60,    // minutes → supervisor
        'escalation_2' => 120,   // minutes → plant manager
        'escalation_3' => 240,   // minutes → ops director
    ],
    'quality_defect' => [
        'response_sla' => 60,
        'resolution_sla' => 480,
        'escalation_1' => 120,
    ],
    'customer_complaint' => [
        'response_sla' => 120,
        'resolution_sla' => 1440, // 24 hours
        'escalation_1' => 240,
    ],
    'ar_collection' => [
        'resolution_sla' => 4320, // 3 days
        'escalation_1' => 1440,   // 1 day
    ],
];
```

### 7.2 SLA Countdown Display

```
ACTION ITEM: Fix máy tráng số 2
SLA: 4 giờ từ 13:25

[████████████░░░░░░░░] 63% — còn 1h 28m
Status: In Progress — Assigned: Anh Tuấn

⚠️ Nếu không xong trước 17:25 → Auto escalate to Plant Manager
```

### 7.3 SLA Breach Handling

```php
class SlaBreachHandler
{
    public function handle(ActionItem $item): void
    {
        // 1. Mark as breached
        $item->update(['sla_breached_at' => now()]);
        
        // 2. Auto-escalate
        $escalateTo = $this->getNextEscalationLevel($item);
        $item->update(['escalated_to_id' => $escalateTo->id]);
        
        // 3. Notify
        $this->notifyEscalation($item, $escalateTo);
        
        // 4. Create audit entry
        $this->auditLog->record('sla_breached', $item);
        
        // 5. If Critical + 2nd breach → alert CEO
        if ($item->priority === 'critical' && $item->escalation_count >= 2) {
            $this->notifyCeo($item);
        }
    }
}
```

---

## 8. Notification Engine

### 8.1 Channels

| Channel | Dùng cho | Latency |
|---------|---------|---------|
| In-app (WebSocket) | Tất cả | < 1s |
| Email | Non-urgent, reports | < 1 min |
| SMS | Critical alerts | < 30s |
| Zalo Business | Internal team | < 5s |
| Slack | DevOps, Tech team | < 5s |

### 8.2 Notification Priority Rules

```
CRITICAL → In-app + SMS + Zalo (all channels, immediate)
HIGH     → In-app + Zalo (immediate)
MEDIUM   → In-app (immediate) + Email (digest hourly)
LOW      → In-app only (digest daily)
INFO     → In-app only (no notification, just badge)
```

### 8.3 Do-Not-Disturb Rules

```
Working hours: 06:00 - 22:00
DND: 22:00 - 06:00
  Exception: CRITICAL alerts always push through
  
During meetings (calendar integration):
  Exception: CRITICAL + HIGH push through
```

---

## 9. Design System

### 9.1 Color Palette

```css
/* Status Colors */
--color-critical: #DC2626;    /* Red-600 */
--color-warning:  #D97706;    /* Amber-600 */
--color-success:  #16A34A;    /* Green-600 */
--color-info:     #2563EB;    /* Blue-600 */
--color-neutral:  #6B7280;    /* Gray-500 */

/* Brand Colors (Thuận Đức) */
--color-navy:     #1E3A5F;    /* Primary */
--color-steel:    #4A6FA5;    /* Secondary */
--color-white:    #FFFFFF;
--color-bg:       #F8FAFC;    /* Background */

/* KPI Status */
--kpi-green:  var(--color-success);
--kpi-amber:  var(--color-warning);
--kpi-red:    var(--color-critical);
```

### 9.2 Typography

```css
/* Dashboard numbers */
.kpi-value         { font-size: 2.25rem; font-weight: 700; }
.kpi-label         { font-size: 0.75rem; font-weight: 500; color: var(--color-neutral); }
.kpi-trend         { font-size: 0.875rem; }

/* Body text */
.body-default      { font-size: 0.875rem; }
.body-small        { font-size: 0.75rem; }
```

### 9.3 UI Rules (Non-negotiable)

```
✅ KPI luôn có màu và xu hướng
✅ Alert luôn có action button
✅ Ticket luôn có owner và due date
✅ Real-time update (không cần F5)
✅ Mobile responsive (plant manager dùng điện thoại)
✅ Right drawer không reload page
✅ ≤ 3 clicks đến root cause
✅ Loading time < 2 seconds

❌ Không scroll dài (paginate hoặc drill)
❌ Không bảng phức tạp > 7 cột visible
❌ Không ẩn thông tin quan trọng trong tooltip
❌ Không popup chồng popup
❌ Không form dài > 1 scroll
```

---

## 10. Mobile Experience (Flutter)

### 10.1 Mobile Personas

| Persona | App | Key Features |
|---------|-----|-------------|
| Plant Manager | Mobile Control Tower | KPI, Alerts, Quick Approve |
| Production Supervisor | Shopfloor App | WO Status, QC Gate, Downtime Log |
| Sales Rep | Sales Mobile | Lead, Quotation, Customer Visit |
| QC Inspector | QC App | Scan QR, Record Inspection |
| Warehouse Staff | WMS Mobile | Scan QR, Move Lot, Receive |

### 10.2 Shopfloor App (Core)

```
Shopfloor App (Flutter)
├── My Shift Dashboard
│   ├── Assigned Work Orders
│   ├── Current Machine Status
│   └── My KPI Today
├── QR Scanner
│   ├── Scan Lot
│   ├── Record Move
│   └── Record Issue
├── QC Gate
│   ├── Scan Lot
│   ├── Enter Inspection Result
│   └── Upload Photo Evidence
├── Downtime Log
│   ├── Record Start
│   ├── Select Reason
│   └── Record End
└── Report Issue
    └── Text + Photo → Ticket
```

---

## 11. Real-time Architecture

### 11.1 WebSocket Events

```javascript
// Client subscribes to channels
Echo.private(`company.${companyId}`)
    .listen('AlertCreated', alert => updateAlertFeed(alert))
    .listen('KpiUpdated', kpi => updateKpiCard(kpi))
    .listen('TicketCreated', ticket => addToTicketList(ticket))
    .listen('AiRecommendationCreated', rec => showAiPanel(rec));

Echo.private(`plant.${plantId}`)
    .listen('MachineStatusChanged', event => updateMachineStatus(event))
    .listen('WorkOrderUpdated', wo => updateWoTable(wo));
```

### 11.2 Update Frequency

| Data | Update Trigger | Frequency |
|------|---------------|-----------|
| Machine status | Event-driven | Real-time |
| Production output | Shift report | Per shift |
| OEE | Machine logs | Hourly |
| KPI cards | Batch calculation | 5 minutes |
| AI recommendations | Agent run | Variable |
| Financial KPIs | Batch | Daily (06:00) |
| Cashflow forecast | Batch | Daily (07:00) |
