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
ddev exec php artisan test
ddev npm run dev
```

## Access

- App URL: `http://supermarket.test`
- Filament admin: `http://supermarket.test/admin`
- Mailpit: `http://supermarket.test:8025`

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

## Development Inventory Seeder

For realistic local data, run:

```bash
ddev exec php artisan db:seed --class=InventoryDevelopmentSeeder
```

This optional seeder creates supermarket-style categories, products, and sample stock entries without replacing the default bootstrap seeding.

## Bootstrap Sudo User

The database seeder automatically creates a development bootstrap sudo user.

- Email: `akinjoseph221@gmail.com`
- Password: `akinjoseph221@gmail.com`

These defaults are for local development only. Override them with `SUDO_EMAIL` and `SUDO_PASSWORD`, and do not keep these credentials for production deployments.

## Auth Notes

Fortify remains installed for the existing frontend login, password reset, and account settings flow. Public self-registration is disabled in this phase so user creation stays under sudo control in the Filament panel.
