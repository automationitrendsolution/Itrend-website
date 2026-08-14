<?php /*
  jQuery was removed here: a repo-wide search for `$(` / `jQuery` found zero
  call sites in assets/js/*.js and zero in any view — the 87 KB file was
  downloaded and parsed on every page for nothing. Bootstrap's bundle is
  jQuery-free (it ships its own Popper), so nothing else depended on it.
  If a future script needs it, add it back here deliberately.
*/ ?>
<!-- Bootstrap (modals) + AOS (legacy reveals) + site scripts -->
<script src="<?= asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') ?>" defer></script>
<script src="<?= asset('assets/vendor/aos/aos.js') ?>" defer></script>
<script src="<?= asset('assets/js/main.js') ?>" defer></script>
<script src="<?= asset('assets/js/app.js') ?>" defer></script>
<script src="<?= asset('assets/js/ui.js') ?>" defer></script>
<script src="<?= asset('assets/js/select.js') ?>" defer></script>
<?php if (recaptcha_enabled()): ?>
<!-- Google reCAPTCHA v2 (checkbox) — auto-renders every .g-recaptcha in the page -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif; ?>
