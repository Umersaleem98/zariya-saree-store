# Deploy the Laravel version

This project now requires PHP 8.2+ and MySQL. Netlify's existing Next.js deployment cannot execute Laravel and remains a separate old preview. Do not deploy this repository as a static website.

1. Choose PHP/Laravel hosting with MySQL and HTTPS. Do not assume that a free static hosting provider supports PHP.
2. Install dependencies with `composer install --no-dev --optimize-autoloader`.
3. Build assets with `npm ci && npm run build`.
4. Set the document root to this project's `public` directory. Never expose `.env`, `vendor`, source or storage logs.
5. Configure production `.env`: `APP_ENV=production`, `APP_DEBUG=false`, actual HTTPS `APP_URL`, MySQL settings, `SESSION_SECURE_COOKIE=true`, and a private admin email/password for initial seeding. Keep a stable private `APP_KEY`; run `php artisan key:generate` only for initial setup.
6. Create the database, then `php artisan migrate --seed --force`. Existing seed data is preserved. Seeding does not change an existing administrator password.
7. Make `storage` and `bootstrap/cache` writable by the PHP process. Run `php artisan config:cache` and `php artisan view:cache`.
8. Configure Google OAuth separately if needed and verify signup, admin authentication, order placement, inventory and payment methods.
9. Add backups, approved return/privacy policies and real contact information before accepting sales.

The new local database `zariya_laravel` starts with sample inventory. The old Next.js database `zariya_store` is preserved. Migrating its real customer/order records requires a separate data migration and password strategy because Next.js used scrypt hashes and Laravel uses bcrypt.
