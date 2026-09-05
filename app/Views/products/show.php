<?php

declare(strict_types=1);

$discount = !empty($product['mrp']) && (float) $product['mrp'] > (float) $product['price']
    ? (int) round((((float) $product['mrp'] - (float) $product['price']) / (float) $product['mrp']) * 100)
    : 0;
$cartProduct = [
    'id' => (string) $product['id'], 'name' => $product['name'], 'slug' => $product['slug'],
    'price' => (float) $product['price'], 'mrp' => $product['mrp'] ? (float) $product['mrp'] : null,
    'stock' => (int) $product['stock'], 'imageUrl' => $product['image_url'], 'brand' => $product['brand'],
    'rxRequired' => (bool) $product['rx_required'],
];
$productSchema = [
    '@context' => 'https://schema.org', '@type' => 'Product', 'name' => $product['name'],
    'description' => $product['description'], 'sku' => (string) $product['id'],
    'brand' => $product['brand'] ? ['@type' => 'Brand', 'name' => $product['brand']] : null,
    'offers' => ['@type' => 'Offer', 'priceCurrency' => 'INR', 'price' => (string) $product['price'],
        'availability' => (int) $product['stock'] > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
        'url' => app_url('/products/' . $product['slug'])],
];
?>
<script type="application/ld+json" nonce="<?= csp_nonce() ?>"><?= json_encode(array_filter($productSchema, static fn ($value) => $value !== null), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>

<nav class="breadcrumbs container" aria-label="Breadcrumb"><a href="<?= app_url('/') ?>">Home</a><span>/</span><a href="<?= app_url('/products') ?>">Medicines</a><?php if (!empty($product['category_name'])): ?><span>/</span><a href="<?= app_url('/products?cat=' . $product['category_slug']) ?>"><?= e($product['category_name']) ?></a><?php endif; ?></nav>

<article class="product-detail container">
  <div class="product-detail__image">
    <?php if (!empty($product['image_url'])): ?><img src="<?= e($product['image_url']) ?>" alt="<?= e($product['name']) ?>"><?php else: ?><svg class="mortar-glyph" viewBox="0 0 64 64" width="100" height="100" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true"><path d="M14 28h36c-1 14-8 22-18 22S15 42 14 28Z"/><path d="M24 50h16M38 10l10 6-17 19M42 8l8 5"/></svg><?php endif; ?>
  </div>
  <div class="product-detail__content">
    <p class="rule-label"><?= e($product['category_name'] ?? 'Medicine') ?></p>
    <div class="product-detail__tags"><?php if (!empty($product['rx_required'])): ?><span class="tag tag--rx">℞ Prescription required</span><?php endif; ?><?php if ($discount): ?><span class="tag tag--offer"><?= $discount ?>% off</span><?php endif; ?></div>
    <h1><?= e($product['name']) ?></h1>
    <?php if (!empty($product['brand'])): ?><p class="product-detail__brand">By <?= e($product['brand']) ?></p><?php endif; ?>
    <?php if (!empty($product['salt_name'])): ?><dl class="composition"><dt>Composition</dt><dd><?= e($product['salt_name']) ?></dd></dl><?php endif; ?>
    <p class="product-detail__price"><strong><?= money($product['price']) ?></strong><?php if ($discount): ?><del><?= money($product['mrp']) ?></del><span>inclusive of taxes</span><?php endif; ?></p>
    <p class="stock-line <?= (int) $product['stock'] <= 0 ? 'stock-line--out' : '' ?>"><?= (int) $product['stock'] > 0 ? 'In stock · ready for pharmacist confirmation' : 'Out of stock' ?></p>
    <p class="product-detail__description"><?= e($product['description']) ?></p>
    <?php if (!empty($product['rx_required'])): ?><div class="rx-notice"><strong>A valid prescription is required.</strong><p>Upload it at checkout. Do not change or start a prescribed medicine without asking your doctor or pharmacist.</p></div><?php endif; ?>
    <div class="product-detail__actions"><button class="button button--primary add-to-cart" type="button" <?= (int) $product['stock'] <= 0 ? 'disabled' : '' ?> data-product="<?= e(json_encode($cartProduct, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ?>">Add to cart</button><a class="button button--outline" href="https://wa.me/<?= e($site['whatsapp']) ?>?text=<?= rawurlencode('Hello, I need ' . $product['name']) ?>" target="_blank" rel="noopener noreferrer">Ask on WhatsApp</a></div>
  </div>
</article>

<?php if (!empty($product['content'])): ?><section class="section-border section-space"><div class="container prose-narrow"><p class="rule-label">Product information</p><h2>About this medicine</h2><?php foreach (preg_split('/\R{2,}/', trim((string) $product['content'])) ?: [] as $paragraph): ?><p><?= e($paragraph) ?></p><?php endforeach; ?></div></section><?php endif; ?>
