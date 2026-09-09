const CART_KEY = 'genezenz-cart-v1';
const COOKIE_KEY = 'genezenz-cookie-choice';

function readCart() {
  try {
    const parsed = JSON.parse(localStorage.getItem(CART_KEY) || '[]');
    return Array.isArray(parsed) ? parsed : [];
  } catch {
    return [];
  }
}

function writeCart(lines) {
  try {
    localStorage.setItem(CART_KEY, JSON.stringify(lines));
  } catch {
    showToast('Your browser could not save the cart.');
  }
  updateCartCount(lines);
}

function updateCartCount(lines = readCart()) {
  const count = lines.reduce((total, line) => total + Number(line.qty || 0), 0);
  document.querySelectorAll('.cart-count').forEach((badge) => {
    badge.textContent = String(count);
    badge.setAttribute('aria-label', `${count} ${count === 1 ? 'item' : 'items'}`);
  });
}

function showToast(message) {
  const region = document.querySelector('.toast-region');
  if (!region) return;
  const toast = document.createElement('div');
  toast.className = 'toast';
  toast.textContent = message;
  region.append(toast);
  window.setTimeout(() => toast.remove(), 3200);
}

function setupCart() {
  updateCartCount();
  document.addEventListener('click', (event) => {
    const button = event.target.closest('.add-to-cart');
    if (!button || button.disabled) return;

    let product;
    try {
      product = JSON.parse(button.dataset.product || '{}');
    } catch {
      showToast('This product could not be added.');
      return;
    }

    const lines = readCart();
    const existing = lines.find((line) => String(line.id) === String(product.id));
    if (existing) {
      existing.qty = Math.min(Number(existing.qty || 0) + 1, Number(product.stock || 99));
    } else {
      lines.push({ ...product, qty: 1 });
    }
    writeCart(lines);
    showToast(`${product.name} added to cart`);

    const original = button.textContent;
    button.textContent = 'Added';
    window.setTimeout(() => { button.textContent = original; }, 1000);
  });
}

function setupCarousel() {
  const carousel = document.querySelector('[data-carousel]');
  if (!carousel) return;
  const track = carousel.querySelector('.promo-track');
  const slides = [...carousel.querySelectorAll('.promo-slide')];
  const dots = [...carousel.querySelectorAll('[data-slide]')];
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  let index = 0;
  let timer;
  let paused = reduceMotion;
  const pauseButton = carousel.querySelector('[data-carousel-pause]');

  const go = (next, smooth = true) => {
    index = (next + slides.length) % slides.length;
    track.scrollTo({ left: track.clientWidth * index, behavior: smooth && !reduceMotion ? 'smooth' : 'auto' });
    dots.forEach((dot, dotIndex) => dot.setAttribute('aria-current', dotIndex === index ? 'true' : 'false'));
  };
  const restart = () => {
    window.clearInterval(timer);
    if (!paused && !reduceMotion && !document.hidden && !carousel.contains(document.activeElement) && !carousel.matches(':hover')) timer = window.setInterval(() => go(index + 1), 5000);
  };

  pauseButton?.addEventListener('click', () => {
    paused = !paused;
    pauseButton.textContent = paused ? '▶' : 'Ⅱ';
    pauseButton.setAttribute('aria-pressed', String(paused));
    pauseButton.setAttribute('aria-label', paused ? 'Resume automatic promotions' : 'Pause automatic promotions');
    restart();
  });
  pauseButton?.setAttribute('aria-pressed', String(paused));
  if (reduceMotion && pauseButton) {
    pauseButton.disabled = true;
    pauseButton.setAttribute('aria-label', 'Automatic promotions disabled by reduced motion preference');
  }
  carousel.addEventListener('focusin', () => window.clearInterval(timer));
  carousel.addEventListener('focusout', () => window.setTimeout(restart, 0));
  document.addEventListener('visibilitychange', restart);

  dots.forEach((dot) => dot.addEventListener('click', () => { go(Number(dot.dataset.slide)); restart(); }));
  track.addEventListener('scrollend', () => {
    if (!track.clientWidth) return;
    index = Math.round(track.scrollLeft / track.clientWidth);
    dots.forEach((dot, dotIndex) => dot.setAttribute('aria-current', dotIndex === index ? 'true' : 'false'));
  });
  carousel.addEventListener('pointerenter', () => window.clearInterval(timer));
  carousel.addEventListener('pointerleave', restart);
  window.addEventListener('resize', () => go(index, false));
  restart();
}

function escapeHtml(value) {
  return String(value)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

function setupSearchBox(box) {
  const input = box.querySelector('input[type="search"]');
  const results = box.querySelector('.search-results');
  if (!input || !results) return;
  let timeout;
  let controller;
  let active = -1;

  const close = () => {
    results.hidden = true;
    input.setAttribute('aria-expanded', 'false');
    input.removeAttribute('aria-activedescendant');
    active = -1;
  };
  const select = (next) => {
    const options = [...results.querySelectorAll('a')];
    active = Math.max(-1, Math.min(next, options.length - 1));
    options.forEach((option, index) => option.setAttribute('aria-selected', index === active ? 'true' : 'false'));
    if (active >= 0) input.setAttribute('aria-activedescendant', options[active].id);
    else input.removeAttribute('aria-activedescendant');
    if (active >= 0) options[active].scrollIntoView({ block: 'nearest' });
  };

  input.addEventListener('input', () => {
    window.clearTimeout(timeout);
    controller?.abort();
    const query = input.value.trim();
    if (query.length < 2) return close();

    timeout = window.setTimeout(async () => {
      controller = new AbortController();
      try {
        const response = await fetch(`/api/search?q=${encodeURIComponent(query)}`, {
          headers: { Accept: 'application/json' }, signal: controller.signal,
        });
        if (!response.ok) throw new Error('Search failed');
        const data = await response.json();
        const matches = Array.isArray(data.matches) ? data.matches : [];
        if (!matches.length) return close();

        const related = Array.isArray(data.related) ? data.related : [];
        results.innerHTML = [...matches, ...related].map((item, index) => `
          <li role="presentation"><a role="option" id="${input.id}-option-${index}" href="/products/${encodeURIComponent(item.slug)}" aria-selected="false">
            <span>${index >= matches.length ? '<small>Related product</small>' : ''}<strong>${escapeHtml(item.name)}${item.rxRequired ? ' <small>℞ Prescription</small>' : ''}</strong><small>${escapeHtml(item.brand || '')}</small></span>
            <b>₹${Number(item.price).toFixed(2)}</b>
          </a></li>`).join('') + `
          <li role="presentation"><a role="option" id="${input.id}-all" class="search-all" href="/products?q=${encodeURIComponent(query)}">See all results for “${escapeHtml(query)}” →</a></li>`;
        results.hidden = false;
        input.setAttribute('aria-expanded', 'true');
        active = -1;
      } catch (error) {
        if (error.name !== 'AbortError') close();
      }
    }, 350);
  });

  input.addEventListener('keydown', (event) => {
    const options = [...results.querySelectorAll('a')];
    if (results.hidden) return;
    if (event.key === 'ArrowDown') { event.preventDefault(); select(active + 1); }
    if (event.key === 'ArrowUp') { event.preventDefault(); select(active - 1); }
    if (event.key === 'Escape') close();
    if (event.key === 'Enter' && active >= 0 && options[active]) {
      event.preventDefault();
      window.location.assign(options[active].href);
    }
  });
  document.addEventListener('pointerdown', (event) => { if (!box.contains(event.target)) close(); });
}

function setupSearch() {
  document.querySelectorAll('[data-search-box]').forEach(setupSearchBox);
}

function setupLeadForms() {
  document.querySelectorAll('[data-lead-form]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      const button = form.querySelector('button[type="submit"]');
      const status = form.querySelector('.form-status');
      const original = button.textContent;
      button.disabled = true;
      button.textContent = 'Sending…';
      status.textContent = '';

      try {
        const response = await fetch(form.action, {
          method: 'POST', body: new FormData(form), headers: { Accept: 'application/json' },
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(data.message || 'Unable to send your request.');
        form.innerHTML = `<div class="lead-success" role="status"><strong>Request received</strong><p>${escapeHtml(data.message || 'Our pharmacist will call you shortly.')}</p></div>`;
      } catch (error) {
        status.textContent = error.message || 'Please call the pharmacy instead.';
        button.disabled = false;
        button.textContent = original;
      }
    });
  });
}

function pharmacyReply(message) {
  const text = message.toLowerCase();
  const medicalAdvice = /(dosage|dose|interaction|side effect|what should i take|which medicine|treatment|pregnant|symptom|diagnos)/i;
  if (medicalAdvice.test(text)) {
    return 'I can’t recommend a medicine, dose, or interaction advice. Please call our pharmacist on +91 80445 60873 or speak with your doctor.';
  }
  if (/(deliver|shipping|today|area)/i.test(text)) return 'We deliver within 10km of Ganapathy. Orders placed before 2 PM are usually dispatched the same day.';
  if (/(open|hours|time|close)/i.test(text)) return 'The counter is open Monday to Saturday, 9 AM to 8 PM.';
  if (/(offer|discount|free delivery)/i.test(text)) return 'Delivery is free above ₹499. Current storefront offers are shown at the top of the page.';
  if (/(prescription|upload|rx)/i.test(text)) return 'Upload a clear photo or PDF. A pharmacist reviews it before any prescription medicine is dispatched.';
  return 'I can help with delivery, hours, offers, prescriptions, or stock. For medicine advice, please speak directly with our pharmacist.';
}

function setupChat() {
  const toggle = document.querySelector('.chat-toggle');
  const panel = document.querySelector('.chat-panel');
  const form = document.querySelector('.chat-form');
  const messages = document.querySelector('.chat-messages');
  if (!toggle || !panel || !form || !messages) return;

  const setOpen = (open) => {
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    toggle.setAttribute('aria-label', open ? 'Close pharmacy assistant' : 'Open pharmacy assistant');
    panel.setAttribute('aria-hidden', open ? 'false' : 'true');
    if (open) form.elements.message.focus();
  };
  toggle.addEventListener('click', () => setOpen(toggle.getAttribute('aria-expanded') !== 'true'));
  form.addEventListener('submit', (event) => {
    event.preventDefault();
    const input = form.elements.message;
    const message = input.value.trim();
    if (!message) return;
    const user = document.createElement('div');
    user.className = 'chat-message chat-message--user';
    user.textContent = message;
    messages.append(user);
    input.value = '';

    window.setTimeout(() => {
      const bot = document.createElement('div');
      bot.className = 'chat-message chat-message--bot';
      bot.textContent = pharmacyReply(message);
      messages.append(bot);
      messages.scrollTop = messages.scrollHeight;
    }, 260);
  });
}

function setupCookies() {
  const banner = document.querySelector('.cookie-banner');
  if (!banner) return;
  let choice = null;
  try { choice = localStorage.getItem(COOKIE_KEY); } catch {}
  const initAnalytics = () => {
    const id = document.documentElement.dataset.gaId;
    if (!/^G-[A-Z0-9]+$/.test(id || '') || document.querySelector('script[data-genezenz-ga]')) return;
    window.dataLayer = window.dataLayer || [];
    window.gtag = function gtag(){ window.dataLayer.push(arguments); };
    window.gtag('js', new Date()); window.gtag('config', id, { anonymize_ip: true });
    const script = document.createElement('script'); script.async = true; script.dataset.genezenzGa = 'true'; script.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(id)}`; document.head.append(script);
  };
  if (!choice) banner.hidden = false;
  if (choice === 'accepted') initAnalytics();
  banner.querySelectorAll('[data-cookie]').forEach((button) => button.addEventListener('click', () => {
    try { localStorage.setItem(COOKIE_KEY, button.dataset.cookie); } catch {}
    banner.hidden = true;
    if (button.dataset.cookie === 'accepted') initAnalytics();
  }));
}

function cartMoney(value) { return `₹${Number(value || 0).toFixed(2)}`; }

function calculateCart(lines) {
  const subtotal = lines.reduce((sum, line) => sum + Number(line.price || 0) * Number(line.qty || 0), 0);
  const delivery = lines.length && subtotal < 499 ? 40 : 0;
  return { subtotal, delivery, total: subtotal + delivery };
}

function setupCartPage() {
  const page = document.querySelector('[data-cart-page]');
  if (!page) return;
  const render = () => {
    const lines = readCart(); const values = calculateCart(lines); const container = page.querySelector('.cart-lines');
    if (!lines.length) container.innerHTML = '<div class="catalogue-empty"><h2>Your cart is empty</h2><p>Browse pharmacy essentials and add what you need.</p><a class="button button--primary" href="/products">Browse products</a></div>';
    else container.innerHTML = lines.map((line) => `<article class="cart-line"><div><strong>${escapeHtml(line.name)}</strong><span>${escapeHtml(line.brand || '')} · ${cartMoney(line.price)} each</span>${line.rx_required || line.rxRequired ? '<small>℞ Prescription verification required</small>' : ''}</div><div class="qty-control"><button type="button" data-qty="-1" data-id="${escapeHtml(line.id)}" aria-label="Decrease quantity">−</button><b>${Number(line.qty)}</b><button type="button" data-qty="1" data-id="${escapeHtml(line.id)}" aria-label="Increase quantity">+</button></div><button class="remove-line" type="button" data-remove="${escapeHtml(line.id)}">Remove</button></article>`).join('');
    page.querySelector('[data-cart-subtotal]').textContent = cartMoney(values.subtotal);
    page.querySelector('[data-cart-delivery]').textContent = values.delivery ? cartMoney(values.delivery) : 'Free';
    page.querySelector('[data-cart-total]').textContent = cartMoney(values.total);
  };
  page.addEventListener('click', (event) => {
    const qty = event.target.closest('[data-qty]'); const remove = event.target.closest('[data-remove]'); if (!qty && !remove) return;
    const lines = readCart(); const id = String(qty?.dataset.id || remove?.dataset.remove); const line = lines.find((item) => String(item.id) === id);
    if (remove) writeCart(lines.filter((item) => String(item.id) !== id));
    else if (line) { line.qty = Math.max(1, Math.min(Number(line.stock || 99), Number(line.qty) + Number(qty.dataset.qty))); writeCart(lines); }
    render();
  });
  render();
}

function setupCheckout() {
  const page = document.querySelector('[data-checkout]'); if (!page) return;
  const lines = readCart(); const total = calculateCart(lines).total;
  page.querySelector('[data-checkout-lines]').innerHTML = lines.length ? lines.map((line) => `<div class="summary-line"><span>${escapeHtml(line.name)} × ${Number(line.qty)}</span><b>${cartMoney(Number(line.price) * Number(line.qty))}</b></div>`).join('') : '<p>Your cart is empty.</p>';
  page.querySelector('[data-checkout-total]').textContent = cartMoney(total);
  const form = page.querySelector('form'); const button = form.querySelector('button[type="submit"]'); const status = form.querySelector('.form-status'); if (!lines.length) button.disabled = true;
  form.addEventListener('submit', async (event) => {
    event.preventDefault(); button.disabled = true; button.textContent = 'Placing order…'; status.textContent = '';
    const data = Object.fromEntries(new FormData(form)); data.items = readCart().map((line) => ({ id: Number(line.id), qty: Number(line.qty) }));
    try {
      const fileInput = form.querySelector('[data-checkout-rx]');
      const file = fileInput?.files?.[0];
      if (file) {
        if (file.size > 8 * 1024 * 1024) throw new Error('Prescription must be 8 MB or smaller.');
        button.textContent = 'Uploading prescription…';
        const upload = new FormData();
        upload.set('_token', data._token);
        upload.set('prescription', file);
        const uploaded = await fetch('/api/prescriptions/upload', {method: 'POST', body: upload, headers: {Accept: 'application/json'}});
        const rx = await uploaded.json();
        if (!uploaded.ok) throw new Error(rx.message || 'Prescription upload failed.');
        data.prescription_id = String(rx.id);
        const select = form.elements.prescription_id;
        select.add(new Option(file.name + ' — PENDING', String(rx.id), true, true));
        fileInput.value = '';
      }
      if (readCart().some(line => line.rxRequired || line.rx_required) && !data.prescription_id) throw new Error('Choose or upload a prescription for your prescription-only items.');
      button.textContent = 'Placing order…';
      const response = await fetch('/api/orders', { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json' }, body: JSON.stringify(data) }); const result = await response.json(); if (!response.ok) throw new Error(result.message || 'Order could not be placed.'); writeCart([]); window.location.assign(result.redirect);
    }
    catch (error) { status.textContent = error.message; button.disabled = false; button.textContent = 'Place verified order'; }
  });
}

function setupAdminActions() {
  document.querySelectorAll('[data-confirm]').forEach((form) => form.addEventListener('submit', (event) => { if (!window.confirm(form.dataset.confirm)) event.preventDefault(); }));
  document.querySelector('.invoice button')?.addEventListener('click', () => window.print());
}

setupCart();
setupCarousel();
setupSearch();
setupLeadForms();
setupChat();
setupCookies();
setupCartPage();
setupCheckout();
setupAdminActions();
