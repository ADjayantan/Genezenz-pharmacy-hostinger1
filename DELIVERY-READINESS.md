# Delivery readiness — 2026-09-06

Current decision: suitable for continued staging and client review; not ready for live customer handover.

## What was fixed

- Prescription-only orders require an unused, non-rejected prescription owned by the signed-in account. The order transaction locks and links it, snapshots the Rx requirement, and rejects fulfilment progression until pharmacist approval.
- Added `POST /api/prescriptions/upload` and checkout upload/selection. Standalone upload continues to work. Database upload records and audit events now commit together.
- Product create/edit accepts a primary JPG/PNG/WebP upload. It checks real MIME, decoded dimensions, file size, image extension and pixel count, then re-encodes to WebP with a maximum 1600px dimension. A failed product save removes its newly uploaded file.
- Search uses bounded SQL instead of fetching up to 200 complete catalogue rows per dropdown request. Name, brand, composition, description, category and tag queries work in suggestions and the results page. Related suggestions exclude Rx items. Added keyboard selection and accessible option IDs.
- Admin list pages query only their selected section rather than loading all five sections.
- Hero assets preload only on the homepage. Secondary slides are lazy-loaded. Added a pause control, keyboard-focus pause, hidden-tab pause and reduced-motion handling.
- Assets use versioned URLs; caching no longer holds unversioned code for a year. Apache text compression is configured.
- A failed production database returns 503 rather than showing sample stock/prices. Staging and private pages receive noindex headers; robots uses the configured domain. Missing-page canonicals no longer default every content page to the homepage.
- Removed sample customer endorsements and corrected the homepage's blanket seven-day return claim to match the policy.

## Verification

Latest local run: **26 database assertions and 24 HTTP assertions passed; 66 PHP files and the storefront JavaScript passed syntax checks.** Existing route smoke tests, static checks and `git diff --check` also passed. The hosting preflight correctly reports missing production configuration locally; it has not been run on the purchased hosting account.

- Database regression suite covers Rx rejection/ownership/reuse/approval, stock rollback/restoration, trusted prices, migration/backfill and search predicate parity. It uses connection-local temporary tables and does not alter existing pharmacy records.
- HTTP integration suite covers anonymous/CSRF/spoofed/valid prescription uploads, encrypted storage, exact owner decryption and cross-account access denial; direct admin product upload, WebP resize, malicious filename/oversize rejection and cleanup after database failure; admin sections, staging noindex and asset/robots behavior. It creates synthetic local fixtures and removes only those fixtures.
- Desktop browser: hero pause, live matching/related suggestions and ArrowDown/Enter navigation to a product verified.
- 390px browser: product layout and mobile search dropdown visually checked. Full checkout UI and all mobile/admin screens are not yet acceptance-tested.
- PHP/JavaScript syntax, existing route smoke tests and static checks are run separately. Local tests cannot establish real Hostinger/GoDaddy latency, cache behavior or Google indexing.

## Why HTML/CSS/JavaScript is not the cause of slowness

The browser frontend is HTML/CSS/vanilla JavaScript. Secure orders, admin and uploads use PHP/MySQL on the server. A Node.js server is not required. Performance depends on transferred assets, server/database work, hosting resources and traffic; using these browser languages does not inherently make a site slow.

The existing CSS is approximately 35 KB, the JavaScript approximately 18 KB, and each existing WebP hero asset approximately 34–101 KB before transfer compression. These are source file sizes, not a live speed score. No claim about visitors-per-day capacity or live Core Web Vitals is made.

## Hosting fit

Target a Linux web hosting plan with PHP 8.2+, MySQL/MariaDB, GD WebP, OpenSSL, fileinfo, PDO MySQL, cURL, HTTPS, rewrite support and private storage outside the domain document root. Enable OPcache and the two scheduled maintenance/worker jobs.

Hostinger's official Single Web plan table includes PHP workers and databases; GoDaddy's Linux Web Hosting/cPanel product supports the relevant stack. Exact purchased plan and final domain remain unconfirmed and the owner will provide them later. A domain registration or website-builder subscription alone is not confirmation of these capabilities.

Sources checked:
- [Hostinger plan limits](https://www.hostinger.com/support/6976044-parameters-and-limits-of-hosting-plans-in-hostinger/)
- [GoDaddy Web Hosting](https://www.godaddy.com/en-in/hosting/web-hosting)
- [GoDaddy hosting software versions](https://www.godaddy.com/en-in/help/software-versions-installed-on-web-hosting-cpanel-windows-hosting-plesk-and-managed-hosting-for-wordpress-accounts-897)

Run `php scripts/hosting-preflight.php` on the actual host. It checks prerequisites without printing secrets or altering data. CLI and web PHP settings may differ.

## Still required before customer handover

1. Confirm exact hosting product, domain, SSL, private storage, production database, encryption-key backup, scheduled jobs and restore procedure.
2. Apply `database/migrations/001-order-prescription.sql` once to an existing database before the new order code. Fresh installations use the updated schema.
3. Review every published product's actual image, price, stock, tax and Rx flag; approve business contact details, licence/GST/pharmacist fields, banner discounts, delivery claims and policies. Current sample values are not release data.
4. Finish agreed feature parity: multi-image gallery, back-in-stock requests, buy-again, product-aware chat, customer detail, richer analytics/invoices/tracking, and the new design's full visual integration.
5. Finish SEO migration: final canonical host/redirects, legacy policy redirects, location/service-area markup, sitemap coverage and approved product/business structured data. `SEO_INDEXING_ENABLED=false` stays in place until release checks pass.
6. Run end-to-end checkout, pharmacist approval, order fulfilment/cancellation, product editing and mobile acceptance against a staging database on the selected host.
7. Verify production HTTPS/status codes, Apache compression/caching, mobile PageSpeed/Core Web Vitals, and Google Search Console domain ownership/sitemap submission.

## Test commands

Use PHP with the required extensions enabled. Set `TEST_ENV_FILE` to an existing **local test** environment file and `DB_PORT` to that test server's port. Do not use a production environment.

```text
php tests/order-search-regression.php
RUN_LOCAL_UPLOAD_TESTS=1 php tests/upload-http-regression.php
php tests/static-audit.php
node --check public_html/assets/js/app.js
php scripts/hosting-preflight.php
```

The upload suite requires the local app at `http://127.0.0.1:8787` using the same test database and encryption key. The environment-variable prefix shown is shell-dependent; in PowerShell set `$env:RUN_LOCAL_UPLOAD_TESTS='1'` separately.
