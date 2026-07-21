(function () {
  'use strict';

  /* ============================================================
     0. AOS (Animate On Scroll) — init once, respects reduced motion
     ============================================================ */
  const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (window.AOS) {
    AOS.init({
      duration: 700,
      easing: 'ease-out-cubic',
      once: true,
      offset: 80,
      disable: prefersReduced,   // honour prefers-reduced-motion
    });
  }

  /* ============================================================
     1. Scroll-reveal: fade & slide up when entering the viewport
     ============================================================ */
  const revealEls = document.querySelectorAll('.reveal');

  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -60px 0px' });

    revealEls.forEach((el) => io.observe(el));
  } else {
    revealEls.forEach((el) => el.classList.add('is-visible'));
  }

  /* ============================================================
     2. Counter animation for stat numbers
     ============================================================ */
  const countEls = document.querySelectorAll('[data-count]');

  const formatNumber = (n) => {
    if (n >= 1000) return Math.floor(n).toLocaleString();
    return Math.floor(n).toString();
  };

  const animateCount = (el) => {
    const target = parseFloat(el.dataset.count);
    const suffix = el.dataset.suffix || '';
    const prefix = el.dataset.prefix || '';
    const duration = 1800;
    const start = performance.now();

    const step = (now) => {
      const elapsed = now - start;
      const p = Math.min(elapsed / duration, 1);
      // easeOutExpo
      const eased = p === 1 ? 1 : 1 - Math.pow(2, -10 * p);
      const value = target * eased;
      el.textContent = prefix + formatNumber(value) + suffix;
      if (p < 1) requestAnimationFrame(step);
      else el.textContent = prefix + formatNumber(target) + suffix;
    };

    requestAnimationFrame(step);
  };

  if ('IntersectionObserver' in window) {
    const co = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          animateCount(entry.target);
          co.unobserve(entry.target);
        }
      });
    }, { threshold: 0.4 });

    countEls.forEach((el) => co.observe(el));
  }


  /* ============================================================
     4. Navbar shadow / compact on scroll
     ============================================================ */
  const nav = document.querySelector('.floating-navbar');
  if (nav) {
    const onScroll = () => {
      if (window.scrollY > 40) nav.classList.add('scrolled');
      else nav.classList.remove('scrolled');
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  /* ============================================================
     5. Active nav-link set on CLICK only (no scroll-spy — the navbar
        does not change/animate while the user scrolls).
     ============================================================ */
  const navLinks = Array.from(document.querySelectorAll('.navbar-nav .nav-link'));
  navLinks.forEach((link) => {
    link.addEventListener('click', () => {
      navLinks.forEach((l) => l.classList.remove('active'));
      link.classList.add('active');
    });
  });

  /* ============================================================
     5.5 File upload — show filename + size, reset on form reset
     ============================================================ */
  const formatBytes = (b) => {
    if (b < 1024) return b + ' B';
    if (b < 1024 * 1024) return Math.round(b / 1024) + ' KB';
    return (b / (1024 * 1024)).toFixed(1) + ' MB';
  };

  document.querySelectorAll('.file-upload').forEach((label) => {
    const input = label.querySelector('input[type="file"]');
    const strong = label.querySelector('.file-upload-text strong');
    const small = label.querySelector('.file-upload-text small');
    const action = label.querySelector('.file-upload-action');
    if (!input || !strong || !small) return;

    const defaultStrong = strong.textContent;
    const defaultSmall = small.textContent;
    const defaultAction = action ? action.textContent : '';

    // A "clear" (×) button to remove the chosen file after selecting it.
    let clearBtn = label.querySelector('.file-upload-clear');
    if (!clearBtn) {
      clearBtn = document.createElement('button');
      clearBtn.type = 'button';
      clearBtn.className = 'file-upload-clear';
      clearBtn.setAttribute('aria-label', 'Remove file');
      clearBtn.innerHTML = '<i class="bi bi-x-lg"></i>';
      clearBtn.hidden = true;
      (label.querySelector('.file-upload-content') || label).appendChild(clearBtn);
      clearBtn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();           // don't re-open the file picker (label click)
        input.value = '';
        input.dispatchEvent(new Event('change', { bubbles: true }));
      });
    }

    const render = () => {
      if (input.files && input.files.length > 0) {
        const f = input.files[0];
        label.classList.add('has-file');
        strong.textContent = f.name;
        small.textContent = formatBytes(f.size) + ' · ready to submit';
        if (action) action.textContent = 'Change';
        clearBtn.hidden = false;
      } else {
        label.classList.remove('has-file');
        strong.textContent = defaultStrong;
        small.textContent = defaultSmall;
        if (action) action.textContent = defaultAction;
        clearBtn.hidden = true;
      }
    };
    input.addEventListener('change', render);
    // When the form's Reset button is used, restore the upload box too.
    const ownerForm = input.form;
    if (ownerForm) { ownerForm.addEventListener('reset', () => setTimeout(render, 0)); }

    // Drag & drop support — drop a file anywhere on the upload box.
    ['dragenter', 'dragover'].forEach((ev) => {
      label.addEventListener(ev, (e) => { e.preventDefault(); e.stopPropagation(); label.classList.add('dragover'); });
    });
    ['dragleave', 'dragend', 'drop'].forEach((ev) => {
      label.addEventListener(ev, (e) => { e.preventDefault(); e.stopPropagation(); label.classList.remove('dragover'); });
    });
    label.addEventListener('drop', (e) => {
      if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length) {
        try { input.files = e.dataTransfer.files; } catch (err) { /* older browsers */ }
        input.dispatchEvent(new Event('change', { bubbles: true }));
      }
    });
  });

  /* ============================================================
     6 & 7. Form + newsletter submission are handled by app.js now
     (real CSRF-protected AJAX POST to the PHP backend). The old
     fake-success handlers were removed to avoid double submission.
     ============================================================ */

  /* ============================================================
     8 & 9. Service-inquiry prefill and the old hero typewriter were
     removed — those elements no longer exist (app.js handles the
     premium hero typewriter on .typewriter-pro).
     ============================================================ */

  /* ============================================================
     10. Gallery lightbox — click a tile to view full image,
         arrow keys / on-screen buttons to navigate.
     ============================================================ */
  const lightbox = document.getElementById('lightbox');
  if (lightbox) {
    const lbImg = lightbox.querySelector('img');
    const lbCaption = lightbox.querySelector('.lightbox-caption');
    const items = Array.from(document.querySelectorAll('.gallery-item'));
    let current = 0;

    const show = (i) => {
      current = (i + items.length) % items.length;
      const img = items[current].querySelector('img');
      if (!img) return;
      lbImg.src = img.src;
      lbImg.alt = img.alt || '';
      lbCaption.textContent = items[current].getAttribute('data-caption') || '';
    };

    const open = (i) => {
      show(i);
      lightbox.classList.add('open');
      document.body.style.overflow = 'hidden';
    };
    const close = () => {
      lightbox.classList.remove('open');
      document.body.style.overflow = '';
    };

    items.forEach((item, i) => {
      item.addEventListener('click', () => open(i));
      item.style.cursor = 'zoom-in';
    });

    lightbox.querySelector('.lightbox-close').addEventListener('click', close);
    lightbox.querySelector('.lightbox-next').addEventListener('click', (e) => { e.stopPropagation(); show(current + 1); });
    lightbox.querySelector('.lightbox-prev').addEventListener('click', (e) => { e.stopPropagation(); show(current - 1); });
    lightbox.addEventListener('click', (e) => { if (e.target === lightbox) close(); });

    document.addEventListener('keydown', (e) => {
      if (!lightbox.classList.contains('open')) return;
      if (e.key === 'Escape') close();
      else if (e.key === 'ArrowRight') show(current + 1);
      else if (e.key === 'ArrowLeft') show(current - 1);
    });
  }
})();

/* ============================================================
   Job role search — filter open-role cards live (handles many jobs)
   ============================================================ */
(function () {
  var input = document.getElementById('jobSearch');
  var grid = document.getElementById('jobsGrid');
  if (!input || !grid) return;
  var cards = Array.prototype.slice.call(grid.querySelectorAll('.job-card'));
  var empty = document.getElementById('jobsEmpty');
  var count = document.getElementById('jobsCount');
  function apply() {
    var q = input.value.trim().toLowerCase();
    var shown = 0;
    cards.forEach(function (c) {
      var hit = !q || (c.getAttribute('data-search') || '').indexOf(q) !== -1;
      c.style.display = hit ? '' : 'none';
      if (hit) shown++;
    });
    if (empty) empty.hidden = shown !== 0;
    if (count) count.textContent = shown + (shown === 1 ? ' role' : ' roles');
  }
  input.addEventListener('input', apply);
  apply();
})();
