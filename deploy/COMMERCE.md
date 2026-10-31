# Rang Mahal commerce operations

Customers shop as guests. Add to Bag opens the drawer from the right. Products with multiple enabled variations first open the option selector. Checkout collects contact and delivery information, including postal code. COD never asks for card details. Save the RM tracking ID shown on confirmation; it also appears in the confirmation email when a real mail transport is configured. New RM IDs allow tracking without login; old ZR references retain email verification. Tracking exposes item snapshots, total, city and status, not email, phone or full address.

## Products, sales and stock

Use **Products & stock → Edit**. Each product has Unstitched and Stitched options with separate regular prices, optional sale prices, enabled switches and remaining stock. A sale price must be below its regular price. Disable sale pricing to use the regular price. The discount percentage is calculated automatically. Mark a product Featured to show it in the dedicated homepage section. Disabling a variation preserves its historical order items. Archiving a product hides it without deleting order history.

The migration copies existing available stock and existing prices into Unstitched. Stitched starts disabled with zero stock; enable it only after entering real price/quantity. Existing products, users and orders are preserved. Low-stock alerts check each enabled variation (five or fewer), not only the combined product total.

Upload up to four JPEG, PNG or WebP images per request, each at most 4 MB, 4096 px per side and 12 megapixels overall. Images are resized to a maximum 1400 px, encoded as WebP at quality 80 and stripped of input metadata. Use Make cover or Remove to adjust the gallery. A new upload replaces the editor's gallery; save the product to persist it. Uploads live in private application storage and are served through a constrained media route, so future deployments preserve them. Server PHP upload/post limits may impose a lower combined upload size.

## Orders

Authorized staff advance orders through Pending, Confirmed, Processing, Packed, Shipped, Out for Delivery and Delivered. Cancellation is allowed before dispatch and restores the reserved variation stock once. Delivered/cancelled orders are final. Each line item retains its ordered variation, price and quantity even after product edits. There is no courier API integration; staff update these statuses manually.

## Access

- **Super Admin:** all commerce, users, roles and settings; at least one active owner must remain.
- **Admin:** commerce, customer records and payment settings; cannot manage staff access.
- **Order Manager:** orders, delivery statuses and guest customer list.
- **Product Manager:** products, stock, sale/featured settings, uploads and categories/brands.
- **Staff:** product/order read access by default.

Super Admin can replace a staff member's default permissions with a custom selection. User/role administration remains exclusive to Super Admin. Customer accounts from the old installation remain preserved but cannot sign into the dashboard. User edits invalidate prior sessions; a user cannot delete, disable or demote their own owner account. Deleting a user preserves orders through the existing nullable foreign key. Protect permissions on the API, regardless of menu visibility.

## Owner credential rotation

The CLI command `php artisan store:rotate-admin --current-email=OLD_EMAIL --email=NEW_EMAIL --output=PRIVATE_FILE` rotates an existing Super Admin password, invalidates old sessions and writes the new password outside the web root. Update private ADMIN_EMAIL/ADMIN_PASSWORD seed configuration to match afterwards; never reseed production as a password-reset mechanism. Do not commit credentials.

## Verification

Run `php artisan test` against the local/development MySQL database, then `npm run build`. Feature tests use rolled-back transactions and cover guest orders, stock, duplicate request protection, tracking privacy, status transitions, permission checks, user management, image validation/conversion, sale pricing and featured flags. Never run the test suite against production. Production deployment takes private database/application backups before additive migrations.
