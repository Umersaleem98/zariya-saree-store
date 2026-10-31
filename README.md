# Zariya — Laravel saree store

Laravel 12 / PHP 8.2+, MySQL, React and Vite. The black-and-gold storefront, original campaign images, product collections, wishlist, bag, customer accounts, checkout, inventory administration and CSV import/export are preserved from the previous Next.js store. PHP/Laravel now runs the backend; Node.js is needed only to build frontend assets.

## Your local installation

- XAMPP project: `C:\xampp\htdocs\zariya-saree-store`
- Storefront: http://localhost:8088/
- Admin: http://localhost:8088/admin
- MySQL database: `zariya_laravel`
- Admin email: `admin@zariya.local`

The admin password is privately saved in the local `.env` file as `ADMIN_PASSWORD`. It is never committed. The previous `zariya_store` database is preserved separately; old customers/orders are not automatically migrated to this new installation.

Start **Apache** and **MySQL** in XAMPP Control Panel. The included `deploy/xampp-zariya.conf` sets a local-only Apache virtual host on port 8088 pointing to `public/`, so environment files and application source are not web-accessible. It is included from XAMPP's `httpd.conf` on this machine.

For changes: use this repository as the source checkout, build assets, then copy updated source/build files to the XAMPP folder. Keep its `.env`, storage and uploaded assets private. Do not overwrite the runtime environment or database on every update.

## Fresh installation

```sh
composer install
```

Copy `.env.example` to `.env`, configure MySQL and set a unique `ADMIN_PASSWORD` of at least 12 characters. Create the chosen empty database in phpMyAdmin, then run:

```sh
php artisan key:generate
npm ci
npm run build
php artisan migrate --seed
php artisan serve --host=127.0.0.1 --port=8000
```

For the built-in PHP server use `APP_URL=http://127.0.0.1:8000`. For Apache use the supplied virtual-host example and change paths/port as necessary. The web document root **must** be the `public` folder.

## Features and security

- Laravel session authentication, session regeneration and CSRF-protected mutations.
- Customers must sign in or register before ordering; shopping bag persists locally.
- Server-side validation, database role checks on every admin endpoint, login throttling.
- MySQL transactions, row locks, server prices and shipping, duplicate-cart consolidation.
- Cash on delivery enabled by default, optional bank transfer controlled by admin; card payments remain unavailable until a gateway is integrated.
- Product CRUD/archive, inventory, categories, brands, customers, order management, product CSV import/export and order export.
- Google OAuth via Laravel Socialite; supply your own credentials. Administrators use email/password.

Configure `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET` and `GOOGLE_REDIRECT_URI` in `.env`. Local callback: `http://localhost:8088/api/auth/google/callback`. Google credentials and consent configuration are still required; this flow cannot be verified with missing credentials.

## Checks

```sh
php artisan test
npm run build
```

Tests use transactions against the configured **development** MySQL database and roll back changes. Do not run them against a production database. GitHub Actions creates a disposable MySQL database for tests.

## Online hosting

Netlify cannot run this Laravel/PHP backend. Its existing website remains the last deployed Next.js preview. Commits for the Laravel replacement use `[skip netlify]` to avoid replacing that preview with an incompatible deployment. Use PHP 8.2+ hosting with MySQL, Composer dependencies, a `public/` document root, HTTPS, and writable `storage`/`bootstrap/cache` folders. See [DEPLOYMENT.md](DEPLOYMENT.md).

Before selling, replace sample inventory with actual product photos/prices and confirm your business contact, return policy and delivery terms. Live card payments, mail-based password reset, courier integration and transactional email are not included.

Save all addresses from [LINKS.md](LINKS.md). The prior Next.js implementation remains recoverable in Git history.
