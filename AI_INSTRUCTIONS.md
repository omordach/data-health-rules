# AI_INSTRUCTIONS.md

## Purpose
This repository is a **Proof of Concept (PoC) Laravel Composer package** for **data health checks** in an ERP system.  
It is designed for **single-tenant** use (for now) and helps detect **data anomalies** such as:
- Dues charges greater than 2× a member’s typical dues.
- Duplicate dues charges in the same month.

## Tech Stack
- PHP 8.2+
- Laravel 10–12
- MySQL/MariaDB
- Composer package autoload (PSR-4)

## ERP Schema (simplified for PoC)
The package assumes these existing ERP tables:

```sql
members (
    id INT PRIMARY KEY,
    typical_due DECIMAL(10,2) NULL
);

charges (
    id INT PRIMARY KEY,
    member_id INT,
    period_ym VARCHAR(7),   -- Format: 'YYYY-MM'
    type VARCHAR(50),       -- Example: 'dues'
    amount DECIMAL(10,2)
);
```

> ⚠️ If schema differs, adjust the SQL queries in `src/Rules/*.php`.

## Package Features
- **Tables created by package**:  
  - `dhp_rules` – configuration of rules (code, name, options, enabled)  
  - `dhp_results` – findings (open/resolved, deduped by hash)
- **Commands**:  
  - `php artisan data-health-poc:run` → runs checks and stores results.
- **Rules**:  
  - `DUE_OVER_MAX` (amount > multiplier × typical_due)  
  - `DUP_CHARGES` (≥2 dues in same month)  
- **Metrics endpoint**:  
  - `/metrics/data-health-poc` (Prometheus gauge counts by rule).

## How AI Should Work With This Repo
- ✅ Follow Laravel best practices.  
- ✅ Keep code **framework-agnostic** where possible, but assume **Laravel Eloquent + DB Facade**.  
- ✅ Keep **rules pluggable** – new rules should implement the `Contracts\Rule` interface.  
- ✅ Results should always have:  
  - `entity_type`, `entity_id`, `period_key`, `payload`, `hash`  
- ✅ Ensure **idempotency**: running the same check twice should not duplicate results.  
- ✅ Default thresholds live in `dhp_rules.options` (JSON).  

## Future Extensions (for Codex/AI agents)
1. **Add alerting** (Mail, Slack, Webhook) with cooldowns.  
2. **Add multi-tenancy** support (Spatie/Stancl resolvers).  
3. **Add suppression/acknowledge workflow**.  
4. **Add tests** with Orchestra Testbench + Pest.  
5. **Add web UI** to browse results.  

## Example Prompt for AI
```
Update the DuesOverMaxRule so that:
1. It ignores members with status='inactive'.
2. It uses period_ym >= '2025-01' only.
3. Payload should also include member_id and rule_code.
```
