<?php declare(strict_types=1); ?>
<footer class="site-footer">
  <div class="container footer-grid">
    <section class="footer-brand">
      <a class="wordmark" href="<?= app_url('/') ?>">
        <img src="<?= asset('images/logo.png') ?>" width="30" height="30" alt="">
        <span>Genezenz <b>Pharmacy</b></span>
      </a>
      <p>Pharmacy serving Coimbatore since <?= e($site['founded']) ?>. Genuine medicines, pharmacist-verified prescriptions, delivered to your door.</p>
      <address>
        <a href="https://maps.google.com/?q=<?= rawurlencode('Genezenz Pharmacy Ganapathy Coimbatore') ?>" target="_blank" rel="noopener noreferrer"><?= e($site['address']) ?></a>
        <a href="tel:<?= e($site['phone']) ?>"><?= e($site['phone_display']) ?></a>
        <a href="mailto:<?= e($site['email']) ?>"><?= e($site['email']) ?></a>
        <span><?= e($site['hours']) ?></span>
      </address>
      <div class="social-links">
        <a href="<?= e($site['facebook']) ?>" target="_blank" rel="noopener noreferrer" aria-label="Facebook">Fb</a>
        <a href="https://wa.me/<?= e($site['whatsapp']) ?>" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp">Wa</a>
      </div>
    </section>

    <section><h2>Shop</h2><ul>
      <li><a href="<?= app_url('/products') ?>">All medicines</a></li>
      <li><a href="<?= app_url('/upload-prescription') ?>">Upload prescription</a></li>
      <li><a href="<?= app_url('/products?cat=vitamins') ?>">Vitamins</a></li>
      <li><a href="<?= app_url('/products?cat=baby-care') ?>">Baby care</a></li>
      <li><a href="<?= app_url('/about') ?>">About us</a></li>
    </ul></section>

    <section><h2>Policies</h2><ul>
      <li><a href="<?= app_url('/legal/privacy-policy') ?>">Privacy Policy</a></li>
      <li><a href="<?= app_url('/legal/cookie-policy') ?>">Cookie Policy</a></li>
      <li><a href="<?= app_url('/legal/terms-of-service') ?>">Terms of Service</a></li>
      <li><a href="<?= app_url('/legal/shipping-delivery') ?>">Shipping &amp; Delivery</a></li>
      <li><a href="<?= app_url('/legal/returns-refunds') ?>">Returns &amp; Refunds</a></li>
      <li><a href="<?= app_url('/legal/prescription-policy') ?>">Prescription Policy</a></li>
    </ul></section>

    <section><h2>We deliver to</h2><ul>
      <?php foreach ($site['service_areas'] as $area): ?>
        <li><a href="<?= app_url('/pharmacy-in-' . slugify($area) . '-coimbatore') ?>"><?= e($area) ?></a></li>
      <?php endforeach; ?>
    </ul></section>
  </div>

  <div class="footer-legal">
    <div class="container compliance-line">
      <span><?= e($site['compliance']['drug_licence']) ?></span>
      <span><?= e($site['compliance']['gstin']) ?></span>
      <span><?= e($site['compliance']['pharmacist']) ?><?= $site['compliance']['pharmacist_registration'] ? ' · ' . e($site['compliance']['pharmacist_registration']) : '' ?></span>
    </div>
    <p>© <?= date('Y') ?> Genezenz Pharmacy · Est. <?= e($site['founded']) ?>, Ganapathy, Coimbatore</p>
  </div>
</footer>
