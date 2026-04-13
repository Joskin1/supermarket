# Supermarket

Laravel 13 foundation for a supermarket inventory and sales system, with Filament as the primary admin interface.

## Stack

- Laravel 13
- Filament 5
- Livewire 4 + Flux
- Laravel Fortify for the existing frontend auth flow
- Spatie Laravel Permission for roles and authorization
- DDEV-managed Docker services for local development

## Local Development

This project uses DDEV as its Docker layer. The repository already includes the required container configuration in [.ddev/config.yaml](/home/oluwadamilare/Code/supermarket/.ddev/config.yaml), so Phase 1 intentionally keeps DDEV instead of introducing a second `docker-compose.yml` workflow to maintain.

### Prerequisites

- Docker Desktop
- DDEV
- Node.js / npm

### Setup

```bash
ddev start
ddev composer install
ddev npm install
ddev exec php artisan migrate --seed
ddev npm run build
```

### Useful Commands

```bash
ddev exec php artisan migrate
ddev exec php artisan db:seed
ddev exec php artisan db:seed --class=InventoryDevelopmentSeeder
ddev exec php artisan users:bootstrap-sudo owner@example.com --name="Store Owner" --password="replace-this-password"
ddev exec php artisan test
ddev npm run dev
```

## Access

- App URL: `http://supermarket.test`
- Filament admin: `http://supermarket.test/admin`
- Mailpit: `http://supermarket.test:8025`

## Local Demo Credentials

When `APP_ENV=local`, `php artisan migrate --seed` now provisions the full demo dataset automatically so you can exercise the whole system in development.

- `sudo`: `akinjoseph221@gmail.com` / `password`
- `admin`: `store-manager@supermarket.test` / `password`
- `admin`: `inventory-admin@supermarket.test` / `password`
- `admin`: `sales-admin@supermarket.test` / `password`

Login happens through Filament at `http://supermarket.test/admin/login`.

Seeded privileged users are email-verified for development. Two-factor authentication is currently disabled, so verified users can continue straight into the admin panel after login.

## Roles

- `sudo`: unrestricted system access
- `admin`: operational access for the supermarket owner

Only `sudo` users can manage users in Filament.

Both `sudo` and `admin` users can access the inventory dashboard, categories, products, and stock entries.

## Inventory Workflow

Phase 2 introduces the inventory core inside Filament:

- `Categories`: top-level product groupings such as Cosmetics, Toiletries, and Beverages
- `Products`: create each sellable item once with SKU, prices, reorder level, and tracked current stock
- `Stock Entries`: replenish an existing product by searching for it and adding new quantity

The intended operating flow is:

1. Create the product once in `Products`.
2. Reuse that product in `Stock Entries` whenever more quantity is purchased.
3. Optionally create a missing product inline from the Stock Entry form.
4. Watch low-stock and out-of-stock states from the dashboard and product table.

`current_stock` is stored on the `products` table for fast reads, while every replenishment remains preserved in `stock_entries` for history and future Excel-driven workflows.

Stock corrections now belong in `Stock Adjustments`, which keeps a separate ledger for damage write-offs, shrinkage, and physical stock-count reconciliation without rewriting product history.

## Operational Trust Layer

The admin panel now includes:

- `Stock Adjustments`: controlled inventory corrections and count reconciliation
- `Activity Log`: read-only trace of critical inventory, sales import, backup, and settings events
- `System Settings`: sudo-only business configuration
- `Backups`: sudo-only backup history and on-demand recovery snapshots

These features are intended for controlled operational use, not demo scaffolding.

## Backup & Recovery

Create a private recovery snapshot with either of these entry points:

```bash
ddev exec php artisan backups:create --note="Before weekend close"
```

Or use the `Backups` page in Filament as a sudo user.

Backup files are stored on the private local disk under `storage/app/private/backups/...` and tracked in the `backup_runs` table. Each snapshot is a JSON bundle of the first-party business tables plus metadata such as the business name, timezone, and generated timestamp.

For recovery planning:

1. Keep the private backup files and the database dump strategy under sudo control.
2. Use the latest successful `backup_runs` record to identify the snapshot path and checksum.
3. Restore into a clean environment, then review system settings, privileged users, and the activity log before reopening operations.

## Development Inventory Seeder

For realistic local data, run:

```bash
ddev exec php artisan db:seed --class=InventoryDevelopmentSeeder
```

In local development, `php artisan db:seed` already runs the same full demo dataset automatically. This explicit seeder remains useful when you want to repopulate the demo data into an existing local database without changing the environment.

The demo dataset includes:

- roles, sudo, and admin users
- product categories and products
- stock entries and stock adjustments
- sales import batches, successes, and failures
- reporting summaries
- system settings
- activity log data created through the real actions
- a sample recovery backup snapshot

## Bootstrap Sudo User

For non-local environments, create the first sudo user explicitly:

```bash
ddev exec php artisan users:bootstrap-sudo owner@example.com --name="Store Owner" --password="replace-this-password"
```

This keeps the default seed path safe while still giving the team a clear onboarding step for the first privileged account.

## Auth Notes

Fortify remains installed for the existing frontend login, password reset, and account settings flow. Public self-registration is disabled in this phase so user creation stays under sudo control in the Filament panel.
