/* Genezenz Pharmacy — shared header + footer injector.
   Keeps navigation in ONE place. Set window.GZ_ROOT (e.g. "../../") and
   optionally window.GZ_ACTIVE before this script runs. Load it at the end of
   <body>, before products-data.js / main.js / cart.js. */
(function () {
  var R = window.GZ_ROOT || "";
  var A = window.GZ_ACTIVE || "";
  function cur(k) { return A === k ? ' aria-current="page"' : ""; }
  var wa = "https://wa.me/918044560873?text=Hi%20Genezenz%20Pharmacy%2C%20I%27d%20like%20to%20order%20medicines.";

  var header =
  '<div class="topbar"><div class="container-x"><span>Medicines 18% OFF</span><span>·</span><span>Surgicals 25% OFF</span><span>·</span><span>Diapers 40% OFF</span><span>·</span><span>Same-day delivery within 10km</span></div></div>' +
  '<header class="site-header"><div class="container-x"><div class="bar">' +
    '<a class="brand" href="' + R + '" aria-label="Genezenz Pharmacy home"><img class="brand__mark" src="' + R + 'assets/logo.svg" alt="" width="40" height="40"><span class="brand__name">Genezenz<small>Pharmacy · Coimbatore</small></span></a>' +
    '<div class="search"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg><input id="site-search" type="search" placeholder="Search medicines, salts, brands…" autocomplete="off" aria-label="Search medicines"><div id="search-results" class="search__results" hidden></div></div>' +
    '<div class="nav-actions"><a class="icon-btn" href="' + R + 'cart/" aria-label="Cart"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.6 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/></svg><span class="cart-count" data-count="0"></span></a>' +
    '<button class="icon-btn nav-toggle" aria-label="Menu" aria-expanded="false" aria-controls="mainnav"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12h18M3 6h18M3 18h18"/></svg></button></div>' +
  '</div></div>' +
  '<nav class="mainnav" id="mainnav" aria-label="Primary"><div class="container-x"><ul>' +
    '<li><a href="' + R + '"' + cur("home") + '>Home</a></li>' +
    '<li><a href="' + R + 'products/"' + cur("products") + '>All Medicines</a></li>' +
    '<li><a href="' + R + 'products/?cat=diabetes-care">Diabetes Care</a></li>' +
    '<li><a href="' + R + 'products/?cat=vitamins">Vitamins</a></li>' +
    '<li><a href="' + R + 'upload-prescription/"' + cur("upload") + '>Upload Prescription</a></li>' +
    '<li><a href="' + R + 'insurance/"' + cur("insurance") + '>Insurance</a></li>' +
    '<li><a href="' + R + 'about/"' + cur("about") + '>About</a></li>' +
    '<li><a href="' + R + 'contact/"' + cur("contact") + '>Contact</a></li>' +
  '</ul></div></nav></header>';

  var footer =
  '<footer class="site-footer"><div class="container-x"><div class="footer-grid">' +
    '<div class="footer-brand"><span class="brand__name" style="font-size:1.3rem">Genezenz Pharmacy</span><p>Your neighbourhood pharmacy in Ganapathy, Coimbatore — genuine medicines, verified by a licensed pharmacist, delivered the same day.</p></div>' +
    '<div><h4>Shop</h4><ul><li><a href="' + R + 'products/">All Medicines</a></li><li><a href="' + R + 'products/?cat=diabetes-care">Diabetes Care</a></li><li><a href="' + R + 'products/?cat=vitamins">Vitamins</a></li><li><a href="' + R + 'products/?cat=cold-fever">Cold &amp; Fever</a></li><li><a href="' + R + 'upload-prescription/">Upload Prescription</a></li></ul></div>' +
    '<div><h4>Areas we deliver</h4><ul><li><a href="' + R + 'pharmacy-in-ganapathy-coimbatore/">Ganapathy</a></li><li><a href="' + R + 'pharmacy-in-saibaba-colony-coimbatore/">Saibaba Colony</a></li><li><a href="' + R + 'pharmacy-in-rs-puram-coimbatore/">RS Puram</a></li><li><a href="' + R + 'pharmacy-in-peelamedu-coimbatore/">Peelamedu</a></li><li><a href="' + R + 'pharmacy-in-saravanampatti-coimbatore/">Saravanampatti</a></li></ul></div>' +
    '<div><h4>Contact</h4><ul><li><a href="tel:+918044560873">+91 80445 60873</a></li><li><a href="mailto:care@genezenz-pharmacy.in">care@genezenz-pharmacy.in</a></li><li>No. 6 &amp; 7, Adhi Vinayagar Complex, Gopalsamy Temple Street, Ganapathy, Coimbatore – 641006</li><li>Mon–Sat, 9:00 AM – 8:00 PM</li></ul></div>' +
  '</div><div class="footer-legal"><span>© <span class="yr">2026</span> Genezenz Pharmacy. All rights reserved.</span><span class="compliance">GSTIN: to be added · Drug Licence No.: to be added · Pharmacist-in-charge: to be added</span></div></div></footer>' +
  '<a class="wa-fab" href="' + wa + '" target="_blank" rel="noopener" aria-label="Chat on WhatsApp"><svg viewBox="0 0 32 32" fill="currentColor"><path d="M16 3C9 3 3.5 8.5 3.5 15.5c0 2.4.7 4.6 1.8 6.5L3 29l7.2-2.3c1.8 1 3.9 1.5 6 1.5 7 0 12.5-5.5 12.5-12.5S23 3 16 3zm0 22.8c-1.9 0-3.7-.5-5.3-1.5l-.4-.2-4.3 1.4 1.4-4.2-.3-.4a10.2 10.2 0 0 1-1.6-5.6c0-5.7 4.6-10.3 10.4-10.3S26.3 9.8 26.3 15.5 21.7 25.8 16 25.8zm5.7-7.7c-.3-.2-1.8-.9-2.1-1s-.5-.2-.7.2-.8 1-.9 1.2-.3.2-.6.1a8.4 8.4 0 0 1-2.5-1.5 9.3 9.3 0 0 1-1.7-2.1c-.2-.3 0-.5.1-.6l.5-.5.3-.5v-.5c0-.2-.7-1.7-1-2.3-.3-.6-.5-.5-.7-.5h-.6c-.2 0-.5.1-.8.4-.3.3-1 1-1 2.5s1.1 2.9 1.2 3.1 2.1 3.3 5.1 4.6c.7.3 1.3.5 1.7.6.7.2 1.4.2 1.9.1.6-.1 1.8-.7 2-1.4.3-.7.3-1.3.2-1.4l-.6-.3z"/></svg></a>';

  var h = document.getElementById("gz-header"); if (h) h.outerHTML = header;
  var f = document.getElementById("gz-footer"); if (f) f.outerHTML = footer;
  Array.prototype.forEach.call(document.querySelectorAll(".yr"), function (el) { el.textContent = new Date().getFullYear(); });
})();
