<!-- jQuery (project stack) + Bootstrap + AOS + site scripts -->
<script src="<?= asset('assets/vendor/jquery/jquery-3.7.1.min.js') ?>"></script>
<script src="<?= asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= asset('assets/vendor/aos/aos.js') ?>"></script>
<script src="<?= asset('assets/js/main.js') ?>"></script>
<script src="<?= asset('assets/js/app.js') ?>"></script>
<?php if (recaptcha_enabled()): ?>
<!-- Google reCAPTCHA v2 (checkbox) — auto-renders every .g-recaptcha in the page -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif; ?>
