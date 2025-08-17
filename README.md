# Data Health POC (Single-Tenant)

Lightweight Laravel Composer package that scans ERP data for anomalies. Built-in SQL rules detect overcharged dues and duplicate monthly charges, save results for review, and optionally expose Prometheus metrics.

## Requirements

- PHP 8.2+
- Laravel 10–12
- MySQL or MariaDB
- Composer

## Installation

1. **Add the package** to your Laravel app (path repo or VCS URL):

```bash
composer config repositories.data-health-poc path ../data-health-poc
composer require unionimpact/data-health-poc:dev-main
```

2. **Run migrations** to create the `dhp_rules` and `dhp_results` tables:

```bash
php artisan migrate
```

3. *(Optional)* **Publish config** to customize rule registration:

```bash
php artisan vendor:publish --tag=data-health-poc-config
```

## Usage

Run all enabled health checks:

```bash
php artisan data-health-poc:run
```

Expose metrics (if desired) by routing `/metrics/data-health-poc`.

## Tailoring the Rules

- **Different schema?** Edit SQL in `src/Rules/*.php` to match your tables.
- **Adjust thresholds** via the JSON `options` column in `dhp_rules`.
- **Add new rules** by implementing `UnionImpact\\DataHealthPoc\\Contracts\\Rule` and registering it in `config/data-health-poc.php`.
- **Performance**: narrow queries to recent periods or add indexes in your app.

## Testing

Run package tests with Pest:

```bash
composer test
```

## License

Released under the [MIT License](LICENSE).

