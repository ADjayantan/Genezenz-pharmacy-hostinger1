<?php declare(strict_types=1); ?>
<div class="invoice">
  <header><div><p>Genezenz Pharmacy</p><h1>Tax invoice</h1></div><button class="button" type="button" data-print>Print</button></header>
  <dl><div><dt>Order</dt><dd><?=e($order['order_no'])?></dd></div><div><dt>Date</dt><dd><?=e($order['created_at'])?> UTC</dd></div><div><dt>Customer</dt><dd><?=e($order['name'])?> · <?=e($order['phone'])?></dd></div><div><dt>Delivery</dt><dd><?=e($order['address'])?> <?=e($order['pincode'])?></dd></div></dl>
  <table><thead><tr><th>Item</th><th>Qty</th><th>GST</th><th>Price</th><th>Total</th></tr></thead><tbody><?php foreach($order['items']as$item):?><tr><td><?=e($item['name'])?></td><td><?=e($item['quantity'])?></td><td><?=e($item['gst_rate']??5)?>%</td><td><?=money($item['price'])?></td><td><?=money((float)$item['price']*(int)$item['quantity'])?></td></tr><?php endforeach;?></tbody><tfoot><tr><th colspan="4">Order total</th><th><?=money($order['total'])?></th></tr></tfoot></table>
  <p>GST-inclusive line rates are snapshots from the order. Add verified GSTIN and drug-licence details in environment configuration before production invoicing.</p>
</div>
