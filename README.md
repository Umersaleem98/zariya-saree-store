# Rang Mahal — Laravel saree store

Laravel 12 / PHP 8.2+, MySQL, React and Vite. The black-and-gold storefront, original campaign images, product collections, wishlist, bag, guest checkout, inventory administration and CSV import/export are preserved from the previous Next.js store. PHP/Laravel now runs the backend; Node.js is needed only to build frontend assets.

## Your local installation

- XAMPP project: `C:\xampp\htdocs\zariya-saree-store`
- Storefront: http://localhost:8088/
- Admin: http://localhost:8088/admin
- MySQL database: `zariya_laravel`
- Admin email: `owner@rangmahal.site`

The current local login is privately saved in `.local-tools/deployment/local-admin.txt`; production uses separate credentials in `.local-tools/deployment/production-admin.txt`. Passwords are never committed. The previous `zariya_store` database is preserved separately; old customers/orders are not automatically migrated to this new installation.

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
- Guest checkout: name, email, phone, complete address, city and postal code. No customer accounts or registration.
- Right-side shopping bag, separate Stitched/Unstitched prices and stock, gallery of up to four compressed WebP images.
- Sale prices and discount badges, featured homepage products, low-stock alerts per variation.
- Tracking by high-entropy RM tracking ID without login; old ZR references still require the original email.
- Sequential delivery states: Pending → Confirmed → Processing → Packed → Shipped → Out for Delivery → Delivered, with cancellation before dispatch.
- Idempotent checkout, transaction-locked variation stock, server prices and one-time cancellation stock restoration.
- Server-side validation, database role checks on every admin endpoint, login throttling.
- MySQL transactions, row locks, server prices and shipping, duplicate-cart consolidation.
- Cash on delivery enabled by default, optional bank transfer controlled by admin; card payments remain unavailable until a gateway is integrated.
- Product CRUD/archive, inventory, categories, brands, customers, order management, product CSV import/export and order export.
- Super Admin, Admin, Order Manager, Product Manager and Staff roles with backend permissions. Only Super Admin manages users and role assignments. Disabled accounts and credential/access changes invalidate existing staff sessions.
- Customer checkout never creates users or authenticates a session. Public signup/Google login routes are disabled.
- Order confirmation email is sent only when a real mail provider is configured; the default log/array mailers do not send.

See [commerce operations](deploy/COMMERCE.md) for product options, image uploads and permissions.

## Checks

```sh
php artisan test
npm run build
```

Tests use transactions against the configured **development** MySQL database and roll back changes. Do not run them against a production database. GitHub Actions creates a disposable MySQL database for tests.

## Online hosting

Production runs on GoDaddy cPanel at **https://rangmahal.site/**; administration is at **https://rangmahal.site/admin**. It uses a dedicated production MySQL database, HTTPS, private application configuration, and a read-only GitHub deployment key. See [the production deployment guide](deploy/README.md) for cPanel update steps, backups, and recovery. Production admin credentials are saved privately outside GitHub; the local XAMPP credentials above apply only to the development installation.

Netlify remains the last deployed Next.js preview and cannot run this Laravel/PHP backend. Laravel commits use `[skip netlify]`. For generic PHP hosting requirements see [DEPLOYMENT.md](DEPLOYMENT.md).

Before selling, replace sample inventory with actual product photos/prices and confirm your business contact, return policy and delivery terms. Live card payments, mail-based password reset, courier integration are not included. Order confirmation email requires a configured mail provider.

Save all addresses from [LINKS.md](LINKS.md). The prior Next.js implementation remains recoverable in Git history.
