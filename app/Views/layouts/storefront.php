<?php

declare(strict_types=1);

$title = $title ?? ($site['name'] . ' — Online Pharmacy in Coimbatore');
$description = $description ?? $site['description'];
$canonical = $canonical ?? app_url('/');
$gaId = \App\Core\Env::get('GA_ID');
$gscVerification = \App\Core\Env::get('GSC_VERIFICATION');
$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'Pharmacy',
    'name' => $site['name'],
    'url' => app_url('/'),
    'telephone' => $site['phone'],
    'email' => $site['email'],
    'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => 'No. 6 & 7, Adhi Vinayagar Complex, Gopalsamy Temple Street',
        'addressLocality' => 'Ganapathy, Coimbatore',
        'addressRegion' => 'Tamil Nadu',
        'postalCode' => '641006',
        'addressCountry' => 'IN',
    ],
];
?>
<!doctype html>
<html lang="en-IN"<?= preg_match('/^G-[A-Z0-9]+$/', $gaId) ? ' data-ga-id="' . e($gaId) . '"' : '' ?>>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#F5F0E6">
  <meta name="description" content="<?= e($description) ?>">
  <?php if ($gscVerification !== ''): ?><meta name="google-site-verification" content="<?= e($gscVerification) ?>"><?php endif; ?>
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="<?= e($site['name']) ?>">
  <meta property="og:title" content="<?= e($title) ?>">
  <meta property="og:description" content="<?= e($description) ?>">
  <meta property="og:url" content="<?= e($canonical) ?>">
  <meta name="twitter:card" content="summary">
  <title><?= e($title) ?></title>
  <link rel="canonical" href="<?= e($canonical) ?>">
  <link rel="icon" href="<?= asset('images/logo.png') ?>" type="image/png">
  <link rel="preload" href="<?= asset('images/banners/banner-welcome-desktop.webp') ?>" as="image" type="image/webp" media="(min-width: 768px)">
  <link rel="preload" href="<?= asset('images/banners/banner-welcome-mobile.webp') ?>" as="image" type="image/webp" media="(max-width: 767px)">
  <link rel="stylesheet" href="<?= asset('css/site.css') ?>">
  <script type="application/ld+json" nonce="<?= csp_nonce() ?>"><?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
  <script type="module" src="<?= asset('js/app.js') ?>"></script>
</head>
<body>
  <a class="skip-link" href="#main">Skip to content</a>
  <?php require BASE_PATH . '/app/Views/partials/header.php'; ?>
  <main id="main"><?= $content ?></main>
  <?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>

  <a class="whatsapp-fab" href="https://wa.me/<?= e($site['whatsapp']) ?>?text=<?= rawurlencode('Hello Genezenz Pharmacy, I need help with a medicine.') ?>" target="_blank" rel="noopener noreferrer" aria-label="Chat with Genezenz Pharmacy on WhatsApp">
    <svg viewBox="0 0 24 24" width="23" height="23" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6A8.38 8.38 0 0 1 12.5 3h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
  </a>

  <button class="chat-toggle" type="button" aria-label="Open pharmacy assistant" aria-expanded="false" aria-controls="pharmacy-chat">
    <svg class="chat-icon-open" viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" aria-hidden="true"><path d="M21 12a8 8 0 0 1-8 8H7l-4 3 1.2-4.4A8 8 0 1 1 21 12Z"/></svg>
    <svg class="chat-icon-close" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
  </button>

  <section class="chat-panel" id="pharmacy-chat" role="dialog" aria-label="Pharmacy assistant" aria-hidden="true">
    <header class="chat-panel__head">
      <strong>Genezenz Assistant</strong>
      <span>Shop questions &amp; stock checks</span>
    </header>
    <div class="chat-messages" aria-live="polite">
      <div class="chat-message chat-message--bot">Hello. I can help with delivery, opening hours, offers, prescriptions, or checking whether we stock something. What do you need?</div>
      <div class="chat-actions"><a href="<?= app_url('/products') ?>">Browse the counter</a><a href="<?= app_url('/upload-prescription') ?>">Upload a prescription</a></div>
    </div>
    <form class="chat-form">
      <label class="sr-only" for="chat-input">Message</label>
      <input id="chat-input" name="message" maxlength="500" placeholder="Ask about delivery, stock…" autocomplete="off">
      <button type="submit">Send</button>
    </form>
  </section>

  <section class="cookie-banner" role="dialog" aria-label="Cookie choice" hidden>
    <p>We use essential storage for your cart. Analytics runs only after you accept.</p>
    <div><button type="button" data-cookie="essential">Essential only</button><button type="button" class="button button--primary" data-cookie="accepted">Accept analytics</button></div>
  </section>

  <div class="toast-region" aria-live="polite" aria-atomic="true"></div>
</body>
</html>
