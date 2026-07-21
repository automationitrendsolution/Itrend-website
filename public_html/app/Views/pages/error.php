<?php /** @var int $code @var string $heading @var string $message @var ?string $detail */ ?>
<main class="error-page">
  <div class="error-orb error-orb-1"></div>
  <div class="error-orb error-orb-2"></div>
  <div class="error-inner">
    <div class="error-code"><?= e((string) $code) ?></div>
    <h1 class="error-heading"><?= e($heading) ?></h1>
    <p class="error-message"><?= e($message) ?></p>
    <?php if (!empty($detail)): ?>
      <pre class="error-detail"><?= e($detail) ?></pre>
    <?php endif; ?>
    <div class="error-actions">
      <a href="<?= url('/') ?>" class="btn-primary-glow">Back to Home <span class="arrow">→</span></a>
      <a href="<?= url('/contact') ?>" class="btn-ghost">Contact Support</a>
    </div>
  </div>
</main>
