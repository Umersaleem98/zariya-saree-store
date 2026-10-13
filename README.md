# Zariya — Next.js saree boutique

Save the store, admin, GitHub and hosting links from [LINKS.md](LINKS.md).

GitHub: [Umersaleem98/zariya-saree-store](https://github.com/Umersaleem98/zariya-saree-store). For free hosting with Netlify and cloud MySQL, see [DEPLOYMENT.md](DEPLOYMENT.md).

Published storefront: https://zariya-saree-store.netlify.app/ — currently in sample-catalog mode until the cloud MySQL database is configured. Registration, admin data and order placement require that database. Local XAMPP credentials do not connect to the hosted site.

A black-and-gold ecommerce store for Pakistani shoppers, built with Next.js App Router, React and MySQL/MariaDB (XAMPP). Original AI-generated editorial images and sample products are included for design preview. Replace them with your actual products before selling.

## Run locally

1. Install Node.js 22.8 or later and XAMPP. Start **MySQL** in XAMPP Control Panel. Apache is only needed for phpMyAdmin; Next.js runs its own server.
2. Run `npm install` in this project.
3. Copy `.env.example` to `.env.local`. Configure your database host, port, username and password. Default XAMPP credentials are commonly `root` with a blank password; use a dedicated database user for production.
4. Set `ADMIN_EMAIL` and a unique `ADMIN_PASSWORD` of at least 12 characters. These are used only when seeding the administrator. Never commit `.env.local`.
5. Run `npm run db:setup`. This creates `zariya_store`, the tables, six sample products, categories, brands, and the admin account. Existing records are preserved. Changing the environment password does not reset an existing account.
6. Run `npm run dev` and open http://127.0.0.1:3000.
7. Open http://127.0.0.1:3000/admin and sign in with the administrator credentials you configured.

If MySQL is unavailable, the storefront displays the sample catalog. Checkout is explicitly disabled in sample mode, and database actions show a setup error. Sample mode does not pretend to persist orders.

## Features

- Responsive editorial storefront with black, gold, ivory and rose colours, subtle motion and reduced-motion support.
- Collection filters, search, brand filtering, price sorting, product details, stock-aware shopping bag and device-local wishlist.
- Customer registration, sign in/out, order history and order tracking using order reference plus email. Customers must sign in before checkout; the shopping bag is preserved and registration/sign-in returns them to checkout.
- Checkout with Pakistani delivery addresses and PKR pricing. Cash on delivery is enabled by default. Bank transfer is disabled by default and can be enabled in Admin → Payment settings.
- Optional Google sign-in with authorization code flow, PKCE, state/nonce validation and verified Google ID tokens.
- Authenticated admin dashboard with delivered revenue, order counts, active products and low-stock alerts.
- Product create/edit/archive/restore, stock editing, categories and brands, registered customer list.
- Product CSV import/export, downloadable CSV template and order CSV export.
- Orders with customer/address/item details and controlled status transitions. Cancelling a pending or confirmed order restores its stock once.

## Database and authentication

`database/schema.sql` defines the schema. `scripts/setup-db.mjs` creates the database and seeds it using your environment settings; import the SQL through phpMyAdmin only if you want to create tables manually (you will still need to seed an admin).

Server-side SQL uses parameterized queries. Passwords are salted with scrypt. Sessions use random tokens, store only SHA-256 hashes in SQL, expire after seven days, and use HTTP-only SameSite cookies. Production cookies require HTTPS. Admin access is checked at every admin endpoint. Mutation requests enforce same origin. Sign-in attempts are throttled per email in SQL.

Checkout recalculates prices and shipping on the server and reserves stock inside an SQL transaction with row locks. Duplicate cart entries are consolidated and locked in product-ID order. Checkout never trusts client-provided prices. Order states progress Pending → Confirmed → Shipped → Delivered; pending or confirmed orders may be cancelled.

## Payments and launch requirements

Admin → **Payment settings** controls which supported payment methods appear at checkout. The API rejects disabled methods even if a caller submits them directly. At least one supported option must stay enabled. Online card payments remain deactivated and hidden until a verified gateway is integrated; the admin panel shows their unavailable status.

**Live card payments are not integrated.** The store never collects card numbers. Connect an approved payment provider, add hosted checkout and verified payment webhooks before offering cards. Bank-transfer orders remain pending; add your business bank details and verify transfers before confirming/dispatching.

Before a public launch, replace sample images/prices/stock, confirm shipping estimates, add your real business contact information, approve the returns/privacy policies, configure transactional email/SMS, add database backups, and deploy with HTTPS and a secured MySQL account. Tax/invoicing, coupons, refunds, courier APIs, password reset email, and payment gateway integrations are not included in this version.

XAMPP MySQL is a local development database. A public deployment needs a reachable secured MySQL server and a Node.js host; the database on your personal computer is not automatically available to an online host.

## Enable Sign in with Google

Email/password registration works immediately with Gmail or any valid email. For the separate **Continue with Google** option:

1. Create a Web application OAuth client in Google Cloud Console and configure the consent screen.
2. Add `http://127.0.0.1:3000/api/auth/google/callback` as an authorized redirect URI for local development. Use the exact matching HTTPS callback URL for a public deployment.
3. Add `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET` and `APP_URL=http://127.0.0.1:3000` to `.env.local`. Keep the client secret on the server.
4. Restart the server (and rebuild if using `npm start`). The Google button becomes available when credentials are configured.

Successful Google registration creates a customer account and resumes checkout when requested. Administrators continue using email/password. Google sign-in cannot be verified end to end until real OAuth credentials are provided. See the [official Google web-server OAuth guide](https://developers.google.com/identity/protocols/oauth2/web-server).

## Commands

```sh
npm run dev
npm test
npm run build
npm start
```

## Images

Optimized JPEGs are under `public/images`. The images are original generated concept assets, not photographs of real inventory. Admin products accept an HTTPS image URL or a file path such as `/images/my-product.jpg`; add your own files under `public/images`.

## Key files

- `app/page.jsx`, `components/Store.jsx`: storefront and shopping interactions.
- `app/admin/page.jsx`: dashboard, inventory and orders.
- `app/api/[...path]/route.js`: backend endpoints.
- `lib/db.mjs`, `lib/auth.mjs`, `lib/security.mjs`: database and authentication.
- `database/schema.sql`, `scripts/setup-db.mjs`: SQL schema and seed.
- `tests/core.test.mjs`: security, CSV, delivery and cart validation checks.

Next.js route handlers and cookies follow the [official App Router documentation](https://nextjs.org/docs/app/api-reference/file-conventions/route).
