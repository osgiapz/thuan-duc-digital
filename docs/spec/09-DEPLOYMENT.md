# PHẦN 9 — DEPLOYMENT ARCHITECTURE
## Triển khai, Infrastructure, CI/CD, Scalability

---

## 1. Deployment Philosophy

```
Principles:
  ✅ Infrastructure as Code (IaC)
  ✅ Immutable deployments (không patch-in-place)
  ✅ Blue-Green deployment (zero downtime)
  ✅ Environment parity (dev = staging ≈ production)
  ✅ Automated testing before every deploy
  ✅ Rollback < 5 minutes
  ✅ Observability-first (logs, metrics, traces)
```

---

## 2. Environment Strategy

### 2.1 Environments

| Env | Mục đích | Data | Deployment |
|-----|---------|------|-----------|
| Local | Dev daily work | Seed data | Manual (Docker Compose) |
| Development | Integration testing | Anonymized | Auto on push to `develop` |
| Staging | UAT / Pre-production | Anonymized production copy | Auto on push to `staging` |
| Production | Live system | Real | Manual approval after staging pass |

### 2.2 Branch Strategy

```
main         → Production
staging      → Staging
develop      → Development
feature/*    → Feature branches (from develop)
hotfix/*     → Hotfix (from main, merged to main + develop)
```

---

## 3. Docker Architecture

### 3.1 Services

```yaml
# docker-compose.yml (Production-like)

services:
  # ─── Application Layer ───────────────────────────────
  app:
    image: thuanduc/factorymind-app:${VERSION}
    environment:
      APP_ENV: production
      DB_HOST: postgres
      REDIS_HOST: redis
      AI_SERVICE_URL: http://ai-service:8000
    deploy:
      replicas: 3
      resources:
        limits: { cpus: "2", memory: 2G }

  horizon:
    image: thuanduc/factorymind-app:${VERSION}
    command: php artisan horizon
    deploy:
      replicas: 2

  reverb:
    image: thuanduc/factorymind-app:${VERSION}
    command: php artisan reverb:start
    ports: ["8080:8080"]
    deploy:
      replicas: 2

  scheduler:
    image: thuanduc/factorymind-app:${VERSION}
    command: php artisan schedule:run
    deploy:
      replicas: 1

  # ─── AI Layer ────────────────────────────────────────
  ai-service:
    image: thuanduc/factorymind-ai:${VERSION}
    environment:
      ANTHROPIC_API_KEY: ${ANTHROPIC_API_KEY}
      OPENAI_API_KEY: ${OPENAI_API_KEY}
      DATABASE_URL: postgresql://...
      REDIS_URL: redis://redis:6379
    deploy:
      replicas: 2
      resources:
        limits: { cpus: "4", memory: 4G }

  # ─── Data Layer ──────────────────────────────────────
  postgres:
    image: postgres:16-alpine
    volumes:
      - postgres_data:/var/lib/postgresql/data
    environment:
      POSTGRES_DB: factorymind
      POSTGRES_USER: ${DB_USER}
      POSTGRES_PASSWORD: ${DB_PASSWORD}
    deploy:
      placement:
        constraints: [node.role == manager]

  postgres-replica:
    image: postgres:16-alpine
    environment:
      POSTGRES_MASTER_HOST: postgres
    deploy:
      replicas: 2

  redis:
    image: redis:7-alpine
    command: redis-server --requirepass ${REDIS_PASSWORD} --save 60 1
    volumes:
      - redis_data:/data

  # ─── Search ──────────────────────────────────────────
  meilisearch:
    image: getmeili/meilisearch:latest
    volumes:
      - meili_data:/meili_data

  # ─── Proxy ───────────────────────────────────────────
  nginx:
    image: nginx:alpine
    ports: ["80:80", "443:443"]
    volumes:
      - ./nginx.conf:/etc/nginx/nginx.conf
      - ssl_certs:/etc/ssl/certs
```

### 3.2 Nginx Config

```nginx
server {
    listen 443 ssl http2;
    server_name api.factorymind.thuanduc.com;

    ssl_certificate /etc/ssl/certs/thuanduc.crt;
    ssl_certificate_key /etc/ssl/certs/thuanduc.key;
    ssl_protocols TLSv1.3;

    # API
    location /api/ {
        proxy_pass http://app:80;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_read_timeout 60s;
    }

    # WebSocket (Reverb)
    location /app/ {
        proxy_pass http://reverb:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_read_timeout 86400;
    }

    # Rate limiting
    limit_req_zone $binary_remote_addr zone=api:10m rate=100r/m;
    location /api/v1/auth/ {
        limit_req zone=api burst=10 nodelay;
        proxy_pass http://app:80;
    }
}
```

---

## 4. Kubernetes Architecture (Scale)

### 4.1 K8s Structure

```
Cluster
├── Namespace: factorymind-prod
│   ├── Deployments
│   │   ├── app (Laravel)          replicas: 3-10 (HPA)
│   │   ├── horizon (Queue)        replicas: 2-6 (HPA)
│   │   ├── reverb (WebSocket)     replicas: 2-4
│   │   ├── ai-service (FastAPI)   replicas: 2-8 (HPA)
│   │   └── scheduler              replicas: 1
│   ├── StatefulSets
│   │   ├── postgres-primary       replicas: 1
│   │   └── postgres-replica       replicas: 2
│   ├── Services
│   │   ├── app-service (ClusterIP)
│   │   ├── ai-service (ClusterIP)
│   │   └── reverb-service (ClusterIP)
│   ├── Ingress (Nginx Ingress Controller)
│   └── ConfigMaps & Secrets
├── Namespace: monitoring
│   ├── Prometheus
│   ├── Grafana
│   └── Loki
└── Namespace: cert-manager
    └── Let's Encrypt certificates
```

### 4.2 Horizontal Pod Autoscaler

```yaml
apiVersion: autoscaling/v2
kind: HorizontalPodAutoscaler
metadata:
  name: app-hpa
spec:
  scaleTargetRef:
    kind: Deployment
    name: app
  minReplicas: 3
  maxReplicas: 10
  metrics:
    - type: Resource
      resource:
        name: cpu
        target:
          type: Utilization
          averageUtilization: 70
    - type: Resource
      resource:
        name: memory
        target:
          type: Utilization
          averageUtilization: 80
```

---

## 5. CI/CD Pipeline

### 5.1 GitHub Actions Pipeline

```yaml
# .github/workflows/deploy.yml

name: Deploy Pipeline

on:
  push:
    branches: [develop, staging, main]

jobs:
  # ─── Stage 1: Quality Checks ─────────────────────────
  quality:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      
      - name: PHP Linting (Pint)
        run: ./vendor/bin/pint --test
        
      - name: Static Analysis (PHPStan)
        run: ./vendor/bin/phpstan analyse --level=8
        
      - name: Security Check
        run: composer audit
        
      - name: Python Linting (ruff)
        run: ruff check ai-service/

  # ─── Stage 2: Tests ──────────────────────────────────
  tests:
    needs: quality
    runs-on: ubuntu-latest
    services:
      postgres:
        image: postgres:16
        env:
          POSTGRES_DB: factorymind_test
          POSTGRES_PASSWORD: secret
      redis:
        image: redis:7
    steps:
      - name: Run Laravel Tests (Pest)
        run: php artisan test --parallel --coverage --min=80
        
      - name: Run AI Service Tests (pytest)
        run: pytest ai-service/ -v --cov=. --cov-fail-under=75

  # ─── Stage 3: Build ──────────────────────────────────
  build:
    needs: tests
    runs-on: ubuntu-latest
    steps:
      - name: Build Laravel Docker Image
        run: |
          docker build -t thuanduc/factorymind-app:${{ github.sha }} .
          
      - name: Build AI Service Image
        run: |
          docker build -t thuanduc/factorymind-ai:${{ github.sha }} ./ai-service
          
      - name: Push to Registry
        run: |
          docker push thuanduc/factorymind-app:${{ github.sha }}
          docker push thuanduc/factorymind-ai:${{ github.sha }}

  # ─── Stage 4: Deploy ─────────────────────────────────
  deploy-staging:
    needs: build
    if: github.ref == 'refs/heads/staging'
    environment: staging
    steps:
      - name: Run DB Migrations
        run: |
          kubectl exec -n factorymind-staging deploy/app -- \
            php artisan migrate --force
          
      - name: Deploy Rolling Update
        run: |
          kubectl set image deployment/app \
            app=thuanduc/factorymind-app:${{ github.sha }} \
            -n factorymind-staging
            
      - name: Health Check
        run: |
          kubectl rollout status deployment/app -n factorymind-staging
          curl -f https://staging.thuanduc.com/api/v1/health

  deploy-production:
    needs: build
    if: github.ref == 'refs/heads/main'
    environment:
      name: production
      url: https://factorymind.thuanduc.com
    steps:
      - name: Require Manual Approval
        uses: trstringer/manual-approval@v1
        with:
          approvers: cto,senior-engineer
          minimum-approvals: 2
          
      - name: Blue-Green Deploy
        run: ./scripts/blue-green-deploy.sh ${{ github.sha }}
        
      - name: Smoke Tests
        run: ./scripts/smoke-tests.sh production
        
      - name: Switch Traffic
        run: ./scripts/switch-traffic.sh green
```

### 5.2 Database Migration Strategy

```bash
#!/bin/bash
# deploy-with-migration.sh

# 1. Run migrations (backward compatible only)
kubectl exec deploy/app -- php artisan migrate --force

# Rule: Each migration must be backward compatible
# (old code must still work while new migration is running)

# Backward compatible examples:
# ✅ Add nullable column
# ✅ Add new table
# ✅ Add index
# ✅ Rename column (in 2 steps: add new + copy data + remove old)

# NOT backward compatible (requires maintenance window):
# ❌ Remove column currently used by code
# ❌ Change column type
# ❌ Add NOT NULL constraint to existing column without default
```

---

## 6. Multi-tenant SaaS Architecture

### 6.1 Tenant Isolation Model

```
Model: Shared Database, Shared Schema
Isolation: Row-level via company_id + PostgreSQL RLS

Why this model:
  ✅ Cost efficient (one DB for all tenants)
  ✅ Easy to maintain migrations
  ✅ Good performance for our scale (< 1000 tenants target)
  
When to upgrade to Schema-level:
  → When any tenant has > 10M rows in critical tables
  → When tenant demands strict data isolation
  → When compliance requires physical separation (e.g., government)
```

### 6.2 Tenant Provisioning

```
New Company Registration:
  1. Create company record
  2. Create default roles and permissions
  3. Create default plant (if provided)
  4. Create admin user
  5. Send welcome email with setup guide
  6. Create default KPI definitions
  7. Create default alert rules
  8. Generate API credentials

Automated via:
  php artisan company:provision {company_code} {admin_email}
```

### 6.3 Tenant Data Backup

```
Backup strategy per tenant:
  - Full backup: Daily at 02:00
  - Incremental: Every 6 hours
  - Transaction logs: Continuous (WAL archiving)
  
Tenant-specific export:
  php artisan tenant:export {company_id} --format=sql
  (For tenant who wants to leave or needs data copy)
```

---

## 7. Observability Stack

### 7.1 Three Pillars

```
Logs      → Loki + Grafana
Metrics   → Prometheus + Grafana
Traces    → OpenTelemetry + Jaeger (or Tempo)
```

### 7.2 Key Metrics to Monitor

#### Application Metrics
```
http_request_duration_seconds (p50, p95, p99)
http_requests_total (by status code, endpoint)
queue_job_processing_time
queue_failed_jobs_total
active_users_current
```

#### Business Metrics
```
work_orders_created_total (by plant)
work_orders_completed_total
inventory_transactions_total
ai_recommendations_total (by agent, domain)
ai_recommendation_acceptance_rate
alert_mean_time_to_acknowledge
sla_breach_rate
```

#### Infrastructure Metrics
```
postgres_connections_active
postgres_query_duration_p95
redis_memory_usage
container_cpu_usage
container_memory_usage
```

### 7.3 Alerting Rules (Prometheus)

```yaml
groups:
  - name: application
    rules:
      - alert: HighErrorRate
        expr: rate(http_requests_total{status=~"5.."}[5m]) > 0.05
        severity: critical
        
      - alert: HighLatency
        expr: http_request_duration_seconds_p95 > 2
        severity: warning
        
      - alert: QueueBacklog
        expr: queue_jobs_pending > 1000
        severity: warning
        
      - alert: AiServiceDown
        expr: up{job="ai-service"} == 0
        severity: critical
```

### 7.4 Grafana Dashboards

```
1. Executive Dashboard
   - Uptime, error rate, latency
   - Active users, API calls/minute
   - Business KPIs (work orders, transactions)

2. Infrastructure Dashboard
   - CPU, Memory, Disk for all pods
   - PostgreSQL connections, query time
   - Redis memory, hit rate

3. AI Dashboard
   - Agent runs per hour
   - API cost per day (by agent)
   - Recommendation acceptance rate
   - Token usage by model

4. Business Operations Dashboard
   - Work orders by status
   - Alerts by severity
   - SLA compliance rate
```

---

## 8. Backup & Disaster Recovery

### 8.1 Backup Schedule

```
PostgreSQL:
  - WAL archiving: Continuous → S3 (point-in-time recovery)
  - Base backup: Daily 02:00 → S3
  - Retention: 30 days rolling

Redis:
  - RDB snapshot: Every 6 hours → S3
  - AOF: Enabled (every second)
  - Retention: 7 days

Application files:
  - S3 replication: Cross-region
  - Retention: Based on file type
```

### 8.2 Recovery Targets

| Target | Value |
|--------|-------|
| RPO (Recovery Point Objective) | 1 hour |
| RTO (Recovery Time Objective) | 4 hours |
| MTTR for critical issues | < 2 hours |

### 8.3 DR Runbook (High-level)

```
1. Declare incident (notify stakeholders)
2. Assess: primary region down vs service failure
3. If region failure → spin up DR environment in secondary region
4. Restore PostgreSQL from latest backup + WAL to desired time
5. Update DNS to point to DR environment
6. Verify critical business functions
7. Notify users of restored service
8. Post-mortem within 48 hours
```

---

## 9. Performance Tuning

### 9.1 PostgreSQL Optimization

```sql
-- Key configurations (postgresql.conf)
shared_buffers = 4GB              -- 25% of RAM
effective_cache_size = 12GB       -- 75% of RAM
work_mem = 256MB                  -- Per query
max_connections = 200
default_statistics_target = 200   -- Better query plans
random_page_cost = 1.1            -- SSD storage

-- Connection pooling (PgBouncer)
pool_mode = transaction
max_client_conn = 500
default_pool_size = 20
```

### 9.2 Redis Cache Strategy

```php
// Cache keys convention: {tenant}:{domain}:{entity}:{id}:{data}
$cacheKey = "ct:{$companyId}:dashboard:kpis:today";

// Cache durations
const CACHE_KPI_REALTIME     = 60;      // 1 min
const CACHE_KPI_DASHBOARD    = 300;     // 5 min
const CACHE_MASTER_DATA      = 3600;    // 1 hour
const CACHE_REPORT           = 1800;    // 30 min
const CACHE_AI_RECOMMENDATION = 900;   // 15 min

// Cache invalidation on data change
class WorkOrderObserver
{
    public function updated(WorkOrder $wo): void
    {
        Cache::tags(["company:{$wo->company_id}", "plant:{$wo->plant_id}"])
             ->flush();
    }
}
```

### 9.3 Queue Priorities

```php
// config/horizon.php
'environments' => [
    'production' => [
        'supervisor-critical' => [
            'queue' => ['critical', 'high'],
            'processes' => 5,
            'tries' => 3,
        ],
        'supervisor-default' => [
            'queue' => ['default', 'notifications'],
            'processes' => 10,
            'tries' => 3,
        ],
        'supervisor-ai' => [
            'queue' => ['ai-processing'],
            'processes' => 4,
            'tries' => 2,
        ],
        'supervisor-reports' => [
            'queue' => ['reports', 'exports'],
            'processes' => 2,
            'tries' => 2,
        ],
    ],
],
```
