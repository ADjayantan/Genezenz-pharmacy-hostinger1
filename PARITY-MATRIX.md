# Route and feature parity matrix

| Original route | Hostinger route/module | Status | Verification |
|---|---|---|---|
| `/` | `HomeController` | Complete | 200, desktop/mobile visual smoke |
| `/products` | `ProductsController::index` | Complete | Search/category/stock/Rx/sort/page query flow |
| `/products/[slug]` | `ProductsController::show` | Complete | 200, server product HTML + JSON-LD + cart |
| `/cart` | `ShopController::cart` | Complete | Local cart, quantity/removal, threshold and total |
| `/checkout` | `ShopController::checkout` | Complete | Login gate, validation, server re-price/stock transaction |
| `/login`, `/register` | `AuthController` | Complete | Password hashing, rate limit, opaque DB session |
| `/profile` | `ShopController::profile` | Complete | Auth gate, order/Rx history |
| `/order/[orderNo]` | `ShopController::order` | Complete | Ownership-gated tracking timeline |
| `/upload-prescription` | `PrescriptionController` | Complete | MIME/size validation, AES-GCM, private file route |
| `/about`, `/contact`, `/insurance` | `ContentController::page` | Complete | Server-rendered content routes |
| `/legal`, `/legal/[slug]` | `ContentController` | Complete | Index plus six policies |
| `/pharmacy-in-[area]-coimbatore` | `ContentController::area` | Complete | Eight configured areas |
| `/admin/login` | Admin login + `AuthController` | Complete | Role gate and 8-hour admin session |
| `/admin` | `AdminController::dashboard` | Complete | Orders/Rx/leads/stock/sales attention stats |
| `/admin/products*` | Product admin | Complete | Create/edit/hide/delete-safe behavior |
| `/admin/orders*` | Order admin/invoice | Complete | Status/tracking/audit/cancel restore |
| `/admin/prescriptions` | Rx admin | Complete | Secure file/review/note/audit |
| `/admin/leads` | Lead admin | Complete | Unified source/status/assignment/notes |
| `/admin/customers` | Customer admin | Complete | Account/order/spend listing |
| `/api/search` | Product search API | Complete | Keyboard autocomplete and server search |
| `/api/leads` | Lead capture API | Complete | CSRF, honeypot, validation, persistent limit |
| `/api/orders` | Transactional order API | Complete | Server authority and conditional stock update |
| `/api/prescriptions/file/[id]` | Private file controller | Complete | Owner/admin only; anonymous 404 |
| `/api/webhooks/meta` | `MetaController` + cron | Complete | Challenge, raw HMAC, dedupe, retry worker |
| `/api/fda` | Not migrated | Deliberate | External FDA proxy not required for current user journeys; no secret exposed |
| `/api/recommendations` | Server-safe catalogue cards | Partial | Unsafe Rx cross-sells omitted; algorithmic recommendations not exposed |

## Deliberate safety deviations

- Legacy ratings/review counts are not imported because no underlying reviews were supplied.
- Competitor-hosted and broken images are removed; honest placeholders are used.
- Legacy catalogue import defaults to unpublished until client/pharmacist verification.
- No browser-only fake persistence is used for users, orders, leads or prescriptions.
