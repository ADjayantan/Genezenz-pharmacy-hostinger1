/* Genezenz Pharmacy — storefront interactions (vanilla JS, no dependencies)
   Mobile nav · header search autocomplete · catalogue filter/sort/paginate */
(function () {
  "use strict";
  var rupee = function (n) { return "₹" + Number(n).toLocaleString("en-IN"); };

  /* ── Mobile nav toggle ─────────────────────────────────────── */
  var toggle = document.querySelector(".nav-toggle");
  var nav = document.querySelector(".mainnav");
  if (toggle && nav) {
    toggle.addEventListener("click", function () {
      var open = nav.classList.toggle("open");
      toggle.setAttribute("aria-expanded", open ? "true" : "false");
    });
  }

  /* ── Promo carousel (hero banner) ──────────────────────────── */
  var carousel = document.querySelector(".promo-carousel");
  if (carousel) {
    var slides = Array.prototype.slice.call(carousel.querySelectorAll(".promo-slide"));
    var dotsWrap = carousel.querySelector(".promo-dots");
    var idx = slides.findIndex(function (s) { return s.classList.contains("is-active"); });
    if (idx < 0) idx = 0;
    var timer = null, DELAY = 5000;

    slides.forEach(function (_, i) {
      var d = document.createElement("button");
      d.className = "promo-dot" + (i === idx ? " is-active" : "");
      d.type = "button";
      d.setAttribute("aria-label", "Go to slide " + (i + 1));
      d.addEventListener("click", function () { go(i); reset(); });
      dotsWrap.appendChild(d);
    });
    var dots = Array.prototype.slice.call(dotsWrap.children);

    function go(n) {
      slides[idx].classList.remove("is-active");
      dots[idx].classList.remove("is-active");
      idx = (n + slides.length) % slides.length;
      slides[idx].classList.add("is-active");
      dots[idx].classList.add("is-active");
    }
    function next() { go(idx + 1); }
    function prev() { go(idx - 1); }
    function reset() { clearInterval(timer); timer = setInterval(next, DELAY); }

    var nb = carousel.querySelector(".promo-nav--next");
    var pb = carousel.querySelector(".promo-nav--prev");
    if (nb) nb.addEventListener("click", function () { next(); reset(); });
    if (pb) pb.addEventListener("click", function () { prev(); reset(); });

    carousel.addEventListener("mouseenter", function () { clearInterval(timer); });
    carousel.addEventListener("mouseleave", reset);

    var x0 = null;
    carousel.addEventListener("touchstart", function (e) { x0 = e.touches[0].clientX; }, { passive: true });
    carousel.addEventListener("touchend", function (e) {
      if (x0 === null) return;
      var dx = e.changedTouches[0].clientX - x0;
      if (Math.abs(dx) > 40) { dx < 0 ? next() : prev(); reset(); }
      x0 = null;
    });

    reset();
  }

  /* ── Header search autocomplete ────────────────────────────── */
  var input = document.getElementById("site-search");
  var box = document.getElementById("search-results");
  var products = window.GZ_PRODUCTS || [];
  var ROOT = (window.GZ_ROOT || "");   // relative path back to site root, e.g. "../"

  function hideResults() { if (box) { box.innerHTML = ""; box.hidden = true; } }
  function renderResults(list, q) {
    if (!box) return;
    if (!list.length) {
      box.innerHTML = '<div style="padding:.7rem .9rem;font-size:.85rem;color:var(--ink-soft)">No medicines match &ldquo;' + escapeHtml(q) + '&rdquo;. Try the generic name, or upload a prescription.</div>';
      box.hidden = false; return;
    }
    box.innerHTML = list.map(function (p) {
      return '<a href="' + ROOT + 'products/' + p.slug + '/">' +
        '<span>' + escapeHtml(p.name) + (p.rx ? ' <span class="badge-soft" style="background:var(--plum-wash);color:var(--plum)">Rx</span>' : '') + '</span>' +
        '<span class="price">' + rupee(p.price) + '</span></a>';
    }).join("");
    box.hidden = false;
  }
  function escapeHtml(s) { return String(s).replace(/[&<>"']/g, function (c) { return ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" })[c]; }); }

  if (input && box) {
    input.addEventListener("input", function () {
      var q = input.value.trim().toLowerCase();
      if (q.length < 2) { hideResults(); return; }
      var hits = products.filter(function (p) {
        return p.name.toLowerCase().indexOf(q) > -1 || p.catName.toLowerCase().indexOf(q) > -1;
      }).slice(0, 7);
      renderResults(hits, q);
    });
    input.addEventListener("keydown", function (e) {
      if (e.key === "Enter") {
        e.preventDefault();
        var q = input.value.trim();
        if (q) window.location.href = ROOT + "products/?q=" + encodeURIComponent(q);
      }
      if (e.key === "Escape") hideResults();
    });
    document.addEventListener("click", function (e) {
      if (!box.contains(e.target) && e.target !== input) hideResults();
    });
  }

  /* ── Catalogue filter / sort / paginate ────────────────────── */
  var grid = document.getElementById("catalogue");
  if (grid) {
    var cards = Array.prototype.slice.call(grid.querySelectorAll(".pcard"));
    var catInputs = Array.prototype.slice.call(document.querySelectorAll('input[name="cat"]'));
    var sortSel = document.getElementById("sort");
    var countEl = document.getElementById("result-count");
    var searchParams = new URLSearchParams(window.location.search);
    var PAGE_SIZE = 12;
    var page = 1;

    // Pre-select category / query from URL
    var urlCat = searchParams.get("cat");
    if (urlCat) { catInputs.forEach(function (i) { if (i.value === urlCat) i.checked = true; }); }
    var urlQ = (searchParams.get("q") || "").toLowerCase();
    var qHeading = document.getElementById("cat-query");
    if (urlQ && qHeading) { qHeading.textContent = 'Results for “' + urlQ + '”'; }

    function activeCats() { return catInputs.filter(function (i) { return i.checked; }).map(function (i) { return i.value; }); }

    function apply() {
      var cats = activeCats();
      var visible = cards.filter(function (c) {
        var okCat = !cats.length || cats.indexOf(c.dataset.cat) > -1;
        var okQ = !urlQ || c.dataset.name.toLowerCase().indexOf(urlQ) > -1;
        return okCat && okQ;
      });

      var sort = sortSel ? sortSel.value : "popular";
      visible.sort(function (a, b) {
        if (sort === "price-asc") return a.dataset.price - b.dataset.price;
        if (sort === "price-desc") return b.dataset.price - a.dataset.price;
        if (sort === "name") return a.dataset.name.localeCompare(b.dataset.name);
        return b.dataset.stock - a.dataset.stock; // popular = stock desc
      });

      cards.forEach(function (c) { c.style.display = "none"; });
      var shown = visible.slice(0, page * PAGE_SIZE);
      shown.forEach(function (c) { c.style.display = ""; grid.appendChild(c); });

      if (countEl) countEl.textContent = visible.length + (visible.length === 1 ? " product" : " products");
      var moreBtn = document.getElementById("load-more");
      if (moreBtn) moreBtn.style.display = shown.length < visible.length ? "" : "none";
      var emptyEl = document.getElementById("cat-empty");
      if (emptyEl) emptyEl.style.display = visible.length ? "none" : "";
    }

    catInputs.forEach(function (i) { i.addEventListener("change", function () { page = 1; apply(); }); });
    if (sortSel) sortSel.addEventListener("change", function () { page = 1; apply(); });
    var moreBtn = document.getElementById("load-more");
    if (moreBtn) moreBtn.addEventListener("click", function () { page++; apply(); });
    apply();
  }

  /* ── Lead / contact forms → WhatsApp ───────────────────────── */
  var WA = "918044560873";
  Array.prototype.slice.call(document.querySelectorAll("form.lead-form")).forEach(function (form) {
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      var name = (form.querySelector('[name="name"]') || {}).value || "";
      var phone = (form.querySelector('[name="phone"]') || {}).value || "";
      var area = (form.querySelector('[name="area"]') || {}).value || "";
      var need = (form.querySelector('[name="need"]') || form.querySelector('[name="message"]') || {}).value || "";
      var src = form.dataset.source || "website";
      var msg = "Hello Genezenz Pharmacy!\n\nName: " + name +
        "\nPhone: " + phone +
        (area ? "\nArea: " + area : "") +
        (need ? "\nI need: " + need : "") +
        "\n\n(Sent from " + src + ")";
      window.open("https://wa.me/" + WA + "?text=" + encodeURIComponent(msg), "_blank");
      var ok = form.querySelector(".form-ok");
      if (ok) { ok.hidden = false; form.reset(); }
    });
  });

  /* ── Prescription upload preview (client-side only) ────────── */
  var drop = document.getElementById("rx-drop");
  var fileInput = document.getElementById("rx-file");
  var fileName = document.getElementById("rx-file-name");
  if (drop && fileInput) {
    ["dragover", "dragenter"].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.add("drag"); }); });
    ["dragleave", "drop"].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.remove("drag"); }); });
    drop.addEventListener("drop", function (e) { if (e.dataTransfer.files.length) { fileInput.files = e.dataTransfer.files; show(); } });
    fileInput.addEventListener("change", show);
    function show() { if (fileName && fileInput.files.length) fileName.textContent = fileInput.files[0].name; }
  }
})();
