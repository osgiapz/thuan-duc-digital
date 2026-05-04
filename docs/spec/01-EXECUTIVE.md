# PHẦN 1 — EXECUTIVE SPECIFICATION
## Đặc tả Tổng quan Cấp Điều hành

---

## 1. Document Control

| Hạng mục | Nội dung |
|----------|----------|
| Tên tài liệu | Master Specification — Thuận Đức AI Enterprise Group Control Tower OS |
| Tên thương mại | FactoryMind AI OS |
| Tên kiến trúc | AI-native Enterprise Operating System (AI-EOS) |
| Phiên bản | v1.0 |
| Trạng thái | Bản chuẩn hóa để triển khai |
| Đơn vị áp dụng | Công ty Cổ phần Thuận Đức / Thuận Đức Group |
| Mức đặc tả | Enterprise Specification Level 1–3 |
| Đối tượng | HĐQT, Ban TGĐ, BA, PM, Solution Architect, Tech Lead, Dev, QA, Data Engineer, AI Engineer |
| Phạm vi | Toàn bộ hệ thống điều hành doanh nghiệp/tập đoàn sản xuất tích hợp AI |
| Mục tiêu cuối | Vận hành nội bộ Thuận Đức + đóng gói SaaS B2B công nghiệp |

---

## 2. Executive Summary

Hệ thống **Thuận Đức AI Enterprise Group Control Tower OS** không phải ERP, MES, CRM, HRM hay BI đơn lẻ.

Đây là **AI-native Enterprise Operating System** — hệ điều hành doanh nghiệp tích hợp AI, đóng vai trò như **bộ não số hợp nhất** toàn bộ chuỗi giá trị của Thuận Đức.

### Chuyển đổi mô hình quản trị:

```
TRƯỚC                              SAU
─────────────────────────────────────────────────────
Báo cáo quá khứ              →    Tín hiệu thời gian thực
Dữ liệu phân tán             →    Single Source of Truth
Quyết định cảm tính          →    AI-augmented decision
Hành động chậm               →    Workflow tự động hóa
Kiểm soát bằng họp hành      →    Control Tower digital
```

---

## 3. Core Business Objectives

| # | Mục tiêu | Mô tả chi tiết |
|---|----------|----------------|
| 1 | **Tối đa hóa lợi nhuận** | Kiểm soát chi phí, giá thành, hiệu suất sản xuất, dòng tiền, tồn kho, biên lợi nhuận theo thời gian thực |
| 2 | **Giảm chi phí vận hành** | Tự động hóa quy trình, giảm nhập liệu thủ công, giảm lỗi vận hành, giảm hàng lỗi, giảm tồn kho chết |
| 3 | **Kiểm soát rủi ro sớm** | Phát hiện bất thường trong sản xuất, kho, chất lượng, tài chính, dòng tiền, đơn hàng, nhân sự, ESG |
| 4 | **Tăng tốc ra quyết định** | Nhìn dashboard → thấy vấn đề → drill-down ≤3 click → nguyên nhân → hành động |
| 5 | **Chuẩn hóa quản trị** | Hỗ trợ đa công ty, đa nhà máy, đa phân xưởng, đa trung tâm chi phí/lợi nhuận |
| 6 | **AI tham gia vận hành** | AI không chỉ trả lời câu hỏi — AI phân tích, cảnh báo, đề xuất, tạo ticket, chuẩn bị báo cáo |
| 7 | **Thương mại hóa SaaS** | Đóng gói thành sản phẩm B2B SaaS cho doanh nghiệp sản xuất công nghiệp |

---

## 4. System Definition

### 4.1 Định nghĩa ngắn gọn

> **FactoryMind AI OS** là nền tảng điều hành doanh nghiệp sản xuất tích hợp AI, hợp nhất dữ liệu, quy trình, con người, máy móc, tài chính, thị trường, kiểm toán, ESG và AI Agents vào một hệ thống Control Tower duy nhất.

### 4.2 Năng lực cốt lõi hệ thống

```
 1. Thu thập dữ liệu từ ERP, MES, CRM, WMS, HRM, kế toán, IoT, máy chấm công
 2. Chuẩn hóa → Single Source of Truth
 3. Quản trị mô hình tổ chức đa tầng Thuận Đức
 4. Theo dõi: sản xuất, kho, chất lượng, bán hàng, tài chính, nhân sự, ESG
 5. AI phân tích dữ liệu + phát hiện tín hiệu rủi ro realtime
 6. AI gợi ý hành động xử lý
 7. Tạo workflow, ticket, CAPA, quyết định, SLA tự động
 8. Lưu toàn bộ audit log — không thể xóa
 9. Đo lường hiệu quả sau hành động
10. Học lại từ dữ liệu thực → cải thiện AI
```

### 4.3 So sánh với hệ thống truyền thống

| Loại | Vai trò truyền thống | Giới hạn | FactoryMind AI OS |
|------|---------------------|----------|-------------------|
| ERP | Quản lý giao dịch | Phản ánh dữ liệu sau khi xảy ra | Kết nối ERP vào Control Tower |
| MES | Quản lý sản xuất | Chỉ tập trung nhà máy | Kết nối với tài chính, bán hàng, kho |
| CRM | Quản lý khách hàng | Không thấy năng lực sản xuất | Kết nối với ATP/CTP, kế hoạch, tồn kho |
| BI | Báo cáo | Thụ động, ít hành động | Signal → Decision → Action |
| Workflow | Giao việc | Không có AI phân tích | AI đề xuất ticket, owner, SLA, CAPA |
| AI Chatbot | Hỏi đáp | Tách rời nghiệp vụ | AI Agents làm việc trong hệ thống |

---

## 5. Enterprise Scope

### 5.1 Phạm vi chuỗi giá trị

```
Nguyên liệu (Hạt nhựa)
    ↓
Kéo sợi
    ↓
Dệt
    ↓
Tráng / Ghép màng
    ↓
In
    ↓
Cắt
    ↓
May
    ↓
Thành phẩm → Kho
    ↓
Bán hàng (Nội địa / Xuất khẩu)
    ↓
Giao hàng
    ↓
Công nợ → Dòng tiền
    ↓
Báo cáo quản trị
    ↓
Kiểm toán / ESG / Tuân thủ quốc tế
```

### 5.2 Phạm vi tổ chức

```
Group / Tập đoàn
    └── Legal Entity / Công ty thành viên
             └── Business Unit / Khối nghiệp vụ
                      └── Plant / Nhà máy
                               └── Department / Phòng ban
                                        └── Workshop / Phân xưởng
                                                 └── Line / Dây chuyền / Tổ
                                                          └── Machine / Máy
                                                                   └── Employee / Nhân sự
```

---

## 6. Management Levels

### 6.1 Group Level — Cấp tập đoàn cần nhìn được

- Doanh thu hợp nhất, lợi nhuận hợp nhất, dòng tiền hợp nhất
- Tình trạng từng công ty / từng nhà máy
- Rủi ro chiến lược, tồn kho toàn hệ thống
- Công nợ phải thu / phải trả
- Năng lực sản xuất toàn chuỗi
- ESG và tuân thủ xuất khẩu

### 6.2 Company Level — Cấp công ty thành viên

- Doanh thu, chi phí, P&L, công nợ độc lập
- Kho, nhân sự, KPI, quy trình phê duyệt riêng

### 6.3 Plant Level — Cấp nhà máy

- Kế hoạch sản xuất, lệnh sản xuất, năng lực máy
- OEE, downtime, phế phẩm
- Tồn kho RM / WIP / FG, chất lượng
- Nhân sự ca kíp, chi phí sản xuất

### 6.4 Workshop / Line Level — Cấp phân xưởng

- Kế hoạch công đoạn, sản lượng theo ca
- BTP đầu vào / đầu ra, QC Gate, lỗi công đoạn
- Hao hụt, năng suất tổ, năng suất nhân sự

### 6.5 Machine / Individual Level — Truy xuất đến

- Máy nào gây downtime / tạo nhiều lỗi
- Nhân sự nào phụ trách / ca nào phát sinh lỗi
- Lệnh sản xuất nào bị ảnh hưởng
- Đơn hàng nào có nguy cơ trễ
- Chi phí phát sinh là bao nhiêu

---

## 7. Multi-Persona Architecture

### 7.1 Vấn đề

Một nhân sự trong Thuận Đức có thể giữ nhiều vai trò đồng thời:

```
Nguyễn Văn A:
  - Thành viên HĐQT Group
  - Giám đốc Nhà máy NM4
  - Trưởng ban dự án AI Control Tower
  - Người phê duyệt mua hàng cấp 2
```

### 7.2 Work Context — Bắt buộc phải có

| Thành phần | Ý nghĩa |
|------------|---------|
| `current_role` | Đang làm việc với vai trò nào |
| `current_company_id` | Đang trong công ty nào |
| `current_plant_id` | Đang trong nhà máy nào |
| `current_business_unit` | Đang theo khối nghiệp vụ nào |
| `permission_scope` | Phạm vi quyền hạn |
| `data_scope` | Phạm vi dữ liệu được xem/sửa |
| `kpi_scope` | KPI áp dụng theo vai trò nào |
| `cost_scope` | Chi phí hạch toán vào trung tâm nào |

### 7.3 Quy tắc khi đổi context

Khi user đổi context, hệ thống **bắt buộc** phải:
- Menu đổi theo role
- Dashboard đổi theo role
- Dữ liệu lọc theo scope
- KPI tính theo đúng vai trò
- Chi phí hạch toán đúng context
- Audit log ghi nhận user thao tác trong context nào

---

## 8. Multi-Dimensional Accounting

Hệ thống phải hạch toán theo nhiều chiều đồng thời:

| Chiều | Ví dụ |
|-------|-------|
| Company | Công ty mẹ, công ty thành viên |
| Plant | NM1A, NM4 |
| Workshop | Dệt, Tráng, In, May |
| Line | Line 01, Line 02 |
| Cost Center | Trung tâm chi phí |
| Profit Center | Trung tâm lợi nhuận |
| Product | Dòng sản phẩm |
| Customer | Khách hàng |
| Order | Đơn hàng |
| Work Order | Lệnh sản xuất |
| Batch / Lot | Lô sản xuất |
| Employee | Nhân sự |
| Machine | Máy móc |

> **Mục tiêu:** Mọi chi phí, doanh thu, lỗi, phế phẩm, năng suất, công nợ, tồn kho đều có thể truy nguyên đến đúng đối tượng chịu trách nhiệm.

---

## 9. Strategic Positioning

### 9.1 Định vị trong hệ sinh thái phần mềm doanh nghiệp

```
               ┌─────────────────────────────────────┐
               │     FactoryMind AI OS               │
               │     (AI-native Enterprise OS)       │
               │                                     │
               │  ┌────────┐  ┌────────┐  ┌───────┐ │
               │  │Control │  │   AI   │  │Workflow│ │
               │  │ Tower  │  │Agents  │  │Engine │ │
               │  └────────┘  └────────┘  └───────┘ │
               └─────────────┬───────────────────────┘
                             │ Kết nối & Tổng hợp
        ┌────────────────────┼────────────────────┐
        │                    │                    │
   ┌────┴────┐          ┌────┴────┐          ┌────┴────┐
   │   ERP   │          │   MES   │          │   CRM   │
   └─────────┘          └─────────┘          └─────────┘
```

### 9.2 Competitive Moat

1. **AI-first**: AI tham gia vận hành, không phải BI thêm vào sau
2. **Control Tower Loop**: Signal → Decision → Action trong một màn hình
3. **Industry-specific**: Tối ưu cho sản xuất công nghiệp (dệt, nhựa, may)
4. **Multi-tenant SaaS ready**: Có thể bán ngay cho doanh nghiệp khác
5. **Append-only ledger**: Audit trail không thể chỉnh sửa — chuẩn enterprise

---

## 10. System Completeness Assessment

| Layer | Status | Ghi chú |
|-------|--------|---------|
| Business Domains | ✅ Full | 8 khối nghiệp vụ |
| Architecture | ✅ Full | 6 tầng |
| Database | ✅ Full | 120+ bảng, 10 phases |
| AI System | ✅ Full | Multi-agent workforce |
| Control Tower UI | ✅ Full | Filament v5 |
| API Spec | ✅ Full | REST + Events |
| Security | ✅ Full | RBAC + Row-level |
| Deployment | ✅ Full | Docker → K8s |
| SaaS Model | ✅ Full | 4 packages |
| Go-live Roadmap | ✅ Full | 4 phases |
