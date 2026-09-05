<?php declare(strict_types=1); $error=flash('error'); ?>
<section class="account-shell section-space"><div class="account-card">
 <p class="rule-label">Customer account</p><h1>Create your account</h1><p>Your orders and prescriptions stay private and accessible only after sign-in.</p>
 <?php if($error):?><div class="alert alert--error" role="alert"><?=e($error)?></div><?php endif;?>
 <form class="stack-form" method="post" action="<?=app_url('/register')?>"><?=csrf_field()?>
  <label><span>Full name</span><input name="name" value="<?=old('name')?>" autocomplete="name" required minlength="2" maxlength="80"></label>
  <label><span>Email</span><input type="email" name="email" value="<?=old('email')?>" autocomplete="email" required></label>
  <label><span>Phone</span><input type="tel" name="phone" value="<?=old('phone')?>" autocomplete="tel" inputmode="numeric" maxlength="15"></label>
  <label><span>Password</span><input type="password" name="password" autocomplete="new-password" minlength="10" required><small>10+ characters with letters and numbers</small></label>
  <button class="button button--primary button--full" type="submit">Create secure account</button>
 </form><p class="account-switch">Already registered? <a href="<?=app_url('/login')?>">Sign in</a></p>
</div></section>
