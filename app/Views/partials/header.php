<?php declare(strict_types=1); ?>
<div class="offer-strip">
  <div class="container offer-strip__inner" aria-label="Current offers">
    <span>Medicines 18% OFF</span><i aria-hidden="true"></i>
    <span>Surgicals 25% OFF</span><i aria-hidden="true"></i>
    <span>Diapers 40% OFF</span><i aria-hidden="true"></i>
    <span class="offer-strip__delivery">Delivery in 10km</span>
  </div>
</div>

<header class="site-header">
  <div class="container site-header__row">
    <a class="wordmark" href="<?= app_url('/') ?>" aria-label="<?= e($site['name']) ?> home">
      <img src="<?= asset('images/logo.png') ?>" width="34" height="34" alt="">
      <span>Genezenz <b>Pharmacy</b></span>
    </a>

    <div class="header-search header-search--desktop"><?php $searchInstance = 'desktop'; require BASE_PATH . '/app/Views/partials/search.php'; ?></div>

    <nav class="main-nav" aria-label="Main">
      <a href="<?= app_url('/products') ?>">Medicines</a>
      <a href="<?= app_url('/upload-prescription') ?>">Upload Rx</a>
      <a href="<?= app_url('/products?cat=baby-care') ?>">Baby Care</a>
      <a href="<?= app_url('/contact') ?>">Contact</a>
    </nav>

    <div class="header-actions">
      <a class="icon-button cart-link" href="<?= app_url('/cart') ?>" aria-label="Shopping cart">
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" aria-hidden="true"><path d="M3 3h2l2.4 11h9.8l2-8H6"/><circle cx="9" cy="19" r="1.5"/><circle cx="17" cy="19" r="1.5"/></svg>
        <span class="cart-count" aria-label="0 items">0</span>
      </a>
      <?php $headerUser=current_user(); ?>
      <a class="icon-button" href="<?= app_url($headerUser ? '/profile' : '/login') ?>" aria-label="<?= $headerUser ? 'My account' : 'Sign in' ?>">
        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
      </a>
    </div>
  </div>

  <div class="container header-search header-search--mobile"><?php $searchInstance = 'mobile'; require BASE_PATH . '/app/Views/partials/search.php'; ?></div>

  <nav class="mobile-nav" aria-label="Categories">
    <div class="container">
      <a href="<?= app_url('/products') ?>">Medicines</a>
      <a href="<?= app_url('/upload-prescription') ?>">Upload Rx</a>
      <a href="<?= app_url('/products?cat=baby-care') ?>">Baby Care</a>
      <a href="<?= app_url('/contact') ?>">Contact</a>
    </div>
  </nav>
</header>
