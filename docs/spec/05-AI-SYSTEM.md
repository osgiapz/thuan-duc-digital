# PHẦN 5 — AI AGENT SYSTEM & ORCHESTRATION
## Kiến trúc AI Lõi — Trái Tim của Hệ thống

---

## 1. AI System Overview

### 1.1 Triết lý

> AI trong hệ thống này **KHÔNG phải chatbot**.
> AI là **AI Agent Workforce** — Tổ chức nhân sự số tham gia vận hành doanh nghiệp.

### 1.2 AI Execution Model

```
External Signal / User Trigger
          │
          ▼
    Agent Selection
    (Domain routing)
          │
          ▼
    Context Assembly
    (Company + Plant + Role + Recent Events)
          │
          ▼
    Memory Retrieval
    (Long-term knowledge + Short-term context)
          │
          ▼
    Tool Execution
    (Structured data access via MCP Tools)
          │
          ▼
    LLM Processing
    (Claude / GPT-4 / DeepSeek)
          │
          ▼
    Response Generation
    (Insight + Recommendation + Draft Action)
          │
          ▼
    Confidence Check
    (≥ threshold → proceed, < threshold → request human input)
          │
          ▼
    Human Review Gate
    (Auto for Low risk, Confirm for Medium, Approve for High)
          │
          ▼
    Execution
    (Create ticket / Update status / Send notification)
          │
          ▼
    Audit Logging
    (Immutable record of all AI actions)
          │
          ▼
    Feedback Loop
    (Human feedback → improves future recommendations)
```

### 1.3 AI vs Human Responsibility Matrix

| Decision Type | AI Role | Human Role |
|--------------|---------|-----------|
| Data analysis | Execute autonomously | Review result |
| Anomaly detection | Execute autonomously | Acknowledge |
| Recommendation | Generate draft | Approve/Reject |
| Ticket creation | Auto-create (low risk) | Validate |
| Approval workflows | Cannot approve | Must approve |
| Financial transactions | Cannot execute | Must execute |
| Data deletion | Never | Must be manual |

---

## 2. AI Agent Workforce

### 2.1 Executive Agents

#### CEO Copilot

```yaml
agent_code: ceo_copilot
name: CEO Copilot
role: Executive Intelligence Assistant
scope: company_wide
access_level: read_all

capabilities:
  - Daily executive briefing (P&L, Cash, OEE, OTIF, Risks)
  - Weekly performance summary vs targets
  - Exception reporting (what needs CEO attention)
  - Decision support (scenario analysis)
  - Board meeting preparation
  - Competitive intelligence integration

tools:
  - get_company_kpi_summary
  - get_plant_performance
  - get_cashflow_status
  - get_top_risks
  - get_pending_decisions
  - get_overdue_action_items
  - generate_executive_report

trigger: daily_0700, on_demand, critical_alert
output: executive_briefing, risk_alert, decision_draft
```

#### Board Secretary AI

```yaml
agent_code: board_secretary
name: Board Secretary AI
role: Board Meeting Support

capabilities:
  - Compile Board Pack (auto-populate with latest data)
  - Meeting agenda management
  - Capture decisions in real-time during meetings
  - Generate meeting minutes
  - Track action items from board meetings
  - Send reminders to responsible parties
  - Archive all board decisions with context

tools:
  - get_financial_highlights
  - get_strategic_kpis
  - create_decision_log
  - create_action_item
  - generate_board_pack_pdf
  - send_notification
```

#### Risk Agent

```yaml
agent_code: risk_agent
name: Enterprise Risk Agent
role: Cross-domain Risk Detection

capabilities:
  - Scan all domains for risk signals (30-min intervals)
  - Correlate risks across domains (e.g., machine down → order delay → cashflow)
  - Predict future risks from current trends
  - Update risk register automatically
  - Escalate critical risks immediately

risk_signals_monitored:
  - OEE < 70% for > 2 hours
  - Cashflow < 30 days runway
  - AR overdue > 30 days > 500M VND
  - Inventory: material shortage for confirmed orders
  - Quality: defect rate > 5% on any line
  - Delivery: OTIF < 85% month-to-date
  - HR: absenteeism > 15% on any line
  - ESG: energy consumption variance > 20%
```

---

### 2.2 Operations Agents

#### Production Planner AI

```yaml
agent_code: production_planner
name: Production Planner AI
role: Production Planning Optimization

capabilities:
  - Analyze demand vs capacity
  - Suggest optimal production schedule
  - Detect capacity bottlenecks before they occur
  - Recommend work order sequencing to minimize setup time
  - Re-plan automatically when disruptions occur (machine down, material shortage)
  - Calculate realistic delivery dates (ATP/CTP)

tools:
  - get_open_sales_orders
  - get_machine_capacity
  - get_material_availability
  - get_current_work_orders
  - create_work_order
  - update_work_order_schedule
  - create_purchase_request
  - send_alert

trigger:
  - Daily: 06:00 (before shift start)
  - Event: machine.downtime.started
  - Event: material.shortage.detected
  - On-demand: planner request
```

#### Quality Agent

```yaml
agent_code: quality_agent
name: Quality Intelligence Agent
role: Defect Analysis and Prevention

capabilities:
  - Real-time defect pattern recognition
  - Root cause hypothesis generation (statistical + ML)
  - Predict which machines/lines/shifts have high defect risk
  - Auto-generate CAPA when defect rate exceeds threshold
  - Trend analysis (is quality improving or deteriorating?)
  - SPC (Statistical Process Control) monitoring

tools:
  - get_qc_events
  - get_defect_analysis
  - get_machine_performance
  - get_operator_performance
  - create_capa
  - create_alert
  - update_qc_threshold

trigger:
  - Event: qc.result.fail
  - Event: qc.result.hold
  - Scheduled: every_hour (trend monitoring)
  - Threshold: defect_rate > configured_threshold
```

#### Inventory Agent

```yaml
agent_code: inventory_agent
name: Inventory Optimization Agent
role: Stock Level Management

capabilities:
  - Monitor stock levels vs reorder points in real-time
  - Forecast stockout dates based on production plan
  - Recommend reorder quantities (EOQ)
  - Detect dead stock (no movement > N days)
  - Optimize bin locations (ABC analysis)
  - Reconcile physical vs system inventory discrepancies

tools:
  - get_inventory_levels
  - get_consumption_history
  - get_pending_purchase_orders
  - get_work_order_material_requirements
  - create_purchase_request
  - create_alert

trigger:
  - Scheduled: every_6_hours
  - Event: inventory.issue.created (recalculate remaining)
  - Event: work_order.released (reserve materials)
```

---

### 2.3 Finance Agents

#### CFO Agent

```yaml
agent_code: cfo_agent
name: CFO Intelligence Agent
role: Financial Performance and Risk Management

capabilities:
  - Daily P&L monitoring (actual vs budget)
  - Cashflow 13-week forecast (update daily)
  - Variance analysis (MPV, MUV, LEV)
  - AR collection risk scoring
  - AP optimization (early payment discount vs cash preservation)
  - Budget vs actual alerts

tools:
  - get_pl_summary
  - get_cashflow_forecast
  - get_ar_aging
  - get_ap_aging
  - get_cost_variances
  - get_budget_vs_actual
  - create_alert
  - draft_financial_report

guardrails:
  - Cannot initiate any payments
  - Cannot approve invoices
  - Can only create alerts and recommendations
```

#### Costing Agent

```yaml
agent_code: costing_agent
name: Product Costing Agent
role: Real-time Cost Intelligence

capabilities:
  - Calculate actual cost per work order upon completion
  - Compare actual vs standard cost
  - Identify cost driver anomalies
  - Suggest standard cost updates when variance is persistent
  - Per-customer profitability analysis
  - Simulate cost impact of price changes

tools:
  - get_work_order_materials_consumed
  - get_labor_hours_actual
  - get_machine_runtime
  - get_overhead_allocation
  - get_standard_costs
  - create_cost_variance_report
  - update_cost_analysis
```

---

### 2.4 Market Agents

#### Sales Copilot

```yaml
agent_code: sales_copilot
name: Sales Intelligence Copilot
role: Sales Productivity and Win Rate Improvement

capabilities:
  - Lead scoring (0-100 probability of conversion)
  - Next best action recommendation for each lead/opportunity
  - Quotation draft generation (product + price + terms)
  - Deal risk detection (going cold, competitor risk)
  - Customer churn prediction
  - Sales forecast (weekly/monthly)

tools:
  - get_lead_history
  - get_customer_purchase_history
  - get_competitor_data
  - get_product_availability (ATP)
  - get_pricing_rules
  - create_quotation_draft
  - send_notification_to_salesperson
```

#### Deal Risk Agent

```yaml
agent_code: deal_risk_agent
name: Deal Risk Monitor

capabilities:
  - Monitor all open opportunities for risk signals
  - Alert when deal hasn't had activity in N days
  - Identify pricing risk (margin too thin)
  - Delivery commitment risk (ATP date uncertain)

trigger:
  - Daily: scan all opportunities status = in_negotiation
  - Event: sales_order.delivery.at_risk
```

---

## 3. AI Runtime Architecture

### 3.1 FastAPI Service Structure

```
ai-service/
├── main.py                   # FastAPI app
├── agents/
│   ├── base_agent.py         # Base class
│   ├── executive/
│   │   ├── ceo_copilot.py
│   │   └── board_secretary.py
│   ├── operations/
│   │   ├── production_planner.py
│   │   ├── quality_agent.py
│   │   └── inventory_agent.py
│   ├── finance/
│   │   ├── cfo_agent.py
│   │   └── costing_agent.py
│   └── market/
│       └── sales_copilot.py
├── tools/
│   ├── registry.py           # Tool registration
│   ├── operations_tools.py
│   ├── finance_tools.py
│   ├── hr_tools.py
│   └── notification_tools.py
├── memory/
│   ├── vector_store.py       # Qdrant / pgvector
│   ├── context_builder.py
│   └── session_memory.py
├── prompts/
│   ├── system_prompts/
│   └── task_prompts/
├── guardrails/
│   ├── content_filter.py
│   ├── risk_classifier.py
│   └── action_validator.py
└── audit/
    └── action_logger.py
```

### 3.2 Agent Base Class

```python
class BaseAgent:
    def __init__(self, agent_config: AgentConfig, context: WorkContext):
        self.config = agent_config
        self.context = context
        self.llm = self._init_llm()
        self.tools = self._load_tools(agent_config.tools)
        self.memory = MemoryEngine(agent_config.memory_config)
        self.guardrails = GuardrailsEngine(agent_config.guardrails)
        self.audit = AuditLogger()

    async def run(self, trigger: AgentTrigger) -> AgentResult:
        run = await self.audit.start_run(trigger)
        try:
            context = await self._build_context(trigger)
            memory = await self.memory.retrieve(context)
            messages = self._build_messages(context, memory, trigger)
            response = await self._execute_with_tools(messages)
            result = await self._process_response(response)
            await self._apply_guardrails(result)
            await self.audit.complete_run(run, result)
            return result
        except Exception as e:
            await self.audit.fail_run(run, str(e))
            raise
```

---

## 4. Prompt Architecture

### 4.1 Prompt Hierarchy

```
Level 1: Global System Prompt
  "You are an AI agent in FactoryMind AI OS.
   You help manage industrial manufacturing enterprises.
   You NEVER take actions that cannot be undone without human approval.
   You ALWAYS cite the data source for your recommendations."

Level 2: Tenant Prompt (per company)
  "Company: Thuận Đức Group
   Industry: Plastic/Textile Manufacturing
   Currency: VND
   Timezone: Asia/Ho_Chi_Minh
   Working hours: 06:00-22:00 (2 shifts)
   Plants: NM1A (Dệt), NM4 (Tráng/In/May)"

Level 3: Domain Prompt (per domain)
  "Domain: Operations
   Focus: Production efficiency, WIP control, Quality
   Key metrics: OEE, Scrap Rate, OTIF, WIP Turns
   Alert thresholds: [configured per plant]"

Level 4: Agent Prompt (per agent)
  "Agent: Production Planner AI
   Your primary goal is to maximize production throughput
   while minimizing WIP inventory and late deliveries.
   You can READ all production data.
   You can SUGGEST work order changes.
   You CANNOT modify work orders without supervisor approval."

Level 5: Task Prompt (per invocation)
  "Analyze current production plan for next 48 hours.
   Identify bottlenecks and suggest re-sequencing.
   Current date: 2026-05-04 06:00
   Plant: NM4"
```

### 4.2 Prompt Templates (Examples)

#### Daily Executive Briefing

```
Prepare the daily executive briefing for {company_name} for {date}.

Structure:
1. Overall health (Red/Amber/Green with reason)
2. Top 3 issues requiring attention today
3. Financial snapshot (Revenue MTD, Cash position, Top AR risk)
4. Operations snapshot (OEE, OTIF, Top production issue)
5. Pending decisions requiring CEO input
6. AI recommendations from yesterday — status update

Tone: Direct, data-driven, actionable. No fluff.
Format: Use bullet points. Flag critical items with ⚠️.
Language: Vietnamese (numbers in VND, dates in DD/MM/YYYY)
```

#### Production Disruption Analysis

```
Machine {machine_code} ({machine_name}) at {plant_name} has been down
for {duration_minutes} minutes.

Analyze the impact and recommend actions:

1. Impact Assessment:
   - Which work orders are affected?
   - What is the estimated delay?
   - Which customer orders are at risk?
   - Financial impact estimate?

2. Root Cause Hypothesis:
   - Based on downtime history for this machine
   - Based on last maintenance date
   - Based on similar incidents in the past

3. Recommended Actions (sorted by priority):
   - Immediate (< 1 hour)
   - Short-term (< 1 day)
   - Preventive (< 1 week)

4. Escalation needed? (Yes/No + reason)
```

---

## 5. Memory Architecture

### 5.1 Memory Levels

| Level | Scope | Storage | TTL | Contents |
|-------|-------|---------|-----|----------|
| **Global** | All tenants | PostgreSQL + Vector | Permanent | Industry knowledge, best practices |
| **Tenant** | Per company | PostgreSQL + Vector | Permanent | Company config, product catalog, history |
| **Domain** | Per domain | PostgreSQL + Vector | 1 year | Domain-specific patterns, decisions |
| **Agent** | Per agent | PostgreSQL | 90 days | Agent-specific learnings |
| **Session** | Per conversation | Redis | 24 hours | Current conversation context |

### 5.2 Vector Search Flow

```python
async def retrieve_context(query: str, context: WorkContext) -> list[str]:
    embedding = await embed(query)
    
    results = await vector_store.search(
        collection="tenant_knowledge",
        vector=embedding,
        filter={
            "company_id": context.company_id,
            "domain": context.domain
        },
        limit=5,
        score_threshold=0.75
    )
    
    return [r.payload["text"] for r in results]
```

### 5.3 Learning Loop

```
AI makes recommendation
    │
    ▼
Human accepts / rejects / modifies
    │
    ▼
Outcome is tracked (was it effective?)
    │
    ▼
Feedback stored in memory
    │
    ▼
Future recommendations improved
```

---

## 6. Tool Registry (MCP Tools)

### 6.1 Nguyên tắc

```
AI KHÔNG ĐƯỢC:
  ❌ Query database trực tiếp
  ❌ Execute raw SQL
  ❌ Access file system
  ❌ Call external APIs without tool wrapper
  ❌ Take financial actions (payment, refund)
  ❌ Delete or update ledger records

AI CHỈ ĐƯỢC:
  ✅ Call registered Tools
  ✅ Read data via Tools
  ✅ Create tickets/recommendations (queued for human review if high risk)
  ✅ Send notifications (non-financial, non-critical)
```

### 6.2 Tool Catalog

#### Operations Tools

```python
@tool("get_work_order_status")
async def get_work_order_status(
    work_order_id: Optional[str] = None,
    status_filter: Optional[list[str]] = None,
    plant_id: Optional[str] = None,
    date_from: Optional[date] = None,
    date_to: Optional[date] = None,
) -> dict:
    """Get work order status and progress"""
    ...

@tool("get_machine_oee")
async def get_machine_oee(
    machine_id: Optional[str] = None,
    plant_id: Optional[str] = None,
    period: str = "today",
    # today, this_week, this_month, custom
) -> dict:
    """Get OEE metrics for machines"""
    ...

@tool("get_production_output")
async def get_production_output(
    plant_id: str,
    date_from: date,
    date_to: date,
    group_by: str = "day",
    # day, week, month, product, line
) -> dict:
    """Get production output summary"""
    ...

@tool("get_inventory_status")
async def get_inventory_status(
    product_id: Optional[str] = None,
    warehouse_id: Optional[str] = None,
    include_reservations: bool = True,
) -> dict:
    """Get current inventory levels"""
    ...

@tool("get_defect_analysis")
async def get_defect_analysis(
    plant_id: str,
    period: str,
    group_by: str = "defect_type",
    # defect_type, machine, line, product, shift, operator
) -> dict:
    """Analyze defect patterns"""
    ...
```

#### Finance Tools

```python
@tool("get_cashflow_summary")
async def get_cashflow_summary(
    company_id: str,
    weeks_ahead: int = 13,
) -> dict:
    """Get 13-week cashflow forecast"""
    ...

@tool("get_cost_variance_report")
async def get_cost_variance_report(
    company_id: str,
    plant_id: Optional[str] = None,
    period: str,
    variance_type: Optional[str] = None,
) -> dict:
    """Get cost variances (MPV, MUV, LEV, etc.)"""
    ...

@tool("get_ar_aging")
async def get_ar_aging(
    company_id: str,
    customer_id: Optional[str] = None,
) -> dict:
    """Get accounts receivable aging"""
    ...
```

#### Action Tools

```python
@tool("create_alert")
async def create_alert(
    company_id: str,
    plant_id: Optional[str],
    severity: Literal["critical", "high", "medium", "low"],
    title: str,
    message: str,
    context: dict,
    notify_users: list[str] = [],
) -> dict:
    """Create a system alert"""
    ...

@tool("create_action_item")
async def create_action_item(
    company_id: str,
    title: str,
    description: str,
    owner_id: str,
    due_hours: int,
    priority: str,
    source_type: str,
    source_id: Optional[str],
) -> dict:
    """Create a ticket for human action"""
    # Note: This always goes to a queue for human review
    # before being formally created
    ...

@tool("create_recommendation")
async def create_recommendation(
    agent_code: str,
    company_id: str,
    domain: str,
    title: str,
    summary: str,
    details: dict,
    suggested_actions: list[dict],
    confidence_score: float,
    priority: str,
) -> dict:
    """Store an AI recommendation for human review"""
    ...
```

---

## 7. Governed Autonomy Framework

### 7.1 Risk Classification

```python
class ActionRiskLevel(Enum):
    INFO     = "info"     # Just display info
    LOW      = "low"      # Auto-execute, log only
    MEDIUM   = "medium"   # Execute with confirmation (popup)
    HIGH     = "high"     # Require approval workflow
    CRITICAL = "critical" # Cannot execute, human only

RISK_MATRIX = {
    "create_alert":          ActionRiskLevel.LOW,
    "create_recommendation": ActionRiskLevel.LOW,
    "send_notification":     ActionRiskLevel.LOW,
    "create_action_item":    ActionRiskLevel.MEDIUM,
    "update_work_order":     ActionRiskLevel.MEDIUM,
    "create_purchase_request": ActionRiskLevel.HIGH,
    "approve_invoice":       ActionRiskLevel.CRITICAL,  # Blocked
    "initiate_payment":      ActionRiskLevel.CRITICAL,  # Blocked
    "delete_data":           ActionRiskLevel.CRITICAL,  # Blocked
}
```

### 7.2 Approval Flow

```
AI proposes action with risk = HIGH
    │
    ▼
Action queued as "pending_human_approval"
    │
    ▼
Notification sent to approver
    │
    ├── Approved → Execute + Log
    ├── Modified → Execute modified version + Log
    └── Rejected → Log rejection + AI learns
```

### 7.3 Confidence Thresholds

```python
CONFIDENCE_THRESHOLDS = {
    "auto_execute":     0.90,  # Auto-execute without asking
    "suggest":          0.70,  # Show recommendation, ask for approval
    "low_confidence":   0.50,  # Show with "uncertain" flag
    "suppress":         0.00,  # Below 0.50: don't show to user
}
```

---

## 8. AI Guardrails

### 8.1 Content Guardrails

```python
GUARDRAIL_RULES = [
    # Never reveal internal system data in user-facing output
    BlockRule("system_config_exposure"),
    # Never generate financial transactions
    BlockRule("financial_transaction_generation"),
    # Never access other tenants' data
    BlockRule("cross_tenant_access"),
    # Confidence must be stated when < 0.8
    WarningRule("low_confidence_transparency"),
    # Always cite data source
    WarningRule("unsourced_claim"),
]
```

### 8.2 Output Validation

```python
async def validate_ai_output(output: AgentOutput) -> ValidatedOutput:
    # 1. Check for PII in output
    if contains_pii(output.text):
        output.text = redact_pii(output.text)
    
    # 2. Check confidence score
    if output.confidence < 0.5:
        output.add_disclaimer("Kết quả có độ tin cậy thấp, vui lòng xác minh thủ công")
    
    # 3. Check action risk level
    for action in output.proposed_actions:
        risk = classify_action_risk(action)
        if risk == ActionRiskLevel.CRITICAL:
            output.block_action(action, "Hành động này yêu cầu phê duyệt của con người")
    
    return ValidatedOutput(output)
```

---

## 9. AI Audit System

### 9.1 What is logged (mọi thứ)

```
For every AI run:
  ✅ agent_id, agent_version
  ✅ trigger type and source
  ✅ user_id (if user-triggered)
  ✅ company_id, plant_id, role context
  ✅ Full input (messages, tool calls, tool results)
  ✅ Full output (response, recommendations, actions)
  ✅ Token usage and cost
  ✅ Duration
  ✅ Confidence scores
  ✅ Human approval/rejection decisions
  ✅ Final outcome if action was executed
```

### 9.2 Audit Retention

- AI run logs: **7 năm** (không thể xóa)
- AI messages: **3 năm**
- AI recommendations: **5 năm** (kể cả rejected)

### 9.3 Compliance Export

```
Auditor Request → Export AI Decision Audit Trail
  - Filtered by: date range, agent, domain, user
  - Format: PDF report + CSV data
  - Includes: every recommendation, human decision, outcome
```

---

## 10. AI Performance Monitoring

### 10.1 Key Metrics

| Metric | Target | Alert Threshold |
|--------|--------|----------------|
| Recommendation Acceptance Rate | > 70% | < 50% |
| Recommendation Accuracy (outcome) | > 80% | < 60% |
| P95 Response Time | < 5s | > 10s |
| Daily Active AI Recommendations | Track | Sudden drop |
| AI Cost per Day | Budget | > 2× budget |

### 10.2 A/B Testing Support

- Multiple prompt versions can be tested simultaneously
- Acceptance rate tracked per prompt version
- Auto-promote winning version after statistical significance

---

## 11. AI Infrastructure

### 11.1 LLM Provider Configuration

```python
LLM_CONFIG = {
    "primary": {
        "provider": "anthropic",
        "model": "claude-opus-4-7",
        "use_for": ["analysis", "planning", "complex_reasoning"]
    },
    "fast": {
        "provider": "anthropic",
        "model": "claude-haiku-4-5-20251001",
        "use_for": ["classification", "extraction", "simple_qa"]
    },
    "fallback": {
        "provider": "openai",
        "model": "gpt-4o",
        "use_for": ["fallback_if_anthropic_down"]
    }
}
```

### 11.2 Cost Management

```python
COST_LIMITS = {
    "per_run_max_tokens": 100_000,
    "per_day_usd_limit": 50.00,
    "alert_threshold_usd": 40.00,
    "per_tenant_daily_limit": 10.00,
}
```

### 11.3 Queue Architecture

```
AI Request Queue (Redis)
    │
    ├── Priority 0: Critical alerts        (process immediately)
    ├── Priority 1: User-triggered         (process < 5s)
    ├── Priority 2: Event-triggered        (process < 30s)
    └── Priority 3: Scheduled/batch        (process < 5min)

Workers: FastAPI background tasks + Celery workers
```
