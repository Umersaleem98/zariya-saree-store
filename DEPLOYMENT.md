# Free live hosting: Netlify + Aiven MySQL

Repository: https://github.com/Umersaleem98/zariya-saree-store

Netlify supports commercial projects on its Free plan and provides a free `name.netlify.app` address. Aiven offers a free MySQL service. These plans have resource/usage limits; a free subdomain is different from owning a registered `.com` domain.

## 1. Create your cloud MySQL database

Sign in at https://console.aiven.io/ and choose **MySQL → Free plan**. Do not select a paid trial or paid plan. Wait for the service to run, then copy its host, port, username and password, and download its project CA certificate.

Store these values privately in the hosting environment. Do not add a cloud password or CA certificate to GitHub. The application verifies the database TLS certificate.

| Variable      | Value                                                  |
| ------------- | ------------------------------------------------------ |
| `DB_HOST`     | Aiven service hostname                                 |
| `DB_PORT`     | Aiven service port                                     |
| `DB_USER`     | Aiven service username                                 |
| `DB_PASSWORD` | Aiven service password                                 |
| `DB_NAME`     | `defaultdb`, or your chosen database                   |
| `DB_SSL`      | `true`                                                 |
| `DB_SSL_CA`   | Entire project CA PEM, with literal `\n` between lines |

Seed this database once using `npm run db:setup` with those environment values and your chosen `ADMIN_EMAIL`/`ADMIN_PASSWORD` (at least 12 characters). The setup script uses externally supplied variables before `.env.local` and preserves existing data. It never needs to run at every deploy.

To use a separate environment file without replacing local XAMPP configuration, create an ignored `.env.production.local` and run:

```sh
node --env-file=.env.production.local scripts/setup-db.mjs
```

Use a new production admin password. The existing local database is not automatically copied to the hosted service. Seeding starts with sample inventory; migrating real orders and customers requires a separate, private data migration.

## 2. Deploy the Next.js store

1. Sign in at https://app.netlify.com/ and select the **Free plan**.
2. Add/import a project from GitHub and select `Umersaleem98/zariya-saree-store`, branch `main`.
3. The included `netlify.toml` sets `npm run build`, `.next` output and Node 24. Netlify automatically uses its Next.js adapter.
4. Add the database variables above as secret environment variables, available to Functions/runtime (and builds if required by the dashboard).
5. Set `APP_URL` to the site's exact HTTPS production URL, for example `https://your-available-name.netlify.app`. This controls OAuth redirects and same-origin security checks. Do not leave it set to localhost.
6. Deploy. If available, change the project name to `zariya-saree-store` to get `zariya-saree-store.netlify.app`; availability is checked by Netlify and the name is not reserved by this document.
7. Visit `/admin`, sign in with the admin you seeded in the cloud database, and verify products and Payment settings.

The production URL should not be treated as a complete live shop until `/api/catalog` returns `demo:false` and signup/checkout work against the cloud database. A sample-only storefront cannot accept real orders.

## 3. Optional Google login

Add `GOOGLE_CLIENT_ID` and `GOOGLE_CLIENT_SECRET` as server-side secrets. In Google Cloud Console register the exact redirect URI:

```text
https://your-actual-site.netlify.app/api/auth/google/callback
```

Use the same actual site URL for `APP_URL`. Redeploy after updating configuration.

## Costs and limits

Netlify Free sites pause when their monthly allocation is exhausted. Aiven Free has limited storage/connections and may pause inactive services. Stay on the explicit free plans and monitor usage. Custom registered domains usually require purchasing separately; the Netlify subdomain and HTTPS are provided by the hosting service.

Official references: [Netlify Free commercial projects](https://www.netlify.com/blog/introducing-netlify-free-plan/), [Next.js support](https://docs.netlify.com/build/frameworks/framework-setup-guides/nextjs/overview/), [Netlify domains](https://docs.netlify.com/manage/domains/domains-fundamentals/understand-domains/), [Aiven MySQL free tier](https://aiven.io/docs/products/mysql/concepts/mysql-free-tier), [Aiven TLS certificates](https://aiven.io/docs/platform/concepts/tls-ssl-certificates).
