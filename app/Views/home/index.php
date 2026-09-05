<?php

declare(strict_types=1);

$faqs = [
    ['q' => 'Do you deliver medicines in Coimbatore?', 'a' => 'Yes. We offer doorstep delivery within a 10km radius of Ganapathy. Orders placed before 2 PM are dispatched the same day.'],
    ['q' => 'How do I order prescription medicines?', 'a' => 'Upload a clear photo or PDF of your doctor’s prescription. A licensed pharmacist reviews it, confirms availability and prepares your order before dispatch.'],
    ['q' => 'Are the medicines genuine?', 'a' => 'Every product is sourced from licensed manufacturers and distributors. Ask our pharmacist if you need source or batch information.'],
    ['q' => 'What is the delivery charge?', 'a' => 'Delivery is free on orders above ₹' . $site['offers']['free_delivery_above'] . '. Below that a small fee applies, shown at checkout before you confirm.'],
];
$faqSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => array_map(static fn (array $faq): array => [
        '@type' => 'Question',
        'name' => $faq['q'],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['a']],
    ], $faqs),
];
$categoryIcons = [
    'diabetes-care' => '<path d="M24 6c-3 5-10 10-10 18a10 10 0 0 0 20 0C34 16 27 11 24 6Z"/><path d="M20 26h8M24 22v8"/>',
    'vitamins' => '<rect x="16" y="8" width="16" height="32" rx="8"/><line x1="16" y1="24" x2="32" y2="24"/><circle cx="24" cy="16" r="2"/>',
    'cold-fever' => '<rect x="21" y="6" width="6" height="30" rx="3"/><circle cx="24" cy="36" r="5"/><path d="M24 14v16"/>',
    'personal-care' => '<path d="M24 38s-12-7-12-16a8 8 0 0 1 12-7 8 8 0 0 1 12 7c0 9-12 16-12 16Z"/>',
    'baby-care' => '<path d="M18 10h6v8h-6zM14 18h14a4 4 0 0 1 4 4v12a4 4 0 0 1-4 4H18a4 4 0 0 1-4-4V22a4 4 0 0 1 0-4Z"/><path d="M24 10V6"/>',
    'pain-relief' => '<path d="M24 6v36M6 24h36"/><rect x="10" y="10" width="28" height="28" rx="4"/>',
];
$trustPoints = [
    ['title' => 'Licensed Pharmacist', 'description' => 'Every order verified by a qualified pharmacist', 'icon' => '<path d="M12 11c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4Z"/><path d="M6 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/><path d="M15 7h2m-1-1v2"/>'],
    ['title' => 'Same-Day Delivery', 'description' => 'Order before 2 PM, get it today across Coimbatore', 'icon' => '<path d="M1 3h15v13H1z"/><path d="M16 8h4l3 3v5h-7V8Z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>'],
    ['title' => 'Genuine Medicines', 'description' => 'Sourced only from licensed distributors', 'icon' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/>'],
    ['title' => 'Easy Returns', 'description' => 'Hassle-free returns within 7 days if sealed', 'icon' => '<polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/>'],
];
$steps = [
    ['number' => '01', 'title' => 'Search or browse', 'description' => 'Find your medicine by name, salt, or category. Use the search bar or browse the counter.'],
    ['number' => '02', 'title' => 'Upload prescription', 'description' => 'Prescription medicines need a valid Rx. Upload a photo — our pharmacist verifies it before dispatch.'],
    ['number' => '03', 'title' => 'Get it delivered', 'description' => 'Same-day dispatch before 2 PM. Free delivery on orders above ₹499 across Coimbatore.'],
];
$testimonials = [
    ['name' => 'Priya R.', 'area' => 'Saibaba Colony', 'text' => 'I have been ordering from Genezenz for over a year. The pharmacist always calls to confirm my diabetes medicines — that personal touch means a lot.'],
    ['name' => 'Karthik M.', 'area' => 'RS Puram', 'text' => 'Fastest delivery I have seen for a local pharmacy. Ordered Paracetamol and vitamins at 11 AM, got them by 3 PM. Prices are fair too.'],
    ['name' => 'Lakshmi S.', 'area' => 'Ganapathy', 'text' => 'As a new mother, I rely on them for baby care products. They never send substitutes without asking first. Very trustworthy pharmacy.'],
];
?>
<script type="application/ld+json" nonce="<?= csp_nonce() ?>"><?= json_encode($faqSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>

<section class="promo-section" aria-label="Current pharmacy promotions">
  <div class="container promo-carousel" data-carousel>
    <div class="promo-track">
      <?php
      $banners = [
          ['welcome', '/products', 'Welcome to Genezenz Pharmacy — first-order offer'],
          ['medicines', '/upload-prescription', 'Genuine medicines — upload your prescription'],
          ['skincare', '/products?cat=personal-care', 'Derma and beauty care'],
          ['active', '/products?cat=pain-relief', 'Wellness, fitness and pain care'],
      ];
      foreach ($banners as [$name, $href, $alt]): ?>
        <a class="promo-slide" href="<?= app_url($href) ?>">
          <picture>
            <source media="(max-width: 767px)" srcset="<?= asset('images/banners/banner-' . $name . '-mobile.webp') ?>" type="image/webp">
            <img src="<?= asset('images/banners/banner-' . $name . '-desktop.webp') ?>" width="1600" height="800" alt="<?= e($alt) ?>">
          </picture>
        </a>
      <?php endforeach; ?>
    </div>
    <div class="promo-dots" role="group" aria-label="Choose promotion">
      <?php foreach ($banners as $index => $_): ?><button type="button" aria-label="Show promotion <?= $index + 1 ?>" aria-current="<?= $index === 0 ? 'true' : 'false' ?>" data-slide="<?= $index ?>"><span></span></button><?php endforeach; ?>
    </div>
  </div>
</section>

<section class="hero section-border">
  <div class="container hero-grid">
    <div class="hero-copy">
      <p class="rule-label">Pharmacist verified · Established <?= e($site['founded']) ?></p>
      <h1>Medicines, <em>dispensed</em><br> by people who know you.</h1>
      <div class="offer-pills"><span>Medicines 18% OFF</span><span>Surgicals 25% OFF</span></div>
      <p class="hero-intro">Doorstep delivery within 10km of Ganapathy. Can’t find your medicine? Upload your prescription.</p>
      <div class="hero-actions"><a class="button button--primary" href="<?= app_url('/products') ?>">Browse the counter</a><a class="button button--outline" href="<?= app_url('/upload-prescription') ?>">Upload a prescription</a></div>
      <p class="hero-proof"><span>Pharmacist verified</span><i aria-hidden="true">•</i><span>Same-day dispatch</span></p>
    </div>

    <aside class="panel panel--amber">
      <p class="rule-label rule-label--plain">Call me back</p>
      <h2>Tell us what you need.</h2>
      <p>Our pharmacist calls you back and confirms availability.</p>
      <form class="lead-form" action="<?= app_url('/api/leads') ?>" method="post" data-lead-form>
        <?= csrf_field() ?>
        <label class="honeypot" aria-hidden="true">Website<input name="website" tabindex="-1" autocomplete="off"></label>
        <div class="field-grid">
          <label><span>Your name</span><input name="name" required minlength="2" maxlength="80" placeholder="Ravi Kumar" autocomplete="name"></label>
          <label><span>Phone</span><input name="phone" required inputmode="tel" placeholder="98765 43210" autocomplete="tel"></label>
        </div>
        <label><span>What do you need? <small>Optional</small></span><textarea name="message" maxlength="1000" rows="3" placeholder="Medicine name, or upload your prescription after we call"></textarea></label>
        <p class="form-status" role="status"></p>
        <button class="button button--primary button--full" type="submit">Request a callback</button>
        <small class="form-note">We call back within opening hours. Your details are never shared.</small>
      </form>
    </aside>
  </div>
</section>

<section class="trust-section section-border">
  <div class="container trust-grid">
    <?php foreach ($trustPoints as $point): ?>
      <article><div class="trust-icon"><svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?= $point['icon'] ?></svg></div><h2><?= e($point['title']) ?></h2><p><?= e($point['description']) ?></p></article>
    <?php endforeach; ?>
  </div>
</section>

<?php if ($categories !== []): ?>
<section class="section-space">
  <div class="container">
    <header class="section-heading"><p class="rule-label">Browse</p><h2>Shop by Category</h2></header>
    <div class="category-grid">
      <?php foreach ($categories as $category): ?>
        <a class="category-card" href="<?= app_url('/products?cat=' . $category['slug']) ?>">
          <svg viewBox="0 0 48 48" width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true"><?= $categoryIcons[$category['slug']] ?? $categoryIcons['personal-care'] ?></svg>
          <span><strong><?= e($category['name']) ?></strong><small><?= (int) $category['product_count'] ?> products</small></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="process-section section-border section-space">
  <div class="container">
    <header class="section-heading"><p class="rule-label">The process</p><h2>How it works</h2></header>
    <ol class="process-grid">
      <?php foreach ($steps as $index => $step): ?><li><span><?= e($step['number']) ?></span><h3><?= e($step['title']) ?></h3><p><?= e($step['description']) ?></p><?php if ($index < count($steps) - 1): ?><i aria-hidden="true"></i><?php endif; ?></li><?php endforeach; ?>
    </ol>
  </div>
</section>

<?php if ($products !== []): ?>
<section class="section-space">
  <div class="container">
    <header class="product-section-head"><div><p class="rule-label">From the counter</p><h2>Popular this month</h2></div><a href="<?= app_url('/products') ?>">All medicines →</a></header>
    <div class="product-grid">
      <?php foreach ($products as $product): require BASE_PATH . '/app/Views/partials/product-card.php'; endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section-space testimonials">
  <div class="container">
    <header class="section-heading"><p class="rule-label">Local trust</p><h2>What our customers say</h2></header>
    <div class="testimonial-grid">
      <?php foreach ($testimonials as $testimonial): ?>
        <figure><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4C2.75 3 2 3.75 2 4.97V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.03V20c0 1 0 1 1 1z"/><path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.97V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1z"/></svg><blockquote>“<?= e($testimonial['text']) ?>”</blockquote><figcaption><strong><?= e($testimonial['name']) ?></strong><span><?= e($testimonial['area']) ?></span></figcaption></figure>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="about-band section-border section-space">
  <div class="container prose-narrow"><p class="rule-label">About the shop</p><h2>Trusted medicine delivery across Coimbatore</h2><p>Genezenz Pharmacy has served families in Ganapathy and greater Coimbatore since <?= e($site['founded']) ?>. We stock medicines and everyday healthcare products across pain relief, diabetes care, vitamins, baby care and more.</p><p>Every prescription order is reviewed by a pharmacist before it leaves our counter. We call if the prescription needs clarification and never substitute a medicine without asking first.</p><p>Place your order before <?= e($site['offers']['dispatch_cutoff']) ?> for same-day dispatch. Delivery is free on orders above <?= money($site['offers']['free_delivery_above']) ?>.</p></div>
</section>

<section class="section-space"><div class="container prose-narrow"><p class="rule-label">Common questions</p><h2>Frequently asked</h2><div class="faq-list"><?php foreach ($faqs as $index => $faq): ?><details <?= $index === 0 ? 'open' : '' ?>><summary><?= e($faq['q']) ?></summary><p><?= e($faq['a']) ?></p></details><?php endforeach; ?></div></div></section>
