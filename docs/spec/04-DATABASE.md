# PHẦN 4 — DATABASE ARCHITECTURE
## Kiến trúc Dữ liệu Chuẩn Enterprise (120+ Bảng)

---

## 1. Database Principles

### 1.1 Core Rules

```
✅ Multi-tenant: company_id bắt buộc trong MỌI bảng nghiệp vụ
✅ UUID primary key: toàn bộ hệ thống
✅ Append-only ledger: transaction tables không được UPDATE/DELETE
✅ Soft delete: chỉ áp dụng cho master data (deleted_at)
✅ Audit timestamps: created_at, updated_at, deleted_at
✅ Created by / Updated by: created_by_id, updated_by_id
✅ JSONB for flexible data: attributes, metadata, config
✅ Decimal precision: decimal(20,4) cho tiền, decimal(18,4) cho số lượng
```

### 1.2 Multi-tenant Pattern

```sql
-- Mọi bảng nghiệp vụ phải có:
company_id  UUID NOT NULL REFERENCES companies(id)
plant_id    UUID NULLABLE REFERENCES plants(id)

-- Row-level security policy (PostgreSQL RLS):
CREATE POLICY tenant_isolation ON work_orders
    USING (company_id = current_setting('app.current_company_id')::uuid);
```

### 1.3 Append-only Ledger Pattern

```sql
-- Ledger tables: KHÔNG có UPDATE, KHÔNG có DELETE
-- Để đảo ngược: tạo reversal entry với quantity âm hoặc type = 'reversal'

CREATE TABLE inventory_transactions (
    id          UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id  UUID NOT NULL,
    -- ... fields ...
    -- KHÔNG có deleted_at
    -- KHÔNG có updated_at (chỉ có created_at)
);

-- Trigger chặn UPDATE/DELETE:
CREATE RULE no_update_inventory_tx AS ON UPDATE TO inventory_transactions
    DO INSTEAD NOTHING;
```

### 1.4 Standard Column Conventions

| Column Pattern | Type | Ý nghĩa |
|---------------|------|---------|
| `id` | UUID PK | Primary key |
| `company_id` | UUID FK | Tenant |
| `plant_id` | UUID FK (nullable) | Nhà máy |
| `code` | VARCHAR(50) | Mã nghiệp vụ (unique per tenant) |
| `name` | VARCHAR(255) | Tên |
| `status` | VARCHAR(50) | Trạng thái |
| `meta` | JSONB | Dữ liệu mở rộng |
| `created_by_id` | UUID FK | Người tạo |
| `updated_by_id` | UUID FK | Người cập nhật |
| `created_at` | TIMESTAMP | |
| `updated_at` | TIMESTAMP | |
| `deleted_at` | TIMESTAMP (nullable) | Soft delete |

---

## 2. Migration Phases

```
Phase 1:  IAM & Organization Foundation
Phase 2:  Master Data
Phase 3:  CRM & Market
Phase 4:  Production (MES)
Phase 5:  Inventory & WIP
Phase 6:  Finance
Phase 7:  HR & Payroll
Phase 8:  Governance & ESG
Phase 9:  AI System
Phase 10: Control Tower
```

**Migration Rule (bắt buộc):** Parent → Child → Pivot (không FK lỗi)

---

## 3. Phase 1 — IAM & Organization Foundation

### 3.1 Tables List

```
companies
business_units
plants
departments
workshops
production_lines
machines
machine_categories

users
user_profiles
roles
permissions
model_has_roles
model_has_permissions
role_has_permissions

user_company_scopes
user_plant_scopes
user_work_contexts
```

### 3.2 Core Schemas

```sql
-- =============================================
-- companies
-- =============================================
CREATE TABLE companies (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    parent_id       UUID REFERENCES companies(id),
    code            VARCHAR(20) NOT NULL UNIQUE,
    name            VARCHAR(255) NOT NULL,
    legal_name      VARCHAR(255),
    tax_code        VARCHAR(20),
    company_type    VARCHAR(50) NOT NULL,
    -- Types: group, subsidiary, branch, holding
    currency_code   CHAR(3) NOT NULL DEFAULT 'VND',
    fiscal_year_start SMALLINT DEFAULT 1,
    status          VARCHAR(20) NOT NULL DEFAULT 'active',
    address         JSONB,
    contact         JSONB,
    meta            JSONB DEFAULT '{}',
    created_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMP NOT NULL DEFAULT NOW()
);

-- =============================================
-- plants
-- =============================================
CREATE TABLE plants (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id      UUID NOT NULL REFERENCES companies(id),
    code            VARCHAR(20) NOT NULL,
    name            VARCHAR(255) NOT NULL,
    plant_type      VARCHAR(50),
    -- Types: manufacturing, warehouse, office, distribution
    manager_user_id UUID REFERENCES users(id),
    address         JSONB,
    coordinates     JSONB,
    -- {"lat": 10.123, "lng": 106.456}
    status          VARCHAR(20) NOT NULL DEFAULT 'active',
    meta            JSONB DEFAULT '{}',
    created_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    deleted_at      TIMESTAMP,
    UNIQUE(company_id, code)
);

-- =============================================
-- departments
-- =============================================
CREATE TABLE departments (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id      UUID NOT NULL REFERENCES companies(id),
    plant_id        UUID REFERENCES plants(id),
    parent_id       UUID REFERENCES departments(id),
    code            VARCHAR(20) NOT NULL,
    name            VARCHAR(255) NOT NULL,
    dept_type       VARCHAR(50),
    head_user_id    UUID REFERENCES users(id),
    cost_center_id  UUID,
    status          VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    deleted_at      TIMESTAMP,
    UNIQUE(company_id, code)
);

-- =============================================
-- workshops
-- =============================================
CREATE TABLE workshops (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id      UUID NOT NULL REFERENCES companies(id),
    plant_id        UUID NOT NULL REFERENCES plants(id),
    department_id   UUID REFERENCES departments(id),
    code            VARCHAR(20) NOT NULL,
    name            VARCHAR(255) NOT NULL,
    supervisor_id   UUID REFERENCES users(id),
    status          VARCHAR(20) NOT NULL DEFAULT 'active',
    meta            JSONB DEFAULT '{}',
    created_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    deleted_at      TIMESTAMP,
    UNIQUE(plant_id, code)
);

-- =============================================
-- production_lines
-- =============================================
CREATE TABLE production_lines (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id      UUID NOT NULL REFERENCES companies(id),
    plant_id        UUID NOT NULL REFERENCES plants(id),
    workshop_id     UUID REFERENCES workshops(id),
    code            VARCHAR(20) NOT NULL,
    name            VARCHAR(255) NOT NULL,
    line_type       VARCHAR(50),
    capacity_per_hour DECIMAL(18,4),
    capacity_uom    VARCHAR(20),
    status          VARCHAR(20) NOT NULL DEFAULT 'active',
    meta            JSONB DEFAULT '{}',
    created_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    deleted_at      TIMESTAMP,
    UNIQUE(plant_id, code)
);

-- =============================================
-- machines
-- =============================================
CREATE TABLE machines (
    id                  UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id          UUID NOT NULL REFERENCES companies(id),
    plant_id            UUID NOT NULL REFERENCES plants(id),
    workshop_id         UUID REFERENCES workshops(id),
    line_id             UUID REFERENCES production_lines(id),
    machine_category_id UUID REFERENCES machine_categories(id),
    code                VARCHAR(20) NOT NULL,
    name                VARCHAR(255) NOT NULL,
    serial_number       VARCHAR(100),
    model               VARCHAR(100),
    manufacturer        VARCHAR(100),
    purchase_date       DATE,
    theoretical_capacity DECIMAL(18,4),
    capacity_uom        VARCHAR(20),
    status              VARCHAR(20) NOT NULL DEFAULT 'active',
    -- active, maintenance, breakdown, retired
    meta                JSONB DEFAULT '{}',
    created_at          TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at          TIMESTAMP NOT NULL DEFAULT NOW(),
    deleted_at          TIMESTAMP,
    UNIQUE(plant_id, code)
);

-- =============================================
-- users
-- =============================================
CREATE TABLE users (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    employee_id     UUID REFERENCES employees(id),
    email           VARCHAR(255) NOT NULL UNIQUE,
    phone           VARCHAR(20),
    password        VARCHAR(255) NOT NULL,
    display_name    VARCHAR(255) NOT NULL,
    avatar_url      VARCHAR(500),
    locale          VARCHAR(10) DEFAULT 'vi',
    timezone        VARCHAR(50) DEFAULT 'Asia/Ho_Chi_Minh',
    status          VARCHAR(20) NOT NULL DEFAULT 'active',
    email_verified_at TIMESTAMP,
    last_login_at   TIMESTAMP,
    last_login_ip   INET,
    meta            JSONB DEFAULT '{}',
    created_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    deleted_at      TIMESTAMP
);

-- =============================================
-- user_work_contexts (active context per user)
-- =============================================
CREATE TABLE user_work_contexts (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id         UUID NOT NULL REFERENCES users(id) UNIQUE,
    company_id      UUID NOT NULL REFERENCES companies(id),
    plant_id        UUID REFERENCES plants(id),
    department_id   UUID REFERENCES departments(id),
    role_id         UUID NOT NULL REFERENCES roles(id),
    context_name    VARCHAR(100),
    switched_at     TIMESTAMP NOT NULL DEFAULT NOW()
);
```

---

## 4. Phase 2 — Master Data

### 4.1 Tables List

```
currencies
units_of_measure
calendars
shifts
shift_templates
holidays

warehouses
warehouse_zones
warehouse_racks
warehouse_bins

product_categories
products
product_variants
materials
material_categories

customers
customer_contacts
customer_addresses
customer_price_lists

suppliers
supplier_contacts
supplier_evaluations

boms
bom_items
bom_item_substitutes

routings
routing_steps
routing_step_resources

price_lists
price_list_items
```

### 4.2 Key Schemas

```sql
-- =============================================
-- products
-- =============================================
CREATE TABLE products (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id      UUID NOT NULL REFERENCES companies(id),
    category_id     UUID REFERENCES product_categories(id),
    code            VARCHAR(50) NOT NULL,
    name            VARCHAR(255) NOT NULL,
    description     TEXT,
    product_type    VARCHAR(50) NOT NULL,
    -- finished_good, semi_finished, raw_material, service
    base_uom        VARCHAR(20) NOT NULL,
    weight_kg       DECIMAL(18,4),
    dimensions      JSONB,
    -- {"length_mm": 100, "width_mm": 50, "height_mm": 30}
    standard_cost   DECIMAL(20,4) DEFAULT 0,
    list_price      DECIMAL(20,4) DEFAULT 0,
    currency_code   CHAR(3) DEFAULT 'VND',
    lead_time_days  SMALLINT DEFAULT 0,
    min_order_qty   DECIMAL(18,4) DEFAULT 1,
    reorder_point   DECIMAL(18,4) DEFAULT 0,
    safety_stock    DECIMAL(18,4) DEFAULT 0,
    is_active       BOOLEAN DEFAULT true,
    attributes      JSONB DEFAULT '{}',
    meta            JSONB DEFAULT '{}',
    created_by_id   UUID REFERENCES users(id),
    updated_by_id   UUID REFERENCES users(id),
    created_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    deleted_at      TIMESTAMP,
    UNIQUE(company_id, code)
);

-- =============================================
-- boms (Bill of Materials)
-- =============================================
CREATE TABLE boms (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id      UUID NOT NULL REFERENCES companies(id),
    product_id      UUID NOT NULL REFERENCES products(id),
    code            VARCHAR(50) NOT NULL,
    name            VARCHAR(255),
    version         VARCHAR(20) NOT NULL DEFAULT '1.0',
    uom             VARCHAR(20) NOT NULL,
    quantity        DECIMAL(18,4) NOT NULL DEFAULT 1,
    -- Quantity of finished product this BOM produces
    effective_from  DATE NOT NULL,
    effective_to    DATE,
    is_active       BOOLEAN DEFAULT true,
    notes           TEXT,
    created_by_id   UUID REFERENCES users(id),
    created_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    UNIQUE(company_id, code, version)
);

CREATE TABLE bom_items (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    bom_id          UUID NOT NULL REFERENCES boms(id) ON DELETE CASCADE,
    company_id      UUID NOT NULL REFERENCES companies(id),
    sequence        SMALLINT NOT NULL DEFAULT 10,
    material_id     UUID NOT NULL REFERENCES products(id),
    quantity        DECIMAL(18,4) NOT NULL,
    uom             VARCHAR(20) NOT NULL,
    scrap_pct       DECIMAL(8,4) DEFAULT 0,
    -- % hao hụt định mức
    is_phantom      BOOLEAN DEFAULT false,
    operation_step  VARCHAR(50),
    -- Gắn với bước gia công nào
    notes           TEXT,
    created_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMP NOT NULL DEFAULT NOW()
);

-- =============================================
-- routings
-- =============================================
CREATE TABLE routings (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id      UUID NOT NULL REFERENCES companies(id),
    product_id      UUID NOT NULL REFERENCES products(id),
    code            VARCHAR(50) NOT NULL,
    name            VARCHAR(255),
    version         VARCHAR(20) NOT NULL DEFAULT '1.0',
    effective_from  DATE NOT NULL,
    effective_to    DATE,
    is_active       BOOLEAN DEFAULT true,
    created_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    UNIQUE(company_id, code, version)
);

CREATE TABLE routing_steps (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    routing_id      UUID NOT NULL REFERENCES routings(id) ON DELETE CASCADE,
    company_id      UUID NOT NULL REFERENCES companies(id),
    step_number     SMALLINT NOT NULL,
    name            VARCHAR(255) NOT NULL,
    operation_code  VARCHAR(50),
    workshop_id     UUID REFERENCES workshops(id),
    line_id         UUID REFERENCES production_lines(id),
    machine_category_id UUID REFERENCES machine_categories(id),
    std_time_minutes DECIMAL(10,4),
    -- Thời gian chuẩn mực (phút/đơn vị)
    setup_time_minutes DECIMAL(10,4),
    labor_count     SMALLINT DEFAULT 1,
    output_product_id UUID REFERENCES products(id),
    -- BTP đầu ra của bước này
    yield_pct       DECIMAL(8,4) DEFAULT 100,
    notes           TEXT,
    created_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMP NOT NULL DEFAULT NOW()
);

-- =============================================
-- warehouses
-- =============================================
CREATE TABLE warehouses (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id      UUID NOT NULL REFERENCES companies(id),
    plant_id        UUID NOT NULL REFERENCES plants(id),
    code            VARCHAR(20) NOT NULL,
    name            VARCHAR(255) NOT NULL,
    warehouse_type  VARCHAR(50) NOT NULL,
    -- raw_material, wip, finished_goods, quarantine, scrap
    is_active       BOOLEAN DEFAULT true,
    meta            JSONB DEFAULT '{}',
    created_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    deleted_at      TIMESTAMP,
    UNIQUE(plant_id, code)
);

CREATE TABLE warehouse_bins (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id      UUID NOT NULL REFERENCES companies(id),
    warehouse_id    UUID NOT NULL REFERENCES warehouses(id),
    zone_id         UUID REFERENCES warehouse_zones(id),
    rack_id         UUID REFERENCES warehouse_racks(id),
    code            VARCHAR(30) NOT NULL,
    bin_type        VARCHAR(30),
    qr_code         VARCHAR(100) UNIQUE,
    max_weight_kg   DECIMAL(10,2),
    max_volume_m3   DECIMAL(10,4),
    is_active       BOOLEAN DEFAULT true,
    created_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    UNIQUE(warehouse_id, code)
);
```

---

## 5. Phase 3 — CRM & Market

```sql
-- leads, opportunities, quotations, quotation_items
-- sales_orders, sales_order_lines
-- customer_interactions, service_cases, contracts
-- delivery_orders, delivery_order_lines

CREATE TABLE sales_orders (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id      UUID NOT NULL REFERENCES companies(id),
    so_number       VARCHAR(30) NOT NULL,
    customer_id     UUID NOT NULL REFERENCES customers(id),
    quotation_id    UUID REFERENCES quotations(id),
    order_date      DATE NOT NULL,
    requested_date  DATE,
    confirmed_date  DATE,
    delivery_date   DATE,
    -- ATP/CTP confirmed date
    status          VARCHAR(30) NOT NULL DEFAULT 'draft',
    -- draft, confirmed, in_production, partial_delivery, completed, cancelled
    currency_code   CHAR(3) NOT NULL DEFAULT 'VND',
    exchange_rate   DECIMAL(18,6) DEFAULT 1,
    subtotal        DECIMAL(20,4) DEFAULT 0,
    tax_amount      DECIMAL(20,4) DEFAULT 0,
    total_amount    DECIMAL(20,4) DEFAULT 0,
    payment_terms   VARCHAR(100),
    incoterms       VARCHAR(20),
    shipping_address JSONB,
    notes           TEXT,
    approved_by_id  UUID REFERENCES users(id),
    approved_at     TIMESTAMP,
    created_by_id   UUID REFERENCES users(id),
    created_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    UNIQUE(company_id, so_number)
);

CREATE TABLE sales_order_lines (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    sales_order_id  UUID NOT NULL REFERENCES sales_orders(id) ON DELETE CASCADE,
    company_id      UUID NOT NULL REFERENCES companies(id),
    line_number     SMALLINT NOT NULL,
    product_id      UUID NOT NULL REFERENCES products(id),
    description     VARCHAR(500),
    quantity        DECIMAL(18,4) NOT NULL,
    uom             VARCHAR(20) NOT NULL,
    unit_price      DECIMAL(20,4) NOT NULL,
    discount_pct    DECIMAL(8,4) DEFAULT 0,
    tax_rate        DECIMAL(8,4) DEFAULT 0,
    line_total      DECIMAL(20,4) NOT NULL,
    requested_date  DATE,
    confirmed_date  DATE,
    -- ATP/CTP date per line
    delivered_qty   DECIMAL(18,4) DEFAULT 0,
    status          VARCHAR(30) DEFAULT 'open',
    work_order_id   UUID REFERENCES work_orders(id),
    notes           TEXT,
    created_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMP NOT NULL DEFAULT NOW()
);
```

---

## 6. Phase 4 — Production (MES)

```sql
-- =============================================
-- production_plans
-- =============================================
CREATE TABLE production_plans (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id      UUID NOT NULL REFERENCES companies(id),
    plant_id        UUID NOT NULL REFERENCES plants(id),
    plan_number     VARCHAR(30) NOT NULL,
    period_type     VARCHAR(20) NOT NULL,
    -- daily, weekly, monthly
    period_start    DATE NOT NULL,
    period_end      DATE NOT NULL,
    status          VARCHAR(30) NOT NULL DEFAULT 'draft',
    -- draft, submitted, approved, released, closed
    total_work_orders INT DEFAULT 0,
    approved_by_id  UUID REFERENCES users(id),
    approved_at     TIMESTAMP,
    released_at     TIMESTAMP,
    notes           TEXT,
    created_by_id   UUID REFERENCES users(id),
    created_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    UNIQUE(company_id, plant_id, plan_number)
);

-- =============================================
-- work_orders
-- =============================================
CREATE TABLE work_orders (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id      UUID NOT NULL REFERENCES companies(id),
    plant_id        UUID NOT NULL REFERENCES plants(id),
    plan_id         UUID REFERENCES production_plans(id),
    sales_order_line_id UUID REFERENCES sales_order_lines(id),
    wo_number       VARCHAR(30) NOT NULL,
    product_id      UUID NOT NULL REFERENCES products(id),
    bom_id          UUID NOT NULL REFERENCES boms(id),
    routing_id      UUID NOT NULL REFERENCES routings(id),
    planned_quantity    DECIMAL(18,4) NOT NULL,
    completed_quantity  DECIMAL(18,4) DEFAULT 0,
    scrap_quantity      DECIMAL(18,4) DEFAULT 0,
    uom             VARCHAR(20) NOT NULL,
    planned_start   TIMESTAMP NOT NULL,
    planned_end     TIMESTAMP NOT NULL,
    actual_start    TIMESTAMP,
    actual_end      TIMESTAMP,
    status          VARCHAR(30) NOT NULL DEFAULT 'planned',
    -- planned, released, in_progress, paused, completed, closed, cancelled
    priority        SMALLINT DEFAULT 50,
    -- 0=Critical, 25=High, 50=Normal, 75=Low
    workshop_id     UUID REFERENCES workshops(id),
    line_id         UUID REFERENCES production_lines(id),
    notes           TEXT,
    created_by_id   UUID REFERENCES users(id),
    updated_by_id   UUID REFERENCES users(id),
    created_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    UNIQUE(company_id, wo_number)
);

-- =============================================
-- work_order_operations
-- =============================================
CREATE TABLE work_order_operations (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    work_order_id   UUID NOT NULL REFERENCES work_orders(id) ON DELETE CASCADE,
    company_id      UUID NOT NULL REFERENCES companies(id),
    routing_step_id UUID NOT NULL REFERENCES routing_steps(id),
    step_number     SMALLINT NOT NULL,
    machine_id      UUID REFERENCES machines(id),
    operator_id     UUID REFERENCES employees(id),
    planned_quantity    DECIMAL(18,4),
    completed_quantity  DECIMAL(18,4) DEFAULT 0,
    scrap_quantity      DECIMAL(18,4) DEFAULT 0,
    planned_start   TIMESTAMP,
    planned_end     TIMESTAMP,
    actual_start    TIMESTAMP,
    actual_end      TIMESTAMP,
    status          VARCHAR(30) DEFAULT 'pending',
    notes           TEXT,
    created_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMP NOT NULL DEFAULT NOW()
);

-- =============================================
-- machine_runtime_logs (APPEND-ONLY LEDGER)
-- =============================================
CREATE TABLE machine_runtime_logs (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id      UUID NOT NULL REFERENCES companies(id),
    plant_id        UUID NOT NULL REFERENCES plants(id),
    machine_id      UUID NOT NULL REFERENCES machines(id),
    work_order_id   UUID REFERENCES work_orders(id),
    operation_id    UUID REFERENCES work_order_operations(id),
    log_type        VARCHAR(30) NOT NULL,
    -- production, setup, idle, maintenance
    started_at      TIMESTAMP NOT NULL,
    ended_at        TIMESTAMP,
    duration_minutes DECIMAL(10,2),
    quantity_produced DECIMAL(18,4) DEFAULT 0,
    quantity_scrapped DECIMAL(18,4) DEFAULT 0,
    operator_id     UUID REFERENCES employees(id),
    shift_id        UUID REFERENCES shifts(id),
    notes           TEXT,
    created_at      TIMESTAMP NOT NULL DEFAULT NOW()
    -- NO updated_at, NO deleted_at — append-only
);

-- =============================================
-- downtime_events (APPEND-ONLY LEDGER)
-- =============================================
CREATE TABLE downtime_events (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id      UUID NOT NULL REFERENCES companies(id),
    plant_id        UUID NOT NULL REFERENCES plants(id),
    machine_id      UUID NOT NULL REFERENCES machines(id),
    work_order_id   UUID REFERENCES work_orders(id),
    downtime_reason_id UUID REFERENCES downtime_reasons(id),
    started_at      TIMESTAMP NOT NULL,
    ended_at        TIMESTAMP,
    duration_minutes DECIMAL(10,2),
    reported_by_id  UUID REFERENCES users(id),
    resolved_by_id  UUID REFERENCES users(id),
    root_cause      TEXT,
    corrective_action TEXT,
    ticket_id       UUID,
    -- References action_items
    created_at      TIMESTAMP NOT NULL DEFAULT NOW()
);

-- =============================================
-- qc_events (APPEND-ONLY LEDGER)
-- =============================================
CREATE TABLE qc_events (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id      UUID NOT NULL REFERENCES companies(id),
    plant_id        UUID NOT NULL REFERENCES plants(id),
    qc_type         VARCHAR(20) NOT NULL,
    -- IQC, IPQC, OQC, FQC
    work_order_id   UUID REFERENCES work_orders(id),
    lot_id          UUID REFERENCES lots(id),
    operation_id    UUID REFERENCES work_order_operations(id),
    inspector_id    UUID REFERENCES employees(id),
    inspected_at    TIMESTAMP NOT NULL,
    sample_size     DECIMAL(18,4),
    pass_quantity   DECIMAL(18,4) DEFAULT 0,
    fail_quantity   DECIMAL(18,4) DEFAULT 0,
    result          VARCHAR(20) NOT NULL,
    -- pass, fail, hold, conditional_pass
    defects         JSONB DEFAULT '[]',
    -- [{"defect_type_id": "...", "qty": 5, "severity": "major"}]
    images          JSONB DEFAULT '[]',
    capa_id         UUID,
    notes           TEXT,
    created_at      TIMESTAMP NOT NULL DEFAULT NOW()
);
```

---

## 7. Phase 5 — Inventory & WIP

```sql
-- =============================================
-- lots (Lô sản phẩm / nguyên liệu)
-- =============================================
CREATE TABLE lots (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id      UUID NOT NULL REFERENCES companies(id),
    plant_id        UUID NOT NULL REFERENCES plants(id),
    lot_number      VARCHAR(50) NOT NULL,
    product_id      UUID NOT NULL REFERENCES products(id),
    uom             VARCHAR(20) NOT NULL,
    quantity        DECIMAL(18,4) NOT NULL,
    available_qty   DECIMAL(18,4) NOT NULL,
    reserved_qty    DECIMAL(18,4) DEFAULT 0,
    qc_status       VARCHAR(20) NOT NULL DEFAULT 'pending',
    -- pending, in_inspection, pass, fail, hold, quarantine
    warehouse_id    UUID REFERENCES warehouses(id),
    bin_id          UUID REFERENCES warehouse_bins(id),
    qr_code         VARCHAR(100) UNIQUE,
    lot_type        VARCHAR(30),
    -- rm_lot, wip_lot, fg_lot, sub_lot
    work_order_id   UUID REFERENCES work_orders(id),
    -- WO nào sản xuất ra lot này (nếu là WIP/FG)
    production_date DATE,
    expiry_date     DATE,
    origin          VARCHAR(50),
    supplier_id     UUID REFERENCES suppliers(id),
    supplier_lot_number VARCHAR(50),
    attributes      JSONB DEFAULT '{}',
    created_by_id   UUID REFERENCES users(id),
    created_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMP NOT NULL DEFAULT NOW()
);

-- =============================================
-- inventory_transactions (APPEND-ONLY LEDGER — CRITICAL)
-- =============================================
CREATE TABLE inventory_transactions (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id      UUID NOT NULL REFERENCES companies(id),
    plant_id        UUID NOT NULL REFERENCES plants(id),
    txn_number      VARCHAR(30) NOT NULL,
    txn_type        VARCHAR(30) NOT NULL,
    -- receipt, transfer, issue, return, adjustment, scrap, sub_out, sub_in, reversal
    txn_date        TIMESTAMP NOT NULL,
    product_id      UUID NOT NULL REFERENCES products(id),
    lot_id          UUID REFERENCES lots(id),
    from_warehouse_id   UUID REFERENCES warehouses(id),
    from_bin_id         UUID REFERENCES warehouse_bins(id),
    to_warehouse_id     UUID REFERENCES warehouses(id),
    to_bin_id           UUID REFERENCES warehouse_bins(id),
    quantity        DECIMAL(18,4) NOT NULL,
    -- Positive = IN, Negative = OUT
    uom             VARCHAR(20) NOT NULL,
    unit_cost       DECIMAL(20,4),
    total_cost      DECIMAL(20,4),
    reference_type  VARCHAR(50),
    -- work_order, sales_order, purchase_order, etc.
    reference_id    UUID,
    reversed_txn_id UUID REFERENCES inventory_transactions(id),
    performed_by_id UUID REFERENCES users(id),
    notes           TEXT,
    created_at      TIMESTAMP NOT NULL DEFAULT NOW()
    -- NO updated_at — APPEND ONLY
);

-- =============================================
-- material_issue_slips
-- =============================================
CREATE TABLE material_issue_slips (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id      UUID NOT NULL REFERENCES companies(id),
    plant_id        UUID NOT NULL REFERENCES plants(id),
    slip_number     VARCHAR(30) NOT NULL,
    work_order_id   UUID NOT NULL REFERENCES work_orders(id),
    issue_date      DATE NOT NULL,
    status          VARCHAR(20) DEFAULT 'pending',
    -- pending, issued, cancelled
    issued_by_id    UUID REFERENCES users(id),
    issued_at       TIMESTAMP,
    notes           TEXT,
    created_by_id   UUID REFERENCES users(id),
    created_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    UNIQUE(company_id, slip_number)
);

CREATE TABLE material_issue_slip_lines (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    slip_id         UUID NOT NULL REFERENCES material_issue_slips(id),
    company_id      UUID NOT NULL REFERENCES companies(id),
    bom_item_id     UUID REFERENCES bom_items(id),
    product_id      UUID NOT NULL REFERENCES products(id),
    lot_id          UUID REFERENCES lots(id),
    required_qty    DECIMAL(18,4) NOT NULL,
    issued_qty      DECIMAL(18,4) DEFAULT 0,
    uom             VARCHAR(20) NOT NULL,
    status          VARCHAR(20) DEFAULT 'pending',
    created_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMP NOT NULL DEFAULT NOW()
);
```

---

## 8. Phase 6 — Finance

```sql
-- =============================================
-- chart_of_accounts
-- =============================================
CREATE TABLE chart_of_accounts (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id      UUID NOT NULL REFERENCES companies(id),
    parent_id       UUID REFERENCES chart_of_accounts(id),
    account_code    VARCHAR(20) NOT NULL,
    account_name    VARCHAR(255) NOT NULL,
    account_type    VARCHAR(30) NOT NULL,
    -- asset, liability, equity, revenue, expense, cogs
    normal_balance  VARCHAR(10) NOT NULL DEFAULT 'debit',
    is_posting      BOOLEAN DEFAULT true,
    currency_code   CHAR(3) DEFAULT 'VND',
    is_active       BOOLEAN DEFAULT true,
    created_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    UNIQUE(company_id, account_code)
);

-- =============================================
-- journal_entries (APPEND-ONLY LEDGER)
-- =============================================
CREATE TABLE journal_entries (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id      UUID NOT NULL REFERENCES companies(id),
    je_number       VARCHAR(30) NOT NULL,
    je_date         DATE NOT NULL,
    period          VARCHAR(7) NOT NULL,
    -- YYYY-MM
    je_type         VARCHAR(30) NOT NULL,
    -- manual, auto_costing, payroll, ar, ap, inventory, reversal
    description     VARCHAR(500),
    reference_type  VARCHAR(50),
    reference_id    UUID,
    is_posted       BOOLEAN DEFAULT false,
    posted_at       TIMESTAMP,
    posted_by_id    UUID REFERENCES users(id),
    is_reversed     BOOLEAN DEFAULT false,
    reversal_je_id  UUID REFERENCES journal_entries(id),
    created_by_id   UUID REFERENCES users(id),
    created_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    UNIQUE(company_id, je_number)
);

CREATE TABLE journal_lines (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    journal_entry_id UUID NOT NULL REFERENCES journal_entries(id),
    company_id      UUID NOT NULL REFERENCES companies(id),
    line_number     SMALLINT NOT NULL,
    account_id      UUID NOT NULL REFERENCES chart_of_accounts(id),
    cost_center_id  UUID REFERENCES cost_centers(id),
    plant_id        UUID REFERENCES plants(id),
    debit_amount    DECIMAL(20,4) DEFAULT 0,
    credit_amount   DECIMAL(20,4) DEFAULT 0,
    currency_code   CHAR(3) NOT NULL DEFAULT 'VND',
    exchange_rate   DECIMAL(18,6) DEFAULT 1,
    description     VARCHAR(500),
    reference_type  VARCHAR(50),
    reference_id    UUID,
    created_at      TIMESTAMP NOT NULL DEFAULT NOW()
);

-- =============================================
-- cost_variances (APPEND-ONLY LEDGER)
-- =============================================
CREATE TABLE cost_variances (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id      UUID NOT NULL REFERENCES companies(id),
    plant_id        UUID NOT NULL REFERENCES plants(id),
    period          VARCHAR(7) NOT NULL,
    work_order_id   UUID REFERENCES work_orders(id),
    product_id      UUID NOT NULL REFERENCES products(id),
    variance_type   VARCHAR(20) NOT NULL,
    -- MPV, MUV, LEV, LRV, OHV
    component       VARCHAR(50),
    standard_value  DECIMAL(20,4),
    actual_value    DECIMAL(20,4),
    variance_amount DECIMAL(20,4),
    explanation     TEXT,
    created_at      TIMESTAMP NOT NULL DEFAULT NOW()
);
```

---

## 9. Phase 7 — HR & Payroll

```sql
-- employees, positions, job_titles, employment_contracts
-- attendance_events (LEDGER), shift_assignments
-- kpi_periods, kpi_definitions, kpi_targets, kpi_values
-- payroll_runs, payroll_run_items, payroll_deductions

CREATE TABLE employees (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id      UUID NOT NULL REFERENCES companies(id),
    plant_id        UUID REFERENCES plants(id),
    department_id   UUID REFERENCES departments(id),
    user_id         UUID UNIQUE REFERENCES users(id),
    employee_code   VARCHAR(30) NOT NULL,
    full_name       VARCHAR(255) NOT NULL,
    date_of_birth   DATE,
    gender          VARCHAR(10),
    id_number       VARCHAR(20),
    tax_code        VARCHAR(20),
    social_insurance_number VARCHAR(20),
    bank_account    JSONB,
    position_id     UUID REFERENCES positions(id),
    hire_date       DATE NOT NULL,
    probation_end   DATE,
    employment_type VARCHAR(30),
    -- full_time, part_time, contractor, probation
    status          VARCHAR(20) DEFAULT 'active',
    salary_p1       DECIMAL(20,4) DEFAULT 0,
    salary_p2       DECIMAL(20,4) DEFAULT 0,
    meta            JSONB DEFAULT '{}',
    created_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    deleted_at      TIMESTAMP,
    UNIQUE(company_id, employee_code)
);

CREATE TABLE payroll_runs (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id      UUID NOT NULL REFERENCES companies(id),
    plant_id        UUID REFERENCES plants(id),
    period          VARCHAR(7) NOT NULL,
    -- YYYY-MM
    run_number      VARCHAR(30) NOT NULL,
    status          VARCHAR(20) DEFAULT 'draft',
    -- draft, calculated, reviewed, approved, paid
    total_employees INT DEFAULT 0,
    total_gross     DECIMAL(20,4) DEFAULT 0,
    total_deductions DECIMAL(20,4) DEFAULT 0,
    total_net       DECIMAL(20,4) DEFAULT 0,
    calculated_at   TIMESTAMP,
    approved_by_id  UUID REFERENCES users(id),
    approved_at     TIMESTAMP,
    paid_at         TIMESTAMP,
    notes           TEXT,
    created_by_id   UUID REFERENCES users(id),
    created_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    UNIQUE(company_id, period, run_number)
);

CREATE TABLE payroll_run_items (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    payroll_run_id  UUID NOT NULL REFERENCES payroll_runs(id),
    company_id      UUID NOT NULL REFERENCES companies(id),
    employee_id     UUID NOT NULL REFERENCES employees(id),
    working_days    DECIMAL(5,2),
    actual_days     DECIMAL(5,2),
    overtime_hours  DECIMAL(8,2) DEFAULT 0,
    salary_p1       DECIMAL(20,4) DEFAULT 0,
    salary_p2       DECIMAL(20,4) DEFAULT 0,
    salary_p3       DECIMAL(20,4) DEFAULT 0,
    kpi_score       DECIMAL(8,4),
    -- P3 calculation basis
    gross_salary    DECIMAL(20,4) DEFAULT 0,
    bhxh_employee   DECIMAL(20,4) DEFAULT 0,
    bhyt_employee   DECIMAL(20,4) DEFAULT 0,
    bhtn_employee   DECIMAL(20,4) DEFAULT 0,
    pit_amount      DECIMAL(20,4) DEFAULT 0,
    other_deductions DECIMAL(20,4) DEFAULT 0,
    net_salary      DECIMAL(20,4) DEFAULT 0,
    p3_breakdown    JSONB DEFAULT '{}',
    created_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMP NOT NULL DEFAULT NOW()
);
```

---

## 10. Phase 8 — Governance & ESG

```sql
-- strategies, strategic_pillars, strategic_objectives
-- okr_periods, okrs, key_results, key_result_updates
-- risk_registers, risk_assessments
-- audit_programs, audits, audit_findings, capas
-- esg_targets, energy_records, water_records, waste_records, carbon_records

CREATE TABLE capas (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id      UUID NOT NULL REFERENCES companies(id),
    capa_number     VARCHAR(30) NOT NULL,
    capa_type       VARCHAR(20) NOT NULL,
    -- corrective, preventive
    source_type     VARCHAR(50),
    -- qc_event, audit_finding, customer_complaint, incident, risk
    source_id       UUID,
    title           VARCHAR(255) NOT NULL,
    description     TEXT,
    root_cause      TEXT,
    root_cause_method VARCHAR(30),
    -- 5_whys, fishbone, fault_tree
    corrective_actions  JSONB DEFAULT '[]',
    preventive_actions  JSONB DEFAULT '[]',
    owner_id        UUID NOT NULL REFERENCES employees(id),
    due_date        DATE NOT NULL,
    status          VARCHAR(30) DEFAULT 'open',
    -- open, in_progress, pending_verification, closed, overdue
    effectiveness_check_date DATE,
    effectiveness_result VARCHAR(20),
    -- effective, not_effective
    closed_by_id    UUID REFERENCES users(id),
    closed_at       TIMESTAMP,
    created_by_id   UUID REFERENCES users(id),
    created_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    UNIQUE(company_id, capa_number)
);
```

---

## 11. Phase 9 — AI System

```sql
-- =============================================
-- ai_agents
-- =============================================
CREATE TABLE ai_agents (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id      UUID REFERENCES companies(id),
    -- NULL = system agent (all tenants)
    code            VARCHAR(50) NOT NULL UNIQUE,
    name            VARCHAR(255) NOT NULL,
    description     TEXT,
    agent_type      VARCHAR(50) NOT NULL,
    -- executive, operations, finance, market, governance, audit, esg
    llm_provider    VARCHAR(50) NOT NULL DEFAULT 'anthropic',
    llm_model       VARCHAR(100) NOT NULL,
    system_prompt   TEXT NOT NULL,
    temperature     DECIMAL(3,2) DEFAULT 0.1,
    max_tokens      INT DEFAULT 4096,
    tools           JSONB DEFAULT '[]',
    -- List of tool codes this agent can use
    memory_config   JSONB DEFAULT '{}',
    guardrails      JSONB DEFAULT '{}',
    is_active       BOOLEAN DEFAULT true,
    created_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMP NOT NULL DEFAULT NOW()
);

-- =============================================
-- ai_runs (APPEND-ONLY LEDGER)
-- =============================================
CREATE TABLE ai_runs (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id      UUID NOT NULL REFERENCES companies(id),
    agent_id        UUID NOT NULL REFERENCES ai_agents(id),
    triggered_by    VARCHAR(30) NOT NULL,
    -- user, event, schedule, escalation
    trigger_user_id UUID REFERENCES users(id),
    trigger_event   VARCHAR(100),
    context         JSONB DEFAULT '{}',
    -- Current work context of user/event
    status          VARCHAR(20) DEFAULT 'running',
    -- running, completed, failed, cancelled
    input_tokens    INT,
    output_tokens   INT,
    cost_usd        DECIMAL(10,6),
    duration_ms     INT,
    result          JSONB,
    error_message   TEXT,
    started_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    completed_at    TIMESTAMP
);

-- =============================================
-- ai_messages (APPEND-ONLY LEDGER — Conversation History)
-- =============================================
CREATE TABLE ai_messages (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    run_id          UUID NOT NULL REFERENCES ai_runs(id),
    company_id      UUID NOT NULL REFERENCES companies(id),
    sequence        INT NOT NULL,
    role            VARCHAR(20) NOT NULL,
    -- user, assistant, tool, system
    content         TEXT,
    tool_calls      JSONB,
    tool_results    JSONB,
    created_at      TIMESTAMP NOT NULL DEFAULT NOW()
);

-- =============================================
-- ai_recommendations
-- =============================================
CREATE TABLE ai_recommendations (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id      UUID NOT NULL REFERENCES companies(id),
    run_id          UUID REFERENCES ai_runs(id),
    agent_id        UUID REFERENCES ai_agents(id),
    recommendation_type VARCHAR(50) NOT NULL,
    domain          VARCHAR(30) NOT NULL,
    -- operations, finance, hr, sales, risk, etc.
    title           VARCHAR(255) NOT NULL,
    summary         TEXT NOT NULL,
    details         JSONB DEFAULT '{}',
    suggested_actions JSONB DEFAULT '[]',
    confidence_score DECIMAL(5,4),
    -- 0.0 to 1.0
    priority        VARCHAR(10) DEFAULT 'medium',
    -- critical, high, medium, low
    reference_type  VARCHAR(50),
    reference_id    UUID,
    status          VARCHAR(20) DEFAULT 'pending',
    -- pending, accepted, rejected, in_progress, done
    reviewed_by_id  UUID REFERENCES users(id),
    reviewed_at     TIMESTAMP,
    action_item_id  UUID REFERENCES action_items(id),
    expires_at      TIMESTAMP,
    created_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMP NOT NULL DEFAULT NOW()
);
```

---

## 12. Phase 10 — Control Tower

```sql
-- dashboards, dashboard_widgets, dashboard_configs
-- alert_rules, alerts, alert_escalations
-- executive_signals, executive_briefings
-- action_items, decision_logs, workflow_instances, workflow_steps

CREATE TABLE alert_rules (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id      UUID REFERENCES companies(id),
    plant_id        UUID REFERENCES plants(id),
    code            VARCHAR(50) NOT NULL,
    name            VARCHAR(255) NOT NULL,
    domain          VARCHAR(30) NOT NULL,
    metric          VARCHAR(100) NOT NULL,
    condition       VARCHAR(200) NOT NULL,
    -- e.g. "oee < 0.7 AND duration > 60"
    severity        VARCHAR(20) NOT NULL DEFAULT 'medium',
    -- critical, high, medium, low, info
    notification_channels JSONB DEFAULT '["in_app"]',
    -- in_app, email, sms, slack, zalo
    auto_create_ticket BOOLEAN DEFAULT false,
    ticket_template JSONB,
    cooldown_minutes INT DEFAULT 60,
    is_active       BOOLEAN DEFAULT true,
    created_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE alerts (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id      UUID NOT NULL REFERENCES companies(id),
    plant_id        UUID REFERENCES plants(id),
    rule_id         UUID REFERENCES alert_rules(id),
    severity        VARCHAR(20) NOT NULL,
    title           VARCHAR(255) NOT NULL,
    message         TEXT NOT NULL,
    context         JSONB DEFAULT '{}',
    -- Raw data that triggered the alert
    status          VARCHAR(20) DEFAULT 'open',
    -- open, acknowledged, resolved, suppressed
    acknowledged_by_id UUID REFERENCES users(id),
    acknowledged_at TIMESTAMP,
    resolved_by_id  UUID REFERENCES users(id),
    resolved_at     TIMESTAMP,
    action_item_id  UUID REFERENCES action_items(id),
    triggered_at    TIMESTAMP NOT NULL DEFAULT NOW(),
    expires_at      TIMESTAMP
);

CREATE TABLE action_items (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id      UUID NOT NULL REFERENCES companies(id),
    plant_id        UUID REFERENCES plants(id),
    ticket_number   VARCHAR(30) NOT NULL,
    title           VARCHAR(255) NOT NULL,
    description     TEXT,
    source_type     VARCHAR(50),
    -- alert, ai_recommendation, decision, audit_finding, manual
    source_id       UUID,
    priority        VARCHAR(10) DEFAULT 'medium',
    status          VARCHAR(20) DEFAULT 'open',
    -- open, in_progress, pending_review, done, cancelled
    owner_id        UUID NOT NULL REFERENCES users(id),
    reviewer_id     UUID REFERENCES users(id),
    due_at          TIMESTAMP NOT NULL,
    sla_hours       DECIMAL(8,2),
    sla_breached_at TIMESTAMP,
    escalated_to_id UUID REFERENCES users(id),
    escalated_at    TIMESTAMP,
    completed_at    TIMESTAMP,
    result          TEXT,
    created_by_id   UUID REFERENCES users(id),
    created_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    UNIQUE(company_id, ticket_number)
);

CREATE TABLE decision_logs (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id      UUID NOT NULL REFERENCES companies(id),
    decision_number VARCHAR(30) NOT NULL,
    title           VARCHAR(255) NOT NULL,
    description     TEXT,
    decision_type   VARCHAR(50),
    context_data    JSONB DEFAULT '{}',
    alternatives    JSONB DEFAULT '[]',
    ai_recommendation_id UUID REFERENCES ai_recommendations(id),
    decision_made   TEXT NOT NULL,
    expected_outcome TEXT,
    actual_outcome  TEXT,
    decided_by_id   UUID NOT NULL REFERENCES users(id),
    decided_at      TIMESTAMP NOT NULL,
    reviewed_at     TIMESTAMP,
    outcome_updated_at TIMESTAMP,
    created_at      TIMESTAMP NOT NULL DEFAULT NOW(),
    UNIQUE(company_id, decision_number)
);
```

---

## 13. Index Strategy

```sql
-- Critical indexes for performance

-- Multi-tenant isolation
CREATE INDEX idx_work_orders_company_plant ON work_orders(company_id, plant_id);
CREATE INDEX idx_inventory_txn_company_date ON inventory_transactions(company_id, txn_date);

-- Status-based queries (very frequent)
CREATE INDEX idx_work_orders_status ON work_orders(company_id, status) WHERE status NOT IN ('completed', 'closed');
CREATE INDEX idx_alerts_open ON alerts(company_id, status) WHERE status = 'open';
CREATE INDEX idx_action_items_open ON action_items(company_id, owner_id, status) WHERE status NOT IN ('done', 'cancelled');

-- Foreign key lookups
CREATE INDEX idx_inventory_txn_lot ON inventory_transactions(lot_id);
CREATE INDEX idx_inventory_txn_work_order ON inventory_transactions(reference_id) WHERE reference_type = 'work_order';

-- Date range queries
CREATE INDEX idx_machine_logs_machine_date ON machine_runtime_logs(machine_id, started_at);
CREATE INDEX idx_downtime_machine_date ON downtime_events(machine_id, started_at);

-- AI queries
CREATE INDEX idx_ai_recommendations_domain_status ON ai_recommendations(company_id, domain, status);
CREATE INDEX idx_ai_recommendations_priority ON ai_recommendations(company_id, priority, created_at DESC);
```

---

## 14. Database Conventions Summary

| Convention | Rule |
|-----------|------|
| Primary Key | UUID, `gen_random_uuid()` |
| Tenant Isolation | `company_id` + `plant_id` in every table |
| Money | `DECIMAL(20,4)` |
| Quantity | `DECIMAL(18,4)` |
| Percentage | `DECIMAL(8,4)` (e.g., 99.9950) |
| Status fields | `VARCHAR(30)`, lower_case, snake_case |
| JSONB fields | Always `DEFAULT '{}'` or `DEFAULT '[]'` |
| Soft delete | Only master data tables, `deleted_at TIMESTAMP` |
| Ledger tables | No `updated_at`, no `deleted_at` |
| Timestamps | `TIMESTAMP NOT NULL DEFAULT NOW()` |
| Foreign keys | `ON DELETE RESTRICT` by default |
