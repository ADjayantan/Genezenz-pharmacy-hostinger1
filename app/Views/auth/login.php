<?php declare(strict_types=1); $error=flash('error'); ?>
<section class="account-shell section-space"><div class="account-card">
  <p class="rule-label"><?= !empty($admin)?'Staff access':'Customer account' ?></p>
  <h1><?= !empty($admin)?'Admin sign in':'Welcome back' ?></h1>
  <p><?= !empty($admin)?'Restricted to authorised Genezenz staff.':'Track orders, manage prescriptions and check out faster.' ?></p>
  <?php if($error): ?><div class="alert alert--error" role="alert"><?= e($error) ?></div><?php endif; ?>
  <form class="stack-form" method="post" action="<?= !empty($admin)?app_url('/login'):app_url('/login') ?>">
    <?= csrf_field() ?><input type="hidden" name="next" value="<?= e($next??'/profile') ?>">
    <label><span>Email</span><input type="email" name="email" autocomplete="email" required></label>
    <label><span>Password</span><input type="password" name="password" autocomplete="current-password" required></label>
    <button class="button button--primary button--full" type="submit">Sign in securely</button>
  </form>
  <?php if(empty($admin)): ?><p class="account-switch">New here? <a href="<?= app_url('/register') ?>">Create an account</a></p><?php endif; ?>
</div></section>
