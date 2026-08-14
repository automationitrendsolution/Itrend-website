/* =====================================================================
   iTrend Solution — UI behaviours for the redesign
   ---------------------------------------------------------------------
   Loaded after main.js / app.js. Owns:
     1. Conditional hero video (bandwidth + motion budget)
     2. Fail-safe scroll reveal (content is never permanently hidden)
     3. Horizontal-overflow assertion in development

   Vanilla JS, no dependencies.
   ===================================================================== */
(function () {
  'use strict';

  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ==================================================================
     1. HERO VIDEO — attach only where it is worth the bytes
     ================================================================== */
  (function heroVideo() {
    var video = document.querySelector('.hero-video[data-src]');
    if (!video) return;

    // Skip on small viewports, when motion is unwelcome, or when the
    // browser reports a metered / slow connection.
    var conn = navigator.connection || {};
    var slow = conn.saveData === true || /^(slow-)?2g$/.test(conn.effectiveType || '');
    if (reduceMotion || window.innerWidth < 992 || slow) return;

    var src = video.getAttribute('data-src');
    if (!src) return;

    var source = document.createElement('source');
    source.src = src;
    source.type = 'video/mp4';
    video.appendChild(source);
    video.load();
    // play() rejects if autoplay is blocked; the gradient ground remains.
    var p = video.play();
    if (p && typeof p.catch === 'function') { p.catch(function () {}); }
  })();

  /* ==================================================================
     2. SCROLL REVEAL — fail-safe
     ---------------------------------------------------------------
     AOS hides elements with opacity:0 until it decides to animate them.
     If it never initialises, or an element starts below the observer's
     trigger and the user never scrolls past it, the content stays
     invisible — a real content-loss risk, not just a visual one.

     This makes visibility the default and treats animation as the
     enhancement: anything still hidden after load is revealed outright.
     ================================================================== */
  (function reveal() {
    var els = document.querySelectorAll('.ds-reveal');

    if (!('IntersectionObserver' in window) || reduceMotion) {
      els.forEach(function (el) { el.classList.add('is-visible'); });
    } else {
      var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (en) {
          if (en.isIntersecting) {
            en.target.classList.add('is-visible');
            io.unobserve(en.target);
          }
        });
      }, { threshold: 0.08, rootMargin: '0px 0px -10% 0px' });
      els.forEach(function (el) { io.observe(el); });
    }

    /* Safety net for the legacy AOS elements.
       AOS sets opacity:0 on every [data-aos] node and only clears it when
       its own scroll handler decides the element is in view. Anything it
       misses — a failed script load, an element already scrolled past on
       arrival, a print or screenshot pass, an in-page anchor jump — stays
       permanently invisible. That is content loss, not a missed animation.

       An IntersectionObserver reveals them independently of AOS, and a
       final sweep guarantees nothing is left hidden. */
    var aosEls = document.querySelectorAll('[data-aos]');

    function show(el) { el.classList.add('aos-animate'); }

    if ('IntersectionObserver' in window && !reduceMotion) {
      var aosIO = new IntersectionObserver(function (entries) {
        entries.forEach(function (en) {
          if (en.isIntersecting) { show(en.target); aosIO.unobserve(en.target); }
        });
      }, { threshold: 0, rootMargin: '0px 0px -8% 0px' });
      aosEls.forEach(function (el) { aosIO.observe(el); });
    } else {
      aosEls.forEach(show);
    }

    // Reveal everything still hidden once the page has settled, and again
    // before printing, so no content can be permanently lost.
    function revealAll() { document.querySelectorAll('[data-aos]:not(.aos-animate)').forEach(show); }
    window.addEventListener('load', function () { setTimeout(revealAll, 1200); });
    window.addEventListener('beforeprint', revealAll);
  })();

  /* ==================================================================
     3. OVERFLOW ASSERTION (localhost only)
     Surfaces the exact element causing horizontal scroll, so overflow
     is caught during development rather than reported from production.
     ================================================================== */
  (function overflowCheck() {
    if (!/^(localhost|127\.0\.0\.1)$/.test(location.hostname)) return;
    window.addEventListener('load', function () {
      setTimeout(function () {
        var docW = document.documentElement.scrollWidth;
        var winW = window.innerWidth;
        if (docW <= winW) return;
        var culprits = [];
        document.querySelectorAll('body *').forEach(function (el) {
          var r = el.getBoundingClientRect();
          if (r.right > winW + 1 || r.left < -1) {
            culprits.push({
              el: el.tagName.toLowerCase() + (el.className ? '.' + String(el.className).split(' ')[0] : ''),
              right: Math.round(r.right), left: Math.round(r.left)
            });
          }
        });
        console.warn('[overflow] doc ' + docW + 'px > viewport ' + winW + 'px', culprits.slice(0, 12));
      }, 400);
    });
  })();
})();
