# Genezenz Pharmacy — Hostinger release

Production-targeted rewrite of the original Next.js pharmacy for PHP 8.2+, MySQL/InnoDB, HTML, CSS and vanilla JavaScript. The existing Next.js source is untouched. Only `public_html/` is web-accessible; application code, secrets, SQL, cron files and encrypted prescriptions stay above the document root.

## Included flows

- Responsive home, catalogue, filters/sorting/pagination, product detail, cart and accessible search.
- Customer registration/login/logout, opaque database sessions, profile, checkout, order history and tracking.
- Server-authoritative prices, transactional stock decrement and idempotent stock restoration on cancellation.
- Private prescription upload, MIME/signature validation, 8 MB cap, AES-256-GCM encryption, ownership checks and pharmacist audit history.
- Admin dashboard, orders/tracking/invoices, Rx review, products, customers and leads.
- Meta lead webhook signature verification, deduplicated job queue, retry worker and housekeeping cron.
- About, contact, insurance, six legal policies, eight Coimbatore service-area pages, dynamic sitemap, robots and JSON-LD.
- CSP, CSRF, prepared statements, persistent rate limits, safe errors and HTTPS/security headers.

## Hostinger requirements

- PHP 8.2+ with `pdo_mysql`, `openssl`, `fileinfo`, `curl`, `mbstring` and `json`.
- MySQL 8 compatible/MariaDB database using InnoDB.
- Apache rewrite/headers support, SSL and cron jobs.
- A writable private directory outside the public document root.

If the purchased plan lacks PHP, MySQL, cron or storage outside `public_html`, do not deploy prescriptions/admin/order processing on that plan.

## Local quick start

1. Copy `.env.example` to `.env`; set a local URL and database credentials.
2. Import `database/schema.sql`, then `database/seed.sql` for staging data only.
3. Generate `APP_KEY` and `FILE_ENCRYPTION_KEY`:

   ```bash
   php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
   php -r "echo base64_encode(random_bytes(32)), PHP_EOL;"
   ```

4. Create the first admin:

   ```bash
   php scripts/create-admin.php admin@example.com "Store Administrator"
   ```

5. Run locally:

   ```bash
   php -S 127.0.0.1:8090 -t public_html dev-router.php
   ```

6. Run checks:

   ```powershell
   php tests/static-audit.php
   powershell -File tests/http-smoke.ps1 -BaseUrl http://127.0.0.1:8090
   ```

Without MySQL, public catalogue pages deliberately use the eight-item fallback only for layout preview. Login, leads, checkout and uploads fail safely instead of pretending data was saved.

## Hostinger staging deployment

1. In hPanel create a staging subdomain, MySQL database and least-privilege database user.
2. Import `database/schema.sql`. Import `database/seed.sql` only for staging.
3. Upload `app/`, `cron/`, `database/`, `scripts/`, `private_uploads/`, `.env` and supporting files one level above the domain's `public_html`.
4. Upload the contents of this package's `public_html/` into the domain's actual document root.
5. If Hostinger forces application files into another path, update the `require` path at the top of `public_html/index.php`; do not expose `.env`, SQL or prescriptions.
6. Set `APP_ENV=production`, `APP_DEBUG=false`, the HTTPS `APP_URL`, random keys, DB credentials and verified business data in `.env`.
7. Make `private_uploads` writable by PHP, normally `0750` for the directory and `0640` for files. Never use `0777` unless Hostinger support explicitly requires and secures it.
8. Run `scripts/create-admin.php` from hPanel terminal/SSH. Do not seed a default admin password in SQL.
9. Configure cron:

   ```text
   */5 * * * * /usr/bin/php /home/ACCOUNT/DOMAIN/cron/process-meta.php
   17 2 * * * /usr/bin/php /home/ACCOUNT/DOMAIN/cron/housekeeping.php
   ```

10. Enable SSL, confirm forced HTTPS, then run the smoke test and the checklist in `DEPLOYMENT-CHECKLIST.md`.

## Catalogue migration

The legacy endpoint currently exposes 124 items in 15 categories. Run a dry run first:

```bash
php scripts/import-legacy-catalogue.php
php scripts/import-legacy-catalogue.php --write
```

The importer rejects known competitor image hosts and imports every legacy item as **unpublished**. An administrator must verify price, stock, description, image rights and Rx/legal listing status before publishing. It never imports unverifiable ratings/review counts.

## Meta webhook

Set `META_APP_SECRET`, `META_VERIFY_TOKEN`, `META_PAGE_ACCESS_TOKEN` and `META_GRAPH_VERSION`. Configure Meta callback URL as `/api/webhooks/meta`. GET verification uses the verify token; POST requires `X-Hub-Signature-256`. Valid events are deduplicated and queued, and cron retrieves lead details.

## Backup and rollback

Before cutover, export the existing site/database and the new MySQL database, archive the uploaded release and record DNS values. To roll back: restore old DNS/document root, restore the pre-deploy MySQL dump if any write migration must be undone, disable new cron entries, and retain encrypted prescriptions/audit data for authorised recovery rather than deleting them.

Rollback immediately for order/stock inconsistency, prescription access leakage, repeated 5xx responses, invalid checkout totals, failed login for all users or webhook retry storms.

## Production values still required

See `CLIENT-INPUT-REQUIRED.md`. The release cannot truthfully invent pharmacy licence/GST/pharmacist details, approve policies/testimonials, choose the real coupon, or certify legacy product prices and images.
