/* =====================================================================
   iTrend Solution — premium select
   ---------------------------------------------------------------------
   A browser's native <select> popup cannot be styled: the option list is
   drawn by the operating system, which is why it appears as a plain grey
   menu in an otherwise designed form. This replaces the CLOSED control and
   the OPEN list with markup we control, while keeping the real <select> in
   the DOM as the source of truth.

   That matters for correctness as much as looks:
     · the real <select> still submits, so no backend change is needed
     · `required` validation and form.reset() keep working
     · the trigger mirrors ARIA combobox semantics, and the listbox is
       fully keyboard-driven (arrows, Home/End, type-ahead, Enter, Esc)
     · on touch devices we leave the native picker alone — the OS wheel is
       genuinely better there than anything re-implemented in a page

   Progressive enhancement: with JS off, the plain <select> works as usual.
   ===================================================================== */
(function () {
  'use strict';

  // Touch devices keep the native picker (better ergonomics, no scroll traps).
  var coarse = window.matchMedia('(pointer: coarse)').matches;
  if (coarse) return;

  var uid = 0;

  function enhance(select) {
    if (select.dataset.psReady) return;
    select.dataset.psReady = '1';

    var id      = 'ps-' + (++uid);
    var wrap    = document.createElement('div');
    var trigger = document.createElement('button');
    var list    = document.createElement('div');

    wrap.className = 'ps';
    trigger.type = 'button';
    trigger.className = 'ps-trigger';
    trigger.setAttribute('role', 'combobox');
    trigger.setAttribute('aria-haspopup', 'listbox');
    trigger.setAttribute('aria-expanded', 'false');
    trigger.setAttribute('aria-controls', id);
    list.className = 'ps-list';
    list.id = id;
    list.setAttribute('role', 'listbox');
    list.hidden = true;

    // Carry the label across so screen readers announce the same name.
    var labelledBy = select.getAttribute('aria-labelledby');
    if (labelledBy) trigger.setAttribute('aria-labelledby', labelledBy);
    var aria = select.getAttribute('aria-label');
    if (aria) trigger.setAttribute('aria-label', aria);

    select.parentNode.insertBefore(wrap, select);
    wrap.appendChild(select);
    wrap.appendChild(trigger);
    wrap.appendChild(list);

    // The native control stays in the DOM (it is what submits) but is taken
    // out of the tab order and hidden from assistive tech, since the trigger
    // now represents it.
    select.classList.add('ps-native');
    select.setAttribute('tabindex', '-1');
    select.setAttribute('aria-hidden', 'true');

    var options = [];   // { el, value, label, disabled, placeholder }
    var activeIdx = -1;

    function build() {
      list.textContent = '';
      options = [];
      Array.prototype.forEach.call(select.options, function (opt, i) {
        var placeholder = (opt.value === '' && opt.disabled);
        var row = document.createElement('div');
        row.className = 'ps-option' + (placeholder ? ' is-placeholder' : '');
        row.setAttribute('role', 'option');
        row.textContent = opt.textContent;
        row.dataset.index = String(i);
        if (opt.disabled) row.setAttribute('aria-disabled', 'true');
        if (!placeholder) list.appendChild(row);
        options.push({ el: row, value: opt.value || opt.textContent,
                       label: opt.textContent, disabled: opt.disabled,
                       placeholder: placeholder, index: i });
      });
      syncTrigger();
    }

    function syncTrigger() {
      var opt = select.options[select.selectedIndex];
      var isPlaceholder = !opt || (opt.value === '' && opt.disabled);
      trigger.textContent = opt ? opt.textContent : '';
      trigger.classList.toggle('is-placeholder', isPlaceholder);
      options.forEach(function (o) {
        var on = o.index === select.selectedIndex && !o.placeholder;
        o.el.classList.toggle('is-selected', on);
        o.el.setAttribute('aria-selected', on ? 'true' : 'false');
      });
      // Mirror the native invalid state so the styling stays in step.
      trigger.classList.toggle('is-invalid', select.classList.contains('field-error'));
    }

    /* Position the list against the viewport.
       The list is moved to <body> and positioned with `fixed` because its
       natural ancestors clip it: .contact-form-card and .dark-canvas both
       set overflow:hidden, and the card additionally has a transform and a
       backdrop-filter. Each of those creates a containing block/stacking
       context that traps an absolutely-positioned child no matter how high
       its z-index is — which is why the menu was invisible inside the form. */
    function position() {
      var r = trigger.getBoundingClientRect();
      var gap = 6;
      var spaceBelow = window.innerHeight - r.bottom - gap;
      var spaceAbove = r.top - gap;
      var maxH = 264;

      list.style.width = r.width + 'px';
      list.style.left = r.left + 'px';

      if (spaceBelow < 180 && spaceAbove > spaceBelow) {
        // Not enough room underneath — open upward.
        var h = Math.min(maxH, spaceAbove);
        list.style.maxHeight = h + 'px';
        list.style.top = (r.top - gap - h) + 'px';
        wrap.classList.add('ps-up');
      } else {
        list.style.maxHeight = Math.min(maxH, spaceBelow) + 'px';
        list.style.top = (r.bottom + gap) + 'px';
        wrap.classList.remove('ps-up');
      }
    }

    function open() {
      if (!list.hidden) return;
      closeOthers();
      // Render in the top layer so no ancestor can clip it.
      if (list.parentNode !== document.body) document.body.appendChild(list);
      list.hidden = false;
      wrap.classList.add('is-open');
      trigger.setAttribute('aria-expanded', 'true');
      position();

      activeIdx = options.findIndex(function (o) { return o.index === select.selectedIndex && !o.placeholder; });
      if (activeIdx < 0) activeIdx = options.findIndex(function (o) { return !o.placeholder && !o.disabled; });
      markActive();

      // Keep the menu pinned to its field while the page moves beneath it.
      window.addEventListener('scroll', position, true);
      window.addEventListener('resize', position);
    }

    function close(focusTrigger) {
      if (list.hidden) return;
      list.hidden = true;
      wrap.classList.remove('is-open', 'ps-up');
      trigger.setAttribute('aria-expanded', 'false');
      trigger.removeAttribute('aria-activedescendant');
      window.removeEventListener('scroll', position, true);
      window.removeEventListener('resize', position);
      if (focusTrigger !== false) trigger.focus();
    }

    function markActive() {
      options.forEach(function (o, i) { o.el.classList.toggle('is-active', i === activeIdx); });
      var cur = options[activeIdx];
      if (cur && cur.el.parentNode) {
        cur.el.scrollIntoView({ block: 'nearest' });
      }
    }

    function move(step) {
      if (list.hidden) { open(); return; }
      var n = options.length;
      for (var k = 1; k <= n; k++) {
        var i = (activeIdx + step * k + n * k) % n;
        if (i < 0) i += n;
        if (!options[i].placeholder && !options[i].disabled) { activeIdx = i; break; }
      }
      markActive();
    }

    function commit(i) {
      var o = options[i];
      if (!o || o.disabled || o.placeholder) return;
      select.selectedIndex = o.index;
      // Fire change so existing listeners (validation, prefill) still run.
      select.dispatchEvent(new Event('change', { bubbles: true }));
      syncTrigger();
      close();
    }

    trigger.addEventListener('click', function () { list.hidden ? open() : close(); });

    list.addEventListener('click', function (e) {
      var row = e.target.closest('.ps-option');
      if (!row) return;
      commit(options.findIndex(function (o) { return o.el === row; }));
    });

    // Type-ahead: jump to the first option starting with what was typed.
    var typed = '', typedTimer = null;
    trigger.addEventListener('keydown', function (e) {
      switch (e.key) {
        case 'ArrowDown': e.preventDefault(); move(1); break;
        case 'ArrowUp':   e.preventDefault(); move(-1); break;
        case 'Home':      e.preventDefault(); activeIdx = -1; move(1); break;
        case 'End':       e.preventDefault(); activeIdx = options.length; move(-1); break;
        case 'Enter':
        case ' ':
          e.preventDefault();
          list.hidden ? open() : commit(activeIdx);
          break;
        case 'Escape':    if (!list.hidden) { e.preventDefault(); close(); } break;
        case 'Tab':       close(false); break;
        default:
          if (e.key.length === 1) {
            typed += e.key.toLowerCase();
            clearTimeout(typedTimer);
            typedTimer = setTimeout(function () { typed = ''; }, 600);
            var hit = options.findIndex(function (o) {
              return !o.placeholder && !o.disabled &&
                     o.label.toLowerCase().indexOf(typed) === 0;
            });
            if (hit >= 0) { if (list.hidden) open(); activeIdx = hit; markActive(); }
          }
      }
    });

    // Clicking away closes. The list is a child of <body> now, so it is not
    // inside `wrap` — both have to be checked or selecting an option would
    // be treated as an outside click.
    document.addEventListener('click', function (e) {
      if (!wrap.contains(e.target) && !list.contains(e.target)) close(false);
    });

    // Keep in step with programmatic changes (job-card prefill, form reset).
    select.addEventListener('change', syncTrigger);
    var form = select.form;
    if (form) form.addEventListener('reset', function () { setTimeout(syncTrigger, 0); });

    build();
  }

  // Only one menu open at a time. Lists live on <body>, so they are found
  // by their trigger's aria-controls rather than by descending from .ps.
  function closeOthers() {
    document.querySelectorAll('.ps.is-open').forEach(function (w) {
      w.classList.remove('is-open', 'ps-up');
      var t = w.querySelector('.ps-trigger');
      if (t) {
        t.setAttribute('aria-expanded', 'false');
        var l = document.getElementById(t.getAttribute('aria-controls'));
        if (l) l.hidden = true;
      }
    });
  }

  function init(root) {
    (root || document).querySelectorAll('select:not([multiple])').forEach(enhance);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { init(); });
  } else {
    init();
  }
})();
