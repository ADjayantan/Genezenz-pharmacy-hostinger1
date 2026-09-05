<?php declare(strict_types=1); ?>
<section class="catalogue-hero section-border">
  <div class="container">
    <p class="rule-label">The counter</p>
    <h1>Medicines &amp; healthcare</h1>
    <p>Search by medicine, brand or salt name. Prescription-only products are clearly marked and reviewed by a pharmacist.</p>
  </div>
</section>

<section class="section-space catalogue-section">
  <div class="container catalogue-layout">
    <aside class="catalogue-filters">
      <form action="<?= app_url('/products') ?>" method="get">
        <label><span>Search</span><input type="search" name="q" value="<?= e($query) ?>" placeholder="Medicine or salt name"></label>
        <label><span>Category</span><select name="cat"><option value="">All categories</option><?php foreach ($categories as $item): ?><option value="<?= e($item['slug']) ?>" <?= $category === $item['slug'] ? 'selected' : '' ?>><?= e($item['name']) ?></option><?php endforeach; ?></select></label>
        <label><span>Availability</span><select name="availability"><option value="">Any availability</option><option value="in-stock" <?=$availability==='in-stock'?'selected':''?>>In stock</option></select></label>
        <label><span>Medicine type</span><select name="type"><option value="">Rx and OTC</option><option value="otc" <?=$type==='otc'?'selected':''?>>OTC only</option><option value="rx" <?=$type==='rx'?'selected':''?>>Prescription only</option></select></label>
        <label><span>Sort</span><select name="sort"><option value="name" <?=$sort==='name'?'selected':''?>>Name A–Z</option><option value="price-low" <?=$sort==='price-low'?'selected':''?>>Price: low first</option><option value="price-high" <?=$sort==='price-high'?'selected':''?>>Price: high first</option><option value="stock" <?=$sort==='stock'?'selected':''?>>Availability</option></select></label>
        <button class="button button--primary button--full" type="submit">Apply filters</button>
        <?php if ($query !== '' || $category !== '' || $availability !== '' || $type !== '' || $sort !== 'name'): ?><a class="filter-clear" href="<?= app_url('/products') ?>">Clear filters</a><?php endif; ?>
      </form>
    </aside>

    <div class="catalogue-results">
      <div class="catalogue-meta"><p><strong><?= e($total) ?></strong> products</p><?php if ($query !== ''): ?><p>Results for “<?= e($query) ?>”</p><?php endif; ?></div>
      <?php if ($products === []): ?>
        <div class="catalogue-empty"><h2>No medicines found</h2><p>Try a shorter name, search by salt, or call the pharmacist.</p><a class="button button--outline" href="tel:<?= e($site['phone']) ?>">Call <?= e($site['phone_display']) ?></a></div>
      <?php else: ?>
        <div class="product-grid"><?php foreach ($products as $product): require BASE_PATH . '/app/Views/partials/product-card.php'; endforeach; ?></div>
        <?php if($pages>1):?><nav class="pagination" aria-label="Catalogue pages"><?php for($n=1;$n<=$pages;$n++):$params=array_filter(['q'=>$query,'cat'=>$category,'availability'=>$availability,'type'=>$type,'sort'=>$sort,'page'=>$n],fn($v)=>$v!=='');?><a href="<?=app_url('/products?'.http_build_query($params))?>" <?=$n===$page?'aria-current="page"':''?>><?=$n?></a><?php endfor;?></nav><?php endif;?>
      <?php endif; ?>
    </div>
  </div>
</section>
