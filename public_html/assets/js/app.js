/* =====================================================================
   iTrend Solution — premium interactions (vanilla JS + jQuery available)
   Theme toggle · hero typewriter · world-map flight planes · AJAX forms
   ===================================================================== */
(function () {
  'use strict';
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------- 1. Hero typewriter ---------- */
  var tw = document.querySelector('.typewriter-pro');
  if (tw && !reduce) {
    var phrases = [
      'scales brands worldwide',
      'powers global commerce',
      'ships across 3 continents',
      'is engineered to last'
    ];
    var i = 0, typeSpeed = 70, eraseSpeed = 35, holdT = 1500, holdE = 350;
    var sleep = function (ms) { return new Promise(function (r) { setTimeout(r, ms); }); };
    (async function loop() {
      while (true) {
        var text = phrases[i];
        tw.textContent = '';
        for (var c = 0; c < text.length; c++) { tw.textContent += text[c]; await sleep(typeSpeed); }
        await sleep(holdT);
        for (var d = text.length; d > 0; d--) { tw.textContent = text.slice(0, d - 1); await sleep(eraseSpeed); }
        await sleep(holdE);
        i = (i + 1) % phrases.length;
      }
    })();
  } else if (tw) {
    tw.textContent = 'scales brands worldwide';
  }

  /* ---------- 3. Dotted-map shipping: ships glide along the routes ---------- */
  var svg = document.querySelector('.dotmap');
  if (svg && !reduce) {
    var routes = Array.prototype.slice.call(svg.querySelectorAll('.ship-route'));
    var svgNS = 'http://www.w3.org/2000/svg';
    var markers = svg.querySelector('.dotmap-markers');
    routes.forEach(function (path, idx) {
      var len = path.getTotalLength();
      var ship = document.createElementNS(svgNS, 'circle');
      ship.setAttribute('r', '0.62');
      ship.setAttribute('class', 'ship');
      (markers || svg).appendChild(ship);
      var dur = 4200 + idx * 220;
      var start = performance.now() + idx * 500;
      function step(now) {
        var t = ((now - start) % dur) / dur;
        if (t < 0) { requestAnimationFrame(step); return; }
        var p = path.getPointAtLength(t * len);
        ship.setAttribute('cx', p.x);
        ship.setAttribute('cy', p.y);
        ship.style.opacity = (t < 0.04 || t > 0.96) ? 0 : 1;
        requestAnimationFrame(step);
      }
      requestAnimationFrame(step);
    });
  }

  /* ---------- 4. Pre-fill career role when "Apply" clicked on a job card ---------- */
  document.querySelectorAll('.job-apply[data-role]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var role = btn.getAttribute('data-role');
      var select = document.querySelector('#careerModal select[name="role"]');
      if (select && role) {
        Array.prototype.forEach.call(select.options, function (opt) {
          if (opt.textContent.trim() === role) opt.selected = true;
        });
      }
    });
  });

  /* ---------- 4a. Smooth in-page scrolling + close mobile menu ---------- */
  var navCollapse = document.getElementById('mainNavbar');
  function closeMobileMenu() {
    if (navCollapse && navCollapse.classList.contains('show') && window.bootstrap) {
      var c = window.bootstrap.Collapse.getInstance(navCollapse) || new window.bootstrap.Collapse(navCollapse, { toggle: false });
      c.hide();
    }
  }
  // JS-animated smooth scroll (guaranteed glide, with fixed-navbar offset + easing)
  function smoothScrollTo(targetY, duration) {
    var startY = window.scrollY || window.pageYOffset;
    var dist = targetY - startY;
    var start = null;
    var ease = function (t) { return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2; };
    function step(ts) {
      if (start === null) start = ts;
      var p = Math.min((ts - start) / duration, 1);
      window.scrollTo(0, startY + dist * ease(p));
      if (p < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
  }
  // Snappy, distance-aware duration: short jumps feel instant, long jumps stay smooth.
  function glideDuration(dist) {
    var d = Math.min(Math.abs(dist) / 3, 480);
    return Math.max(260, d); // clamp 260–480ms
  }
  function navOffset() {
    var nav = document.getElementById('siteNav');
    return (nav ? nav.offsetHeight : 70) + 14;
  }
  // Safe selector lookup: ignores empty/"#"/invalid selectors instead of throwing.
  function safeSelect(sel) {
    if (!sel || sel.length < 2 || sel.charAt(0) !== '#') return null;
    try { return document.querySelector(sel); } catch (err) { return null; }
  }
  // Scroll to an in-page target and reflect the section in the URL (deep-linkable).
  function goToSection(hash, push) {
    var target = safeSelect(hash);
    if (!target) return false;
    var top = target.getBoundingClientRect().top + (window.scrollY || window.pageYOffset) - navOffset();
    if (reduce) { window.scrollTo(0, top); }
    else { smoothScrollTo(top, glideDuration(top - (window.scrollY || window.pageYOffset))); }
    if (push) {
      try { history.pushState(null, '', hash); } catch (err) { location.hash = hash; }
    }
    return true;
  }
  document.querySelectorAll('a[href^="#"], a[href*="/#"]').forEach(function (a) {
    a.addEventListener('click', function (e) {
      var raw = a.getAttribute('href') || '';
      var hash = raw.indexOf('#') >= 0 ? raw.slice(raw.indexOf('#')) : '';
      if (!safeSelect(hash)) return;
      e.preventDefault();
      closeMobileMenu();
      if (navCollapse && navCollapse.classList.contains('show')) {
        // mobile: let the menu collapse first, then recompute & glide
        setTimeout(function () { goToSection(hash, true); }, 130);
      } else {
        goToSection(hash, true);
      }
    });
  });
  // Deep link: if the URL already carries a section hash (e.g. arriving from another
  // page via "/#what-we-do"), glide to it once the layout has settled. We wait for the
  // full load — hero video, lazy images, fonts — because the target's final position
  // isn't known until then, and run a second correction pass to catch late AOS/lazy
  // shifts so we always land squarely on the requested section.
  if (safeSelect(location.hash)) {
    var deepHash = location.hash;
    var landOnDeepLink = function () { goToSection(deepHash, false); };
    var runDeepLink = function () {
      setTimeout(landOnDeepLink, 60);   // first glide once layout is (mostly) ready
      setTimeout(landOnDeepLink, 450);  // correction pass after images/AOS reflow
    };
    if (document.readyState === 'complete') { runDeepLink(); }
    else { window.addEventListener('load', runDeepLink); }
  }
  // Back / forward buttons re-sync the view to the URL's section.
  window.addEventListener('popstate', function () {
    goToSection(location.hash, false);
  });
  // Close the mobile menu when any nav link is tapped
  document.querySelectorAll('#mainNavbar .nav-link').forEach(function (l) {
    l.addEventListener('click', closeMobileMenu);
  });

  /* ---------- 4a. Scroll-spy: reflect the current section in the URL AND highlight the
     matching navbar link automatically as the visitor scrolls (Home active at the top). */
  (function () {
    // EVERY in-page section, in document order. Sections without a permanent nav link
    // (Who We Are, iTrend Glimpse, etc.) are shown/hidden dynamically in the navbar.
    var ids = ['home', 'trust', 'what-we-do', 'glimpse', 'about', 'awards', 'careers', 'get-started'];
    // Labels for sections that are NOT permanent navbar items — surfaced dynamically.
    var dynLabels = {
      'glimpse': 'iTrend Glimpse', 'about': 'Who We Are', 'get-started': 'Get Started'
    };
    var pageTop = (window.scrollY || window.pageYOffset);
    var sections = ids
      .map(function (id) { return document.getElementById(id); })
      .filter(Boolean)
      .sort(function (a, b) {
        return (a.getBoundingClientRect().top + pageTop) - (b.getBoundingClientRect().top + pageTop);
      });
    if (sections.length < 2) return; // not the landing page — nothing to spy on

    var navLinks = Array.prototype.slice.call(document.querySelectorAll('#mainNavbar .nav-link'));
    var dynItem = document.querySelector('#mainNavbar .nav-dynamic');
    var dynLink = dynItem ? dynItem.querySelector('.nav-link') : null;

    // A permanent navbar link whose href targets this section id (e.g. "#what-we-do").
    function linkForId(id) {
      var suffix = '#' + id;
      for (var i = 0; i < navLinks.length; i++) {
        if (navLinks[i] === dynLink) { continue; }
        var h = navLinks[i].getAttribute('href') || '';
        if (h === suffix || h.slice(-suffix.length) === suffix) { return navLinks[i]; }
      }
      return null;
    }
    function clearStatic() {
      navLinks.forEach(function (l) {
        if (l !== dynLink && l.getAttribute('href') && l.getAttribute('href').indexOf('#') !== -1) { l.classList.remove('active'); }
      });
    }
    function hideDyn() { if (dynItem) { dynItem.hidden = true; dynItem.classList.remove('show'); if (dynLink) { dynLink.classList.remove('active'); } } }

    // Slot the single dynamic navbar item into the correct place in the flow:
    // right after the nearest permanent in-page link that precedes this section,
    // so the pill reads left→right in scroll order (not stuck in a fixed spot).
    function positionDyn(id) {
      if (!dynItem) { return; }
      var idx = ids.indexOf(id);
      for (var i = idx; i >= 0; i--) {
        var l = linkForId(ids[i]);
        if (l && l.parentNode) {
          var li = l.parentNode; // the <li> wrapping the permanent link
          if (dynItem.previousElementSibling !== li) {
            li.parentNode.insertBefore(dynItem, li.nextSibling);
          }
          return;
        }
      }
    }

    var current = location.hash || '';
    var currentActiveId = null;
    var ticking = false;

    function setHash(hash) {
      if (hash === current) { return; }
      current = hash;
      try {
        history.replaceState(null, '', hash || (location.pathname + location.search));
      } catch (err) { /* ignore */ }
    }

    // Highlight the section in view. Permanent sections → highlight their link & hide the
    // dynamic slot. "Missing" sections → reveal the dynamic slot with that section's name
    // (and hide it again the moment you scroll into a different section).
    function setActive(id) {
      if (id === currentActiveId) { return; }
      currentActiveId = id;
      var link = linkForId(id);
      clearStatic();
      if (link) {
        link.classList.add('active');
        hideDyn();
      } else if (dynLabels[id] && dynItem && dynLink) {
        dynLink.textContent = dynLabels[id];
        dynLink.setAttribute('href', '#' + id);
        dynLink.classList.add('active');
        positionDyn(id);
        dynItem.hidden = false;
        requestAnimationFrame(function () { dynItem.classList.add('show'); });
      } else {
        hideDyn();
      }
    }

    function update() {
      ticking = false;
      var y = (window.scrollY || window.pageYOffset);
      // Near the very top → Home.
      if (y < 80) { setHash(''); setActive('home'); return; }
      // Bottom of the page → last section.
      if ((window.innerHeight + y) >= (document.body.scrollHeight - 4)) {
        var last = sections[sections.length - 1];
        setHash('#' + last.id); setActive(last.id);
        return;
      }
      var line = y + navOffset() + 12; // the reference line just under the fixed navbar
      var active = sections[0];
      for (var i = 0; i < sections.length; i++) {
        var top = sections[i].getBoundingClientRect().top + y;
        if (top <= line) { active = sections[i]; }
        else { break; }
      }
      setHash('#' + active.id);
      setActive(active.id);
    }

    window.addEventListener('scroll', function () {
      if (!ticking) { ticking = true; requestAnimationFrame(update); }
    }, { passive: true });
    update();
  })();

  /* ---------- 4a2. Auto-dismiss flash messages after 4 seconds ---------- */
  document.querySelectorAll('.flash-toast').forEach(function (el) {
    setTimeout(function () {
      el.classList.add('flash-hide');
      setTimeout(function () { if (el.parentNode) el.parentNode.removeChild(el); }, 450);
    }, 4000);
  });

  /* ---------- 4b. Back-to-top button ---------- */
  var toTop = document.getElementById('backToTop');
  if (toTop) {
    var onScrollTop = function () {
      if (window.scrollY > 600) toTop.classList.add('show');
      else toTop.classList.remove('show');
    };
    window.addEventListener('scroll', onScrollTop, { passive: true });
    onScrollTop();
    toTop.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' });
    });
  }

  /* ---------- 4c. Lightweight lazy reveal for any .lz elements ---------- */
  var lzEls = document.querySelectorAll('.lz');
  if (lzEls.length && 'IntersectionObserver' in window && !reduce) {
    var lzIO = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) { en.target.classList.add('in'); lzIO.unobserve(en.target); }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    lzEls.forEach(function (el) { lzIO.observe(el); });
  } else {
    lzEls.forEach(function (el) { el.classList.add('in'); });
  }

  /* ---------- 4d. Instant navigation: prefetch internal pages on hover ---------- */
  (function () {
    var prefetched = {};
    function prefetch(href) {
      if (!href || prefetched[href]) return;
      prefetched[href] = true;
      var l = document.createElement('link');
      l.rel = 'prefetch';
      l.href = href;
      document.head.appendChild(l);
    }
    function maybe(e) {
      var a = e.target.closest && e.target.closest('a[href]');
      if (!a) return;
      var href = a.getAttribute('href');
      // Only same-origin, real page paths (skip #anchors, mailto, downloads, external).
      if (!href || href[0] === '#' || a.target === '_blank' || a.hasAttribute('download')) return;
      if (a.host && a.host !== window.location.host) return;
      if (/\.(pdf|zip|jpg|jpeg|png|mp4)$/i.test(href)) return;
      prefetch(a.href);
    }
    document.addEventListener('mouseover', maybe, { passive: true });
    document.addEventListener('touchstart', maybe, { passive: true });
  })();

  /* ---------- 4f. Network-issue modal (shown on connection failure) ---------- */
  var netModal = (function () {
    var overlay = null;
    var lastRetry = null;
    function build() {
      if (overlay) return;
      overlay = document.createElement('div');
      overlay.className = 'net-modal-overlay';
      overlay.setAttribute('role', 'dialog');
      overlay.setAttribute('aria-modal', 'true');
      overlay.setAttribute('aria-label', 'Network connection problem');
      overlay.innerHTML =
        '<div class="net-modal" role="alertdialog" aria-live="assertive">' +
          '<div class="net-modal-icon"><span class="net-ring"></span><i class="bi bi-wifi-off"></i></div>' +
          '<h4>Connection lost</h4>' +
          '<p>We couldn’t reach our servers. Please check your internet connection and try again.</p>' +
          '<div class="net-modal-actions">' +
            '<button type="button" class="net-retry"><i class="bi bi-arrow-clockwise"></i> Retry</button>' +
            '<button type="button" class="net-dismiss">Dismiss</button>' +
          '</div>' +
          '<p class="net-modal-note"><i class="bi bi-shield-check"></i> Your details are safe — nothing was sent.</p>' +
        '</div>';
      document.body.appendChild(overlay);
      overlay.addEventListener('click', function (e) { if (e.target === overlay) hide(); });
      overlay.querySelector('.net-dismiss').addEventListener('click', hide);
      overlay.querySelector('.net-retry').addEventListener('click', function () {
        var fn = lastRetry; lastRetry = null; hide();
        if (navigator.onLine === false) { show(fn); return; } // still offline — keep it up
        if (typeof fn === 'function') fn();
      });
      document.addEventListener('keydown', function (e) { if (e.key === 'Escape') hide(); });
    }
    function show(retry) { build(); lastRetry = retry || null; overlay.classList.add('show'); }
    function hide() { if (overlay) overlay.classList.remove('show'); }
    return { show: show, hide: hide };
  })();

  // Connectivity watchdog.
  // navigator.onLine only knows whether the device has *a* network interface — it
  // stays `true` when the real problem is "server unreachable / DNS fails / request
  // times out / flaky Wi-Fi". So besides the offline/online events we actively probe
  // a tiny same-origin endpoint and surface the modal on genuine loss of reachability
  // (works the same on desktop and mobile). The modal clears the instant it recovers.
  (function () {
    var FAIL_LIMIT = 2;   // require two misses so a single blip doesn't nag
    var fails = 0, timer = null, inflight = false;

    function probe() {
      if (inflight) return;
      // If the device itself reports offline, that's already conclusive.
      if (navigator.onLine === false) { netModal.show(probe); return; }
      inflight = true;
      var done = false;
      var ctrl = (typeof AbortController !== 'undefined') ? new AbortController() : null;
      var to = setTimeout(function () { done = true; if (ctrl) ctrl.abort(); fail(); }, 7000);
      fetch('/health?ping=' + Date.now(), { method: 'GET', cache: 'no-store', signal: ctrl ? ctrl.signal : undefined })
        .then(function (r) {
          if (done) return; clearTimeout(to); inflight = false;
          if (r && r.ok) { fails = 0; netModal.hide(); } else { fail(); } // unreachable upstream still counts as a network problem
        })
        .catch(function () { if (done) return; clearTimeout(to); inflight = false; fail(); });
    }
    function fail() {
      inflight = false;
      if (++fails >= FAIL_LIMIT) netModal.show(probe);
    }
    function startBeat() { stopBeat(); timer = setInterval(function () { if (!document.hidden) probe(); }, 20000); }
    function stopBeat() { if (timer) { clearInterval(timer); timer = null; } }

    window.addEventListener('offline', function () { netModal.show(probe); });
    window.addEventListener('online', function () { fails = 0; probe(); });
    window.addEventListener('focus', probe);
    document.addEventListener('visibilitychange', function () { if (!document.hidden) probe(); });
    startBeat();
  })();

  /* ---------- 5. AJAX form submission (CSRF-aware, graceful) ---------- */
  function showError(form, msg) {
    var box = form.querySelector('.js-form-msg');
    if (!box) {
      box = document.createElement('p');
      box.className = 'js-form-msg';
      box.style.cssText = 'margin:.6rem 0 0;color:#ef4444;font-size:.86rem;font-weight:600;';
      form.appendChild(box);
    }
    box.textContent = msg;
  }

  function succeed(form) {
    var container = form.closest('.modal-content') || form.parentElement;
    var success = container && container.querySelector('.form-success');
    var modal = form.closest('.modal');

    if (success) {
      form.classList.add('hidden');
      success.classList.add('show');
      if (modal && window.bootstrap) {
        modal.addEventListener('hidden.bs.modal', function () {
          form.classList.remove('hidden');
          success.classList.remove('show');
          form.reset();
        }, { once: true });
      } else {
        setTimeout(function () { form.classList.remove('hidden'); success.classList.remove('show'); form.reset(); }, 6000);
      }
    } else {
      // footer newsletter (no success panel) — swap text
      var wrap = form.closest('.footer-newsletter');
      var text = wrap && wrap.querySelector('.newsletter-text');
      if (text) {
        var orig = text.innerHTML;
        text.innerHTML = '<h4 style="color:#16a34a">✓ Subscribed!</h4><p>Watch your inbox — the first edition lands soon.</p>';
        form.style.display = 'none';
        setTimeout(function () { text.innerHTML = orig; form.style.display = ''; form.reset(); }, 5000);
      } else {
        form.reset();
      }
    }
  }

  document.querySelectorAll('form.js-form').forEach(function (form) {
    var btn = form.querySelector('button[type="submit"]');
    var label = btn ? btn.innerHTML : '';

    function send() {
      var endpoint = form.getAttribute('data-endpoint') || form.getAttribute('action');
      if (!endpoint) return;

      // No connection at all → show the network modal up-front; Retry resends.
      if (navigator.onLine === false) { netModal.show(send); return; }

      if (btn) { btn.disabled = true; btn.innerHTML = '<span class="btn-spin"></span> Sending…'; }
      var data = new FormData(form);

      fetch(endpoint, {
        method: 'POST',
        body: data,
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        credentials: 'same-origin'
      })
        .then(function (r) { return r.json().catch(function () { return { ok: false, message: 'Unexpected response.' }; }); })
        .then(function (res) {
          // Refresh CSRF token for any subsequent submit.
          if (res && res.token) {
            form.querySelectorAll('input[name="_csrf"]').forEach(function (inp) { inp.value = res.token; });
          }
          if (res && res.ok) {
            succeed(form);
          } else {
            showError(form, (res && res.message) || 'Something went wrong. Please try again.');
          }
        })
        .catch(function () {
          // fetch only rejects on a genuine network/connection failure — surface the modal.
          netModal.show(send);
          showError(form, 'Network issue — please check your connection and try again.');
        })
        .finally(function () { if (btn) { btn.disabled = false; btn.innerHTML = label; } });
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      // Block submit if any mandatory field is empty — show the native prompt + a clear message.
      if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
        form.reportValidity();
        var firstInvalid = form.querySelector(':invalid');
        if (firstInvalid && firstInvalid.focus) { firstInvalid.focus(); }
        showError(form, 'Please fill in all required fields marked with *.');
        return;
      }
      send();
    });
  });
})();
