# Migration status

> Historical report from the imported backend. The current Hostinger1 integration is incomplete; see `CURRENT-STATUS.md` for known blockers that supersede the completion labels below.

Reference commit: `da78886138d779d5caf22dbd3c5cf65c536a5ed1`

| Area | Status | Evidence |
|---|---|---|
| Shared storefront/home | Complete | Desktop and 390 px visual smoke; zero horizontal overflow |
| Catalogue/product/search | Complete | Server HTML; search/filter/sort/pagination; Product JSON-LD |
| Cart/checkout/orders | Complete | Cart interaction; MySQL transaction; stock 120→118 for qty 2 |
| Customer authentication/profile | Complete | Register/login/database session/profile integration test |
| Order tracking/admin cancellation | Complete | Admin flow; stock restored 118→120 and stayed 120 on repeat cancel |
| Prescription workflow | Complete | Valid PNG encrypted to `GZRX1` container; authorised download restored PNG; anonymous request 404 |
| Admin back office | Complete | Dashboard, products, orders, invoice, Rx, leads, customers |
| Meta webhook/worker | Complete | Invalid signature returned 401; signed queue/worker implementation audited |
| Legal/content/area routes | Complete | 3 content, 6 policy and 8 service-area routes |
| SEO/security | Complete | Dynamic sitemap, robots, JSON-LD, CSP, CSRF, rate limiting, safe errors |
| Legacy importer | Complete, awaiting business review | Dry run found 124/124 items and 15 categories; write imports unpublished |
| Hostinger staging | Awaiting account access | Exact plan, database, domain, secrets and production content required |

“Code complete” does not mean “legally/content approved.” Production publication remains gated by `CLIENT-INPUT-REQUIRED.md` and the staging checklist.
