<?php declare(strict_types=1); ?>
<section class="catalogue-hero"><div class="container"><p class="rule-label">Your basket</p><h1>Cart</h1><p>Prices and stock are verified securely again when you place the order.</p></div></section>
<section class="container section-space cart-page" data-cart-page>
 <div class="cart-lines" aria-live="polite"></div>
 <aside class="order-summary"><h2>Order summary</h2><dl><div><dt>Subtotal</dt><dd data-cart-subtotal>₹0.00</dd></div><div><dt>Delivery</dt><dd data-cart-delivery>₹0.00</dd></div><div class="summary-total"><dt>Total</dt><dd data-cart-total>₹0.00</dd></div></dl><p>Free local delivery on orders of ₹499 or more.</p><a class="button button--primary button--full" href="<?=app_url('/checkout')?>">Continue to checkout</a><a class="button button--outline button--full" href="<?=app_url('/products')?>">Continue shopping</a></aside>
</section>
