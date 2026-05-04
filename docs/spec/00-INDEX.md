# THUẬN ĐỨC AI ENTERPRISE GROUP CONTROL TOWER OS
## FactoryMind AI OS — Master Specification Index

**Version:** v1.0  
**Status:** Ready for Architecture & Development  
**Organization:** Thuận Đức Group  
**Date:** 2026-05-04

---

## Document Structure

| File | Nội dung |
|------|----------|
| [01-EXECUTIVE.md](./01-EXECUTIVE.md) | Executive Summary, Mục tiêu chiến lược, Phạm vi hệ thống |
| [02-ARCHITECTURE.md](./02-ARCHITECTURE.md) | Kiến trúc 6 tầng, Technology Stack, Data Flow |
| [03-BUSINESS-DOMAINS.md](./03-BUSINESS-DOMAINS.md) | 8 khối nghiệp vụ lõi: OPS, PEOPLE, MONEY, MARKET, GOV, AUDIT, ESG, AI |
| [04-DATABASE.md](./04-DATABASE.md) | Database Architecture, 120+ bảng, 10 Migration Phases |
| [05-AI-SYSTEM.md](./05-AI-SYSTEM.md) | AI Agent Workforce, Prompt System, Memory, Tools, Guardrails |
| [06-CONTROL-TOWER.md](./06-CONTROL-TOWER.md) | Control Tower UI/UX, Workflow Engine, SLA, War Room |
| [07-API-SPEC.md](./07-API-SPEC.md) | REST API, Event API, Auth, Naming Convention |
| [08-SECURITY.md](./08-SECURITY.md) | RBAC, Row-level Security, Audit Trail, AI Guardrails |
| [09-DEPLOYMENT.md](./09-DEPLOYMENT.md) | Docker, Kubernetes, CI/CD, Multi-tenant SaaS |
| [10-ROADMAP.md](./10-ROADMAP.md) | MVP Roadmap, Go-live Plan, SaaS Packaging |

---

## Quick Reference

### Technology Stack
| Layer | Technology |
|-------|-----------|
| Backend | PHP 8.4 + Laravel 13 |
| Admin UI | Filament v5 |
| Frontend | Astro + TypeScript |
| Mobile | Flutter |
| Database | PostgreSQL 16 |
| Cache / Queue | Redis 7 |
| AI Runtime | Python 3.12 + FastAPI |
| Deploy | Docker + Kubernetes |

### 8 Business Domains
```
OPS     → Operations (MES, Planning, WIP, QC, Inventory)
PEOPLE  → HR, Attendance, KPI Engine, Payroll 3P
MONEY   → GL, AP, AR, Dynamic Costing, Cashflow
MARKET  → Omni CRM, Sales, B2B/B2C/Export, ATP/CTP
GOV     → Strategy, OKR, Risk, Decision Log
AUDIT   → Audit Lifecycle, CAPA, Compliance
ESG     → Carbon, Energy, Water, Waste, CBAM, BSCI
AI      → Multi-Agent Workforce, Orchestration
```

### Control Tower Core Loop
```
Signal → Drill-down → Decision → Action → Result → Feedback
```
