# Genezenz Pharmacy — Static Site (Hostinger-ready)

Macha, idhu **fully static** version — HTML + CSS + JS only, no Node.js, no database.
Adhanaala **Hostinger basic (shared) plan-la** direct-a velai seyyum. Fast + best-SEO built-in.

Old Next.js site-la irundha **design, pages, products, SEO ellam** intha version-la
recreate panniruken. What changed and why → see "What's different" section below.

---

## 1. Folder contents

```
genezenz-site/
├── index.html                      → Home
├── products/
│   ├── index.html                  → All medicines (search / filter / sort)
│   └── <product-slug>/index.html   → 20 product pages (one folder each)
├── about/ contact/ insurance/ upload-prescription/ cart/
├── pharmacy-in-<area>-coimbatore/  → 8 local SEO landing pages
├── assets/
│   ├── styles.css                  → all styling (Apothecary Editorial theme)
│   ├── include.js                  → shared header + footer (edit nav ONCE here)
│   ├── products-data.js            → product list for search box
│   ├── main.js                     → nav, search, catalogue filter, forms
│   ├── cart.js                     → cart + WhatsApp checkout
│   └── logo.svg                    → logo / favicon
├── sitemap.xml  robots.txt  site.webmanifest  .htaccess  404.html
└── DEPLOY.md   (this file)
```

---

## 2. Upload to Hostinger (easiest way — hPanel File Manager)

1. Login → **hPanel** → **Files → File Manager**.
2. Open the **`public_html`** folder of your domain.
3. If there is an old `index.html` / default file there, delete it.
4. Upload **everything inside `genezenz-site/`** (NOT the `genezenz-site` folder itself —
   the contents must sit directly inside `public_html`).
   - Easiest: zip the contents, upload the zip, then "Extract" inside `public_html`.
   - Make sure the hidden **`.htaccess`** file also gets uploaded (enable "show hidden files").
5. Visit your domain — done. ✅

**FTP alternative:** use FileZilla with your Hostinger FTP details, drop the same files into `public_html`.

---

## 3. IMPORTANT — set your real domain

Every page has a `canonical` URL and the sitemap/structured-data use
**`https://genezenz-pharmacy.in`** (I guessed this from your email `care@genezenz-pharmacy.in`).

**If your real domain is different**, do a find-and-replace across all files:

- Find:  `https://genezenz-pharmacy.in`
- Replace with:  `https://YOUR-REAL-DOMAIN`

(Do it in a code editor like VS Code — "Replace in Files" — before uploading. It appears in
the `<link canonical>`, Open Graph tags, JSON-LD, `sitemap.xml` and `robots.txt`.)

Also update the host inside `.htaccess` if you are not using the www→non-www redirect as-is.

---

## 4. Things you'll want to change (all easy)

**WhatsApp number / phone** — currently `918044560873`. It appears in:
`assets/include.js`, `assets/main.js`, `assets/cart.js`, and the `tel:`/`wa.me` links in pages.
Find `918044560873` and replace with your number (country code, no `+`, no spaces).

**Nav links, footer, address, hours** → edit **once** in `assets/include.js`.

**Prices / products** — prices are the placeholders from the old build. To change a price:
edit the product's own page in `products/<slug>/index.html` (the `now`, `price-mrp`, `save`
and the `data-price` on the Add button), the matching card in `products/index.html`, and the
price in `assets/products-data.js`. To add a new product, copy an existing product folder,
rename the slug, update the content, then add a card to `products/index.html`, an entry to
`products-data.js`, and a `<url>` line to `sitemap.xml`.

**Compliance details** (legally required for a pharmacy) — the footer shows
`GSTIN: to be added · Drug Licence No.: to be added · Pharmacist-in-charge: to be added`.
Fill these in `assets/include.js` (footer section) with your real numbers before going live.

**Logo / photos** — `assets/logo.svg` is a placeholder mark. Drop in your real logo (keep the
filename `logo.svg`, or replace with a PNG and update the `<link rel="icon">` + `<img>` refs).
Product images are clean SVG placeholders — you can later add real photos if you want.

---

## 5. Google SEO — go-live checklist (do these, ranking depends on it)

1. **Google Search Console** → add your domain (Settings → Ownership; Hostinger lets you add a
   DNS TXT or HTML-tag verification). Then **Sitemaps → submit** `sitemap.xml`.
2. **Google Business Profile** (free) → create/claim "Genezenz Pharmacy", set the exact map pin,
   hours, phone and website. **This is the single biggest lever for "medical shop near me"
   searches** — the local pack ranks on distance + reviews, not the website alone.
3. **Verify structured data** → run each key page through
   [search.google.com/test/rich-results](https://search.google.com/test/rich-results).
   Pharmacy, Product, FAQ and Breadcrumb schema are already built in.
4. **Ask happy customers for Google reviews.** (Note: the site deliberately does NOT put a
   "4.8★ / 50,000 customers" rating in the code — fake/unverifiable ratings are a Google penalty
   risk and, for a licensed pharmacy, a compliance risk. Real Google Business reviews are the
   safe way to show ratings.)
5. **PageSpeed** → run [pagespeed.web.dev](https://pagespeed.web.dev). Should score very high;
   the only external request is Google Fonts.

---

## 6. What's different from the old Next.js site (and why)

The old site was a full-stack app (Next.js + PostgreSQL + admin panel + login + API webhooks).
That needs a **Node.js server**, which the Hostinger basic plan does not run — that's exactly
why it couldn't be hosted. This static version keeps everything a static host CAN do, and drops
what genuinely needs a server:

| Feature | Old site | This static site |
|---|---|---|
| Design, pages, SEO, structured data | ✅ | ✅ (recreated) |
| Product catalogue, search, filter, cart | ✅ (server) | ✅ (in-browser) |
| Local area landing pages | ✅ | ✅ (all 8) |
| Checkout / orders | DB orders | **WhatsApp order** (cart → prefilled WhatsApp message) |
| Lead / "call me back" forms | DB inbox | **WhatsApp** (form → prefilled message) |
| Prescription upload | server upload | **WhatsApp** (attach photo in chat) |
| Admin dashboard, login, DB, Meta webhooks | ✅ | ❌ (needs a server) |

For a single-branch pharmacy whose goal is "rank on Google + pull WhatsApp leads", this covers
the real workflow. If you later need the admin panel / online payments / accounts back, that
needs either a PHP+MySQL rewrite (still works on Hostinger basic) or a Node-capable plan — happy
to build either when you want.

---

Enjoyu da macha 🚀 — ethachum doubt-na kelu.
