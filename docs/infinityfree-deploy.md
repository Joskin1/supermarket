# InfinityFree SQL Import Guide

This project now has two MySQL import files you can use:

## 1. Clean schema only

Use this when you want an empty database structure:

`database/schema/whitemart-infinityfree-schema.sql`

This creates the tables, indexes, and foreign keys only.

## 2. Full demo database

Use this when you want the tables plus demo/sample data:

`database/schema/whitemart-infinityfree-demo.sql`

This includes:

- users and roles
- categories and products
- stock entries and stock adjustments
- sales imports, sales records, and failures
- reporting summaries
- system settings
- activity logs
- backup runs

Important:

- the demo SQL contains your current local seeded data
- it also includes the current demo sudo user and other local demo users
- import it only if you want that sample data on the hosted database

## InfinityFree import flow

1. Create a new MySQL database in InfinityFree.
2. Open phpMyAdmin from the InfinityFree panel.
3. Select the new database.
4. Click `Import`.
5. Choose one of the SQL files above.
6. Start the import and wait for it to finish.

## Which file should you use?

- Use `whitemart-infinityfree-schema.sql` if you want a fresh production-style database.
- Use `whitemart-infinityfree-demo.sql` if you want the hosted app to open with sample data already loaded.

## App configuration after import

After importing, update your hosted environment to match your InfinityFree database:

- `APP_NAME`
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL`
- `DB_CONNECTION=mysql`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`

See `docs/infinityfree.env.example` for a starter template.
