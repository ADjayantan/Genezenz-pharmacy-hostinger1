# Deploy checklist: Genezenz Hostinger release

Date: 2026-09-02 · Deployer: to be assigned

## Pre-deploy

- [ ] Exact plan supports required PHP extensions, MySQL, cron, SSL and non-public storage.
- [ ] `CLIENT-INPUT-REQUIRED.md` blocking items are resolved and signed off.
- [ ] Production database and site backups downloaded and restorable.
- [ ] `schema.sql` tested on staging; no sample users/admin passwords exist.
- [ ] `.env` is outside `public_html`, `APP_DEBUG=false`, and keys are unique production values.
- [ ] Private upload directory is writable to PHP and not reachable by URL.
- [ ] Real catalogue reviewed; restricted products and unlicensed images remain unpublished.
- [ ] Rollback owner, previous release archive and DNS/document-root rollback steps are recorded.

## Deploy to staging

- [ ] Import schema and create admin through `scripts/create-admin.php`.
- [ ] Verify home, search, product, cart, login/register, checkout and profile.
- [ ] Place a low-value test order; verify server price, stock decrement and tracking.
- [ ] Cancel it twice; confirm stock is restored once only.
- [ ] Upload a synthetic prescription; verify anonymous access returns 404 and admin review is audited.
- [ ] Verify admin product/order/Rx/lead/customer workflows and printable invoice.
- [ ] Verify Meta challenge/signature/duplicate handling if credentials are enabled.
- [ ] Run `tests/static-audit.php` and `tests/http-smoke.ps1`.
- [ ] Check 390, 768 and 1440 px layouts, keyboard navigation, console and PHP error log.
- [ ] Validate HTTPS redirect, CSP, cache headers, robots, sitemap and structured data.

## Production cutover

- [ ] Take final catalogue/order delta and database backup.
- [ ] Upload the exact staging-tested release; import the approved production data.
- [ ] Enable cron and SSL; switch document root/DNS.
- [ ] Test register/login, one product, cart, checkout, Rx and admin without using real patient data.
- [ ] Monitor 4xx/5xx, PHP logs, disk quota, failed login/upload/webhook jobs for 72 hours.
- [ ] Submit `/sitemap.xml` to Google Search Console after canonical domain verification.

## Rollback triggers

- Any prescription or order visible to the wrong account.
- Stock/total mismatch, duplicate stock restoration or checkout failure on valid stock.
- Site-wide login failure, sustained 5xx above 2%, or PHP fatal errors on critical routes.
- Private `.env`, SQL, logs or encrypted upload directory reachable from the public web.
- Webhook retry storm, disk exhaustion or invalid compliance/product content published.

On trigger: disable new checkout/uploads if possible, restore previous document root/DNS, disable cron, restore the pre-deploy DB only when needed, and preserve audit/private data for authorised investigation.
