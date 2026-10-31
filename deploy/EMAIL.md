# Transactional email

Sender requested: Rang Mahal <info@rangmahal.site>. Provision the mailbox and verify ownership before enabling SMTP; never commit credentials. Use cPanel Connect Devices secure SMTP hostname/port, certificate verification enabled, MAIL_SCHEME=smtps for implicit TLS on 465, or smtp with STARTTLS on 587. MAIL_FROM_ADDRESS and MAIL_USERNAME must match the authorized mailbox. MAIL_FROM_NAME="Rang Mahal". Local development remains log-only.

The additive 2026_11_01_000000_order_email_outbox migration stores an immutable email snapshot in the same database transaction as order placement, real status transitions, or changed shipment/payment details. Existing orders are not backfilled or emailed. Checkout retries and identical status/detail saves do not enqueue duplicates. HTML and text receipts contain real order lines, charges, reference, verified tracking link, actual courier/ETA when supplied and policy information. Catalogue edits do not email customers or alter historical order lines.

Install a one-minute cron after SMTP configuration, preserving other cron entries:

    * * * * * /usr/bin/flock -n /home/jjt1xvidnzva/apps/rangmahal/storage/framework/order-emails.lock /usr/local/bin/php /home/jjt1xvidnzva/apps/rangmahal/artisan orders:send-emails --limit=50 >> /home/jjt1xvidnzva/apps/rangmahal/storage/logs/order-email-worker.log 2>&1

Confirm actual PHP/flock paths on the host first. Worker claims each row with a database lock and lease, retries with bounded exponential backoff, and stops after eight attempts. Monitor unsent attempts>=8 and private application logs. SMTP acceptance records sent_at; it does not prove inbox delivery. A process failure between SMTP acceptance and recording sent_at can result in a duplicate; no SMTP implementation can promise exactly-once inbox delivery. No exceptions roll back a successfully placed order because sending is outside checkout.

SPF must include the actual outbound sender without duplicate SPF records; publish DKIM from cPanel and aligned DMARC initially in monitoring mode after inspecting existing DNS. Do not overwrite existing mail provider MX/SPF records blindly. Confirm forward/reverse DNS and host relay configuration with GoDaddy. Send a test to a business-authorized mailbox and inspect Authentication-Results for SPF/DKIM/DMARC, plus Inbox/Spam placement. Inbox placement cannot be guaranteed. Do not send marketing broadcasts through order notifications.

Tests cover queueing once for checkout retries, professional receipt content, status-update deduplication, delivery failure/backoff/recovery, and no stock mutation on email retry.
