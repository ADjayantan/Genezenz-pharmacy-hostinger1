# Migration checkpoint — 2026-09-05

This repository contains an incomplete integration checkpoint, not a production release.

## What is saved

- The original Hostinger1 static design is preserved unchanged in `design-reference/`.
- The existing PHP/MySQL application is imported into `app/`, `public_html/`, `database/`, `cron/`, `scripts/` and `tests/`.
- The imported application includes authentication, server-side orders and stock checks, encrypted prescription storage, admin pages, leads, Meta webhook handling and SEO routes.
- The PHP storefront rendered locally during the audit. The new static design has not yet been fully ported into the PHP templates.

## Required before hosting

1. Enforce prescription requirements in the order transaction, verify upload ownership, link the prescription to the order, and block dispatch until pharmacist approval. The current order backend does not enforce this requirement.
2. Add a JSON prescription upload API and integrate it with checkout. Existing standalone upload storage is not equivalent to the requested integrated API workflow.
3. Add secure direct product-image upload and a gallery in admin. Current product editing supports an image URL only.
4. Improve database search suggestions and related results; the current endpoint returns an empty `related` list.
5. Complete visual integration of the new design and verify hero controls, mobile layout and keyboard access.
6. Restore remaining old-site features: back-in-stock requests, buy again, appropriate product recommendations, product-aware chat, customer detail, richer analytics/invoices and tracking details.
7. Finish SEO hardening: confirmed canonical domain, aligned redirects/robots/sitemap, private-page noindex, truthful location markup and legacy policy redirects.
8. Verify business contact details, licence/GST/pharmacist details, product data, offers and testimonials. Existing sample content is not approved production content.
9. Run fresh database/API/security and browser regression tests on the integrated application, then validate the actual Hostinger configuration.
10. Submit the live verified domain and sitemap through the customer's Google Search Console account. No Google submission or live hosting has been performed.

## Deployment layout

Only the contents of `public_html/` belong in the domain document root. Keep `.env`, `app/`, database scripts, cron scripts and `private_uploads/` above the public directory. Never publish `design-reference/`, `.git/`, tests or internal documentation.

## Verification scope

`TEST-REPORT.md`, `MIGRATION-STATUS.md` and `PARITY-MATRIX.md` were imported from the earlier backend project. Their completion statements are historical and are superseded by the blockers above. Basic syntax and static checks do not establish prescription enforcement, feature parity or deployment readiness.
