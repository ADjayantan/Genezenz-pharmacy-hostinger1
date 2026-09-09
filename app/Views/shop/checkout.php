<?php declare(strict_types=1); ?>
<section class="catalogue-hero"><div class="container"><p class="rule-label">Secure checkout</p><h1>Delivery details</h1><p>Cash on delivery. Prescription products remain subject to pharmacist approval.</p></div></section>
<section class="container section-space checkout-layout" data-checkout>
 <form class="stack-form checkout-form"><?=csrf_field()?>
  <div class="field-grid"><label><span>Name</span><input name="name" autocomplete="name" value="<?=e($user['name']??'')?>" required></label><label><span>Phone</span><input name="phone" autocomplete="tel" inputmode="numeric" value="<?=e($user['phone']??'')?>" required></label></div>
  <label><span>Delivery address</span><textarea name="address" rows="4" autocomplete="street-address" required><?=e($user['address']??'')?></textarea></label>
  <label><span>Pincode</span><input name="pincode" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required></label>
  <label><span>Order notes <small>optional</small></span><textarea name="notes" rows="3" maxlength="1000"></textarea></label>
  <fieldset class="checkout-prescription">
   <legend>Prescription</legend>
   <p>Required for prescription-only medicines. Upload a new file or choose an unused prescription from your account.</p>
   <label><span>Choose prescription</span><select name="prescription_id"><option value="">No prescription selected</option><?php foreach ($prescriptions as $rx): ?><option value="<?= (int)$rx['id'] ?>"><?= e(($rx['original_name'] ?: 'Prescription').' — '.$rx['status']) ?></option><?php endforeach; ?></select></label>
   <label><span>Or upload a new prescription (maximum 8 MB)</span><input type="file" data-checkout-rx accept="image/jpeg,image/png,image/webp,image/heic,image/heif,application/pdf"></label>
   <p>New uploads remain pending until reviewed by the pharmacy.</p>
  </fieldset>
  <div class="alert">Payment: Cash on delivery. The pharmacy will confirm Rx items before dispatch.</div>
  <p class="form-status" role="alert"></p><button class="button button--primary button--full" type="submit">Place verified order</button>
 </form>
 <aside class="order-summary"><h2>Cart review</h2><div data-checkout-lines></div><dl><div class="summary-total"><dt>Estimated total</dt><dd data-checkout-total>₹0.00</dd></div></dl></aside>
</section>
