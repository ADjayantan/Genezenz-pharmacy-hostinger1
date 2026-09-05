# Verification report — 2026-09-02

> Historical tests from the imported backend project, not fresh verification of the Hostinger1 integration. See `CURRENT-STATUS.md` for current gaps and release status.

Environment: PHP 8.4.25, MariaDB 11.4.8, Windows local Hostinger-like PHP/MySQL stack.

## Passed

- 62 PHP files: syntax clean.
- Vanilla JavaScript: `node --check` clean.
- Static security/route/schema audit: all assertions pass.
- HTTP smoke: home, catalogue, cart, login, registration, about, contact, insurance, legal, area, sitemap and 404 returned expected status.
- Search API returned Paracetamol; catalogue in-stock/OTC/price sorting rendered successfully.
- Customer registration/login/profile using opaque MySQL-backed session passed.
- Checkout created order `qty=2`; product stock changed 120 → 118.
- Admin cancellation restored stock 118 → 120; repeating cancellation left stock at 120.
- Prescription PNG passed signature validation, stored as encrypted `GZRX1` bytes, decrypted for its owner and returned anonymous 404.
- Admin dashboard/order/Rx views loaded; pharmacist approval recorded reviewer identity.
- Meta GET verification returned the challenge; signed POST accepted; duplicate signed delivery produced one queue row; invalid signature returned 401.
- Legacy importer dry run read 124/124 valid products and 15 categories. Write test imported all 124 as unpublished.
- Desktop 1440×1000 and mobile 390×844 cart/product UI visually inspected; no horizontal overflow and no browser console warnings/errors.
- Cart add, badge, quantity presentation, delivery threshold and total verified. Chat refused a dosage request.
- No `.env`, SQL, logs or prescription files exist under `public_html`; no test credentials remain in release source.

## Not claimable until staging/client access

- Hostinger hPanel upload, exact PHP extensions/cron/private path and production DNS/SSL.
- Lighthouse scores on the final Hostinger domain and real-network latency.
- Real payment flow: current approved implementation is COD only.
- Live Meta Graph lead retrieval requires client tokens/app approval; queue signing/dedupe was integration-tested with synthetic payloads.
- Production catalogue/business/legal accuracy requires client and pharmacist review.
- Real patient prescription testing is intentionally prohibited; only a synthetic image was used and removed.

The code package is deployable to staging. Production cutover remains gated by `CLIENT-INPUT-REQUIRED.md` and `DEPLOYMENT-CHECKLIST.md`.
