<?php declare(strict_types=1); ?>
<div class="search-box" data-search-box>
  <form action="<?= app_url('/products') ?>" method="get" role="search">
    <label class="sr-only" for="site-search-<?= isset($searchInstance) ? e($searchInstance) : 'default' ?>">Search products</label>
    <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
    <input id="site-search-<?= isset($searchInstance) ? e($searchInstance) : 'default' ?>" type="search" name="q" role="combobox" aria-controls="search-list-<?= isset($searchInstance) ? e($searchInstance) : 'default' ?>" minlength="2" maxlength="100" autocomplete="off" placeholder="Search medicines, vitamins, salt name…" aria-autocomplete="list" aria-expanded="false">
  </form>
  <ul id="search-list-<?= isset($searchInstance) ? e($searchInstance) : 'default' ?>" class="search-results" role="listbox" aria-label="Product suggestions" hidden></ul>
</div>
