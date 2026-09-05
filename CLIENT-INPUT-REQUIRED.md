# Client input required before production

## Blocking launch data

- Exact Hostinger plan and confirmation of PHP 8.2+, MySQL, cron, SSL and private storage.
- Production domain and hPanel/database credentials, supplied privately and never committed.
- GSTIN, drug-licence number, pharmacist-in-charge name and registration number.
- Verified store address, phone, operating hours, delivery radius and Google Business Profile coordinates.
- Final catalogue prices, MRP, stock, batches, expiry, GST rates and owned/licensed product images.
- Pharmacist/legal decision for Schedule H/H1 and other restricted products. The importer leaves all legacy products unpublished.
- Approved privacy, cookie, terms, shipping, returns and prescription wording.
- Written approval or removal of testimonials and promotional/statistical claims copied from the reference site.
- Confirm first-order coupon: source config says `GENEZENZ10`; one existing banner says `GENEZENZ20`.
- Meta App/Page credentials and approved webhook subscription if Meta lead capture is required at launch.
- Analytics ID and explicit consent/measurement requirements.

## Launch decision rule

Do not publish a product, compliance claim or customer claim until the responsible client/pharmacist has verified it. The code is configured to show placeholders for missing compliance identifiers and keep imported legacy products unpublished.
