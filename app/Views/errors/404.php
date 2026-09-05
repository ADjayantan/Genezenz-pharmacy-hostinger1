<?php declare(strict_types=1); ?>
<section class="empty-page container">
  <p class="rule-label">Not found</p>
  <h1><?= e($title ?? 'Page not found') ?></h1>
  <p><?= e($description ?? 'The page you requested could not be found.') ?></p>
  <a class="button button--primary" href="<?= app_url('/products') ?>">Browse medicines</a>
</section>
