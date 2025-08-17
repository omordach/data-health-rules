# Data Health POC (Single-Tenant) — README

Minimal Composer/Laravel package that runs **business-data health checks** inside your ERP (e.g., “dues > 2× typical”, “duplicate monthly charges in the same month”), stores durable results, and exposes an optional Prometheus-style `/metrics` endpoint.

> **Status:** Proof-of-Concept (single tenant, no UI).  
> **Laravel:** 10–12, **PHP:** 8.2+ • **DB:** MySQL/MariaDB

---

## Table of Contents

1. [What it does](#what-it-does)  
2. [Prerequisites](#prerequisites)  
3. [Schema assumptions](#schema-assumptions)  
4. [Install (from repo)](#install-from-repo)  
   - [Local path repo (fastest for PoC)](#option-a-local-path-repo)  
   - [Git (GitHub/GitLab) repo](#option-b-git-vcs-repo)  
5. [Migrate database](#migrate-database)  
6. [Quick start](#quick-start)  
7. [Metrics endpoint (optional)](#metrics-endpoint-optional)  
8. [Tuning thresholds](#tuning-thresholds)  
9. [Scheduling](#scheduling)  
10. [Adapting to your schema](#adapting-to-your-schema)  
11. [Adding your own rules](#adding-your-own-rules)  
12. [Indexes & performance](#indexes--performance)  
13. [Troubleshooting](#troubleshooting)  
14. [Uninstall / clean up](#uninstall--clean-up)  
15. [Repo layout](#repo-layout)  
16. [License](#license)

---

## What it does

- Runs **two built-in checks** (SQL-first):
  - `DUE_OVER_MAX` — dues charge > `multiplier × typical_due` (default: 2 × 70).
  - `DUP_CHARGES` — same member has ≥2 dues charges in the same month.
- Stores deduped, durable results in `dhp_results` (`open`/`resolved`).
- Includes a seeder to insert default rules into `dhp_rules`.
- (Optional) Exposes a Prometheus-style endpoint: `/metrics/data-health-poc`.

Use it on a fresh Laravel app or drop it into your ERP, then customize.

---

## Prerequisites

- PHP **8.2+**
- Laravel **10–12**
- MySQL/MariaDB
- Composer

---

## Schema assumptions

The PoC expects the following **existing** tables in your app database:

- `members(id, typical_due NULLABLE)`
- `charges(id, member_id, period_ym VARCHAR(7) like 'YYYY-MM', type VARCHAR, amount DECIMAL)`

> If your schema differs, see [Adapting to your schema](#adapting-to-your-schema). You can run as-is on a scratch DB by creating minimal versions of these tables.

---

## Install (from repo)

### Option A) Local path repo

1) **Clone** this package somewhere adjacent to your Laravel app:

```
/path/to/your-app
/path/to/data-health-poc   ← this repo
```

2) **Tell Composer** about the path repository (run from your Laravel app root):

```bash
composer config repositories.data-health-poc path ../data-health-poc
composer require unionimpact/data-health-poc:dev-main
```

> Adjust `../data-health-poc` if your folder structure is different.

---

### Option B) Git (VCS) repo

If you pushed this package to GitHub/GitLab:

```bash
composer config repositories.data-health-poc vcs https://github.com/your-org/data-health-poc.git
composer require unionimpact/data-health-poc:^0.1
```

(Use your actual URL and tag.)

---

## Migrate database

Run package migrations (creates `dhp_rules`, `dhp_results`):

```bash
php artisan migrate
```

Then seed the default rule rows:

```bash
php artisan db:seed --class="UnionImpact\\DataHealthPoc\\Database\\Seeders\\DataHealthPocSeeder"
```

That’s it. No config files in the PoC.

---

## Quick start

1) (Optional) Insert quick test data:

```sql
-- Minimal example data (adjust to your schema)
INSERT INTO members (id, typical_due) VALUES (1,70),(2,70),(3,NULL);

INSERT INTO charges (member_id, period_ym, type, amount) VALUES
(1,'2025-08','dues',150.00), -- > 2×70 = 140 → violation
(1,'2025-08','dues',30.00),  -- duplicate month for member 1 → violation
(2,'2025-08','dues',200.00), -- over max
(3,'2025-08','dues',300.00); -- typical_due null → defaults to 70
```

2) **Run the checks:**

```bash
php artisan data-health-poc:run
```

- Uses rule rows in `dhp_rules` (seeded via `DataHealthPocSeeder`).
- Results written to `dhp_results` with `status = open`.
- Re-running will mark stale rows as `resolved` if violations disappear.

3) **Inspect results:**

```sql
SELECT * FROM dhp_results ORDER BY detected_at DESC;
SELECT * FROM dhp_rules;
```

4) (Optional) **Run a single rule:**

```bash
php artisan data-health-poc:run --rule=DUE_OVER_MAX
```

---

## Metrics endpoint (optional)

A minimal Prometheus endpoint is available at:

```
GET /metrics/data-health-poc
```

**Example output:**
```
# HELP data_health_poc_open Open violations by rule
# TYPE data_health_poc_open gauge
data_health_poc_open{rule="DUE_OVER_MAX"} 3
data_health_poc_open{rule="DUP_CHARGES"} 1
```

> ⚠️ For PoC simplicity this route is public. In production, protect it (e.g., behind a proxy, VPN, or by wrapping the route with auth middleware in the service provider).

---

## Tuning thresholds

Thresholds live in `dhp_rules.options` (JSON). After the first run, update as needed:

- `DUE_OVER_MAX` → `{"default_due": 70, "multiplier": 2.0}`
- `DUP_CHARGES` → `{}` (none)

Then re-run:

```bash
php artisan data-health-poc:run
```

---

## Scheduling

Add to your Laravel scheduler (`app/Console/Kernel.php`) to run periodically:

```php
protected function schedule(Schedule $schedule): void
{
    $schedule->command('data-health-poc:run')->everyFifteenMinutes();
}
```

> For the PoC there’s no alerting; you can still scrape `/metrics/data-health-poc` from Prometheus for visibility.

---

## Adapting to your schema

If your table/column names differ:

- Edit the SQL inside the rule classes:

  - `src/Rules/DuesOverMaxRule.php`
  - `src/Rules/DuplicateMonthlyChargesRule.php`

**What to change:**

- Table names: `members`, `charges`
- Columns: `typical_due`, `member_id`, `period_ym`, `type`, `amount`
- `period_ym` format: expected `'YYYY-MM'` (adjust the SQL if you use dates)

> Keep the returned structure the same (`entity_type`, `entity_id`, `period_key`, `payload`, `hash`).

---

## Adding your own rules

Rules are plain PHP classes that implement the simple `Rule` contract:

```php
interface Rule
{
    public static function code(): string; // e.g., 'MISSING_DUES'
    public static function name(): string;

    /** Return Collection of:
     * ['entity_type','entity_id','period_key','payload'=>[], 'hash']
     */
    public function evaluate(array $options = []): Collection;
}
```

**Create a rule file**, e.g., `app/Health/Rules/MissingDuesRule.php`:

```php
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use UnionImpact\DataHealthPoc\Contracts\Rule;

class MissingDuesRule implements Rule
{
    public static function code(): string { return 'MISSING_DUES'; }
    public static function name(): string { return 'Active member missing dues this month'; }

    public function evaluate(array $opt = []): Collection
    {
        $month = $opt['month'] ?? now()->format('Y-m');

        $rows = DB::select("
            SELECT m.id AS member_id
            FROM members m
            LEFT JOIN charges c
              ON c.member_id = m.id
             AND c.period_ym = ?
             AND c.type = 'dues'
            WHERE c.member_id IS NULL
              AND m.status = 'active'
        ", [$month]);

        return collect($rows)->map(function ($r) use ($month) {
            $hash = sha1("missing:{$r->member_id}:{$month}");
            return [
                'entity_type' => 'member',
                'entity_id'   => (string)$r->member_id,
                'period_key'  => $month,
                'payload'     => ['expected' => true],
                'hash'        => $hash,
            ];
        });
    }
}
```

**Register the rule row** in `dhp_rules`:

```sql
INSERT INTO dhp_rules (code, name, options, enabled)
VALUES ('MISSING_DUES', 'Active member missing dues this month', JSON_OBJECT('month','2025-08'), 1);
```

**Make it discoverable (PoC tip):**  
For the PoC, built-in rules are hardcoded. To run your custom rule, temporarily **swap** one of the built-in rule classes (e.g., replace the `DUP_CHARGES` class mapping in `RunDataHealthCommand`) or fork the package and add your rule to the `$builtIns` map.

---

## Indexes & performance

Add these indexes in your app DB for faster scans:

```sql
CREATE INDEX idx_charges_member_period_type ON charges(member_id, period_ym, type);
CREATE INDEX idx_charges_period_type       ON charges(period_ym, type);
-- Optional if used: typical_due frequently accessed
CREATE INDEX idx_members_typical_due       ON members(typical_due);
```

If tables are huge, consider restricting queries to a **recent window** (e.g., last 12 months) directly in the SQL.

---

## Troubleshooting

- **`Base table or view not found`** – Ensure your app has `members` and `charges` tables (or adapt the SQL).
- **`Unknown column`** – Update the rule SQL to your column names.
- **No results but expected** – Check the `period_ym` format and `type = 'dues'` filter.
- **Duplicate `hash` constraint** – The PoC uses `hash` to dedupe. If you change payloads that affect the hash logic, update existing rows or adjust the hash composition accordingly.

---

## Uninstall / clean up

From your app:

```bash
composer remove unionimpact/data-health-poc
```

Drop the tables if desired:

```bash
php artisan tinker
>>> Schema::dropIfExists('dhp_results');
>>> Schema::dropIfExists('dhp_rules');
```

(Or write a down migration in your app.)

---

## Repo layout

```
data-health-poc/
├─ composer.json
├─ src/
│  ├─ DataHealthPocServiceProvider.php
│  ├─ Console/RunDataHealthCommand.php
│  ├─ Contracts/Rule.php
│  ├─ Http/MetricsController.php              # optional /metrics endpoint
│  ├─ Models/{Rule.php, Result.php}
│  └─ Rules/{DuesOverMaxRule.php, DuplicateMonthlyChargesRule.php}
└─ database/migrations/2025_01_01_000000_create_dhp_tables.php
```

---

## License

MIT
