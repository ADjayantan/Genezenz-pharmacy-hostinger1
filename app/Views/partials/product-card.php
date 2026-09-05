<?php

declare(strict_types=1);

$discount = !empty($product['mrp']) && (float) $product['mrp'] > (float) $product['price']
    ? (int) round((((float) $product['mrp'] - (float) $product['price']) / (float) $product['mrp']) * 100)
    : 0;
$cartProduct = [
    'id' => (string) $product['id'],
    'name' => $product['name'],
    'slug' => $product['slug'],
    'price' => (float) $product['price'],
    'mrp' => $product['mrp'] !== null ? (float) $product['mrp'] : null,
    'stock' => (int) $product['stock'],
    'imageUrl' => $product['image_url'],
    'brand' => $product['brand'],
    'rxRequired' => (bool) $product['rx_required'],
];
?>
<article class="product-card <?= !empty($product['rx_required']) ? 'product-card--rx' : '' ?>">
  <a class="product-card__body" href="<?= app_url('/products/' . $product['slug']) ?>">
    <div class="product-card__image">
      <?php if (!empty($product['image_url'])): ?>
        <img src="<?= e($product['image_url']) ?>" alt="<?= e($product['name']) ?>" loading="lazy">
      <?php else: ?>
        <svg class="mortar-glyph" viewBox="0 0 64 64" width="56" height="56" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true"><path d="M14 28h36c-1 14-8 22-18 22S15 42 14 28Z"/><path d="M24 50h16M38 10l10 6-17 19M42 8l8 5"/></svg>
      <?php endif; ?>
    </div>
    <div class="product-card__tags">
      <?php if (!empty($product['rx_required'])): ?><span class="tag tag--rx">℞ Prescription</span><?php endif; ?>
      <?php if ($discount > 0): ?><span class="tag tag--offer"><?= $discount ?>% off</span><?php endif; ?>
    </div>
    <h3><?= e($product['name']) ?></h3>
    <?php if (!empty($product['brand'])): ?><p class="product-card__brand"><?= e($product['brand']) ?></p><?php endif; ?>
    <p class="product-card__price"><strong><?= money($product['price']) ?></strong><?php if ($discount): ?><del><?= money($product['mrp']) ?></del><?php endif; ?></p>
    <p class="stock-line <?= (int) $product['stock'] <= 0 ? 'stock-line--out' : '' ?>"><?= (int) $product['stock'] > 0 ? 'In stock' : 'Out of stock' ?></p>
  </a>
  <button class="button button--primary button--full add-to-cart" type="button" <?= (int) $product['stock'] <= 0 ? 'disabled' : '' ?> data-product="<?= e(json_encode($cartProduct, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ?>">Add to cart</button>
</article>
