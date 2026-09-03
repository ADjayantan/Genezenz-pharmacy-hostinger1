/* Genezenz Pharmacy — client-side cart (localStorage) + WhatsApp checkout.
   No backend needed: checkout hands the order to the pharmacy over WhatsApp,
   exactly the lead flow a single-branch pharmacy actually runs on. */
(function () {
  "use strict";
  var KEY = "gz_cart_v1";
  var WA = "918044560873";              // pharmacy WhatsApp (from site config)
  var FREE_ABOVE = 499;
  var DELIVERY_FEE = 40;
  var rupee = function (n) { return "₹" + Number(n).toLocaleString("en-IN"); };

  function read() { try { return JSON.parse(localStorage.getItem(KEY)) || []; } catch (e) { return []; } }
  function write(c) { try { localStorage.setItem(KEY, JSON.stringify(c)); } catch (e) {} updateCount(); }

  function add(item, qty) {
    var cart = read();
    var row = cart.find(function (r) { return r.slug === item.slug; });
    if (row) row.qty += qty; else cart.push({ slug: item.slug, name: item.name, price: item.price, rx: !!item.rx, qty: qty });
    write(cart);
  }
  function setQty(slug, qty) {
    var cart = read();
    var row = cart.find(function (r) { return r.slug === slug; });
    if (row) { row.qty = qty; if (row.qty < 1) cart = cart.filter(function (r) { return r.slug !== slug; }); }
    write(cart); renderCartPage();
  }
  function remove(slug) { write(read().filter(function (r) { return r.slug !== slug; })); renderCartPage(); }
  function count() { return read().reduce(function (n, r) { return n + r.qty; }, 0); }
  function subtotal() { return read().reduce(function (s, r) { return s + r.price * r.qty; }, 0); }

  function updateCount() {
    var n = count();
    document.querySelectorAll(".cart-count").forEach(function (el) { el.textContent = n; el.dataset.count = n; });
  }

  /* Toast */
  function toast(msg) {
    var wrap = document.querySelector(".toast-wrap");
    if (!wrap) { wrap = document.createElement("div"); wrap.className = "toast-wrap"; document.body.appendChild(wrap); }
    var t = document.createElement("div"); t.className = "toast"; t.textContent = msg; wrap.appendChild(t);
    setTimeout(function () { t.remove(); }, 2200);
  }

  /* Add-to-cart buttons: <button data-add data-slug data-name data-price data-rx> */
  document.addEventListener("click", function (e) {
    var btn = e.target.closest("[data-add]");
    if (!btn) return;
    e.preventDefault();
    var qtyInput = btn.closest("form, .pdp, body") && document.getElementById("pdp-qty");
    var qty = qtyInput ? Math.max(1, parseInt(qtyInput.value, 10) || 1) : 1;
    add({ slug: btn.dataset.slug, name: btn.dataset.name, price: Number(btn.dataset.price), rx: btn.dataset.rx === "true" }, qty);
    toast(btn.dataset.name.split("(")[0].trim() + " added to cart");
  });

  /* Quantity steppers on product page */
  document.addEventListener("click", function (e) {
    var q = e.target.closest("[data-qty]");
    if (!q) return;
    var inp = document.getElementById("pdp-qty");
    if (!inp) return;
    var v = parseInt(inp.value, 10) || 1;
    inp.value = Math.max(1, q.dataset.qty === "up" ? v + 1 : v - 1);
  });

  /* Cart page rendering */
  function renderCartPage() {
    var host = document.getElementById("cart-body");
    if (!host) return;
    var cart = read();
    var summary = document.getElementById("cart-summary");
    if (!cart.length) {
      host.innerHTML = '<div class="empty-state"><p style="font-size:1.1rem;margin-bottom:.6rem">Your cart is empty.</p><p style="margin-bottom:1.4rem">Browse the counter and add what you need.</p><a class="btn" href="../products/">Browse medicines</a></div>';
      if (summary) summary.style.display = "none";
      return;
    }
    if (summary) summary.style.display = "";
    var icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="7" y="3" width="10" height="18" rx="3"/><path d="M7 12h10"/></svg>';
    host.innerHTML = cart.map(function (r) {
      return '<div class="cart-row">' +
        '<div class="thumb">' + icon + '</div>' +
        '<div><a href="../products/' + r.slug + '/" style="font-family:var(--font-display);font-weight:500">' + r.name + '</a>' +
        (r.rx ? ' <span class="badge-soft" style="background:var(--plum-wash);color:var(--plum)">Rx required</span>' : '') +
        '<div class="mono" style="font-size:.8rem;color:var(--ink-soft);margin-top:.2rem">' + rupee(r.price) + ' each</div>' +
        '<div class="qty" style="margin-top:.5rem"><button type="button" aria-label="Decrease" onclick="GZCart.setQty(\'' + r.slug + '\',' + (r.qty - 1) + ')">−</button>' +
        '<input value="' + r.qty + '" readonly aria-label="Quantity"><button type="button" aria-label="Increase" onclick="GZCart.setQty(\'' + r.slug + '\',' + (r.qty + 1) + ')">+</button></div></div>' +
        '<div style="text-align:right"><div class="mono" style="font-size:1.05rem;color:var(--green)">' + rupee(r.price * r.qty) + '</div>' +
        '<button type="button" onclick="GZCart.remove(\'' + r.slug + '\')" style="background:none;border:0;color:var(--out);font-size:.78rem;cursor:pointer;margin-top:.4rem">Remove</button></div>' +
        '</div>';
    }).join("");

    var sub = subtotal();
    var delivery = sub >= FREE_ABOVE ? 0 : DELIVERY_FEE;
    var total = sub + delivery;
    var hasRx = cart.some(function (r) { return r.rx; });
    summary.innerHTML =
      '<h3 style="font-family:var(--font-display);font-size:1.2rem;margin-bottom:.8rem">Order summary</h3>' +
      '<div class="line"><span>Subtotal</span><span class="mono">' + rupee(sub) + '</span></div>' +
      '<div class="line"><span>Delivery' + (delivery === 0 ? ' (free over ' + rupee(FREE_ABOVE) + ')' : '') + '</span><span class="mono">' + (delivery === 0 ? "Free" : rupee(delivery)) + '</span></div>' +
      '<div class="line total"><span>Total</span><span class="mono">' + rupee(total) + '</span></div>' +
      (hasRx ? '<div class="rx-note" style="margin:.9rem 0">This order includes prescription medicines. Our pharmacist will ask for your prescription before dispatch.</div>' : '') +
      '<button type="button" class="btn btn--block" style="margin-top:1rem" onclick="GZCart.checkout()">Confirm order on WhatsApp</button>' +
      '<p class="mono" style="font-size:.7rem;color:var(--ink-soft);text-align:center;margin-top:.7rem">You review the order in WhatsApp before it is placed.</p>';
  }

  function checkout() {
    var cart = read();
    if (!cart.length) return;
    var lines = cart.map(function (r) { return "• " + r.name + " × " + r.qty + " = " + rupee(r.price * r.qty); });
    var sub = subtotal();
    var delivery = sub >= FREE_ABOVE ? 0 : DELIVERY_FEE;
    var msg = "Hello Genezenz Pharmacy, I would like to place an order:\n\n" + lines.join("\n") +
      "\n\nSubtotal: " + rupee(sub) + "\nDelivery: " + (delivery === 0 ? "Free" : rupee(delivery)) +
      "\nTotal: " + rupee(sub + delivery) +
      (cart.some(function (r) { return r.rx; }) ? "\n\n(I have prescription items — I will share my prescription.)" : "") +
      "\n\nMy name / delivery area: ";
    window.open("https://wa.me/" + WA + "?text=" + encodeURIComponent(msg), "_blank");
  }

  window.GZCart = { add: add, setQty: setQty, remove: remove, checkout: checkout, count: count };
  updateCount();
  renderCartPage();
})();
