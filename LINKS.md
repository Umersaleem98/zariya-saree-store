# Zariya — save these project links

## Public website

- Storefront: https://zariya-saree-store.netlify.app/
- Admin login: https://zariya-saree-store.netlify.app/admin

These are the production addresses. GitHub updates deploy to the same website address. The hosted storefront currently uses the sample catalog; customer accounts, admin data and order placement need the cloud MySQL database to be configured.

## Code and hosting management

- GitHub repository: https://github.com/Umersaleem98/zariya-saree-store
- Saved website images: https://github.com/Umersaleem98/zariya-saree-store/tree/main/public/images
- This saved link list: https://github.com/Umersaleem98/zariya-saree-store/blob/main/LINKS.md
- Netlify project: https://app.netlify.com/projects/zariya-saree-store/overview
- Deployment history: https://app.netlify.com/projects/zariya-saree-store/deploys
- Hosting environment variables: https://app.netlify.com/projects/zariya-saree-store/configuration/env
- Domain settings: https://app.netlify.com/projects/zariya-saree-store/domain-management
- Database console/sign-in: https://console.aiven.io/
- Setup guide: https://github.com/Umersaleem98/zariya-saree-store/blob/main/DEPLOYMENT.md
- Google login configuration (optional): https://console.cloud.google.com/apis/credentials

Management links require your own provider login. Passwords and database credentials are intentionally not included in this file or in GitHub.

## Keep the addresses working over time

Keep ownership of your GitHub and Netlify accounts, keep the Netlify project named `zariya-saree-store`, and do not delete the project. Its production address stays the same when code or images change. Use the production link rather than a deploy-preview link.

Free hosting is subject to provider rules and usage limits, so permanent unlimited uptime is not guaranteed. Netlify may pause a free project when its allowance is exhausted; Aiven may pause inactive free databases. Monitor the accounts and make periodic private database backups once the database is live.

The `.netlify.app` address is a hosting subdomain. A separately registered domain can be added later while the default address remains available.

Official references: [Netlify production domains](https://docs.netlify.com/manage/domains/domains-fundamentals/understand-domains/), [Netlify plans](https://www.netlify.com/pricing/), [Aiven MySQL free tier](https://aiven.io/docs/products/mysql/concepts/mysql-free-tier).

## Local development only

- Local storefront: http://127.0.0.1:3000/
- Local admin: http://127.0.0.1:3000/admin

Local links work only on the computer running the Next.js server. Local XAMPP database settings are separate from the live website.
