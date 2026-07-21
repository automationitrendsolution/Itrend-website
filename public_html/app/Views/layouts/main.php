<?php /** @var string $content @var string $title @var string $description @var string $page */ ?>
<!doctype html>
<html lang="en" data-theme="light">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
<?php
    $seoTitle = $title ?? 'iTrend Solution — Global Product Commerce Company';
    $seoDesc  = $description ?? 'iTrend Solution builds and delivers quality products across global marketplaces — 5,000+ SKUs, 7+ countries, since 2016.';
    $seoKeys  = $keywords ?? 'iTrend Solution, product company, global e-commerce, marketplace seller, product sourcing, Amazon seller, FBA, Chennai, India, online retail brand';
    $seoImage = abs_url($ogImage ?? 'assets/img/itrend-logo.png');
    $canonical = canonical_url();
?>
    <title><?= e($seoTitle) ?></title>
    <meta name="description" content="<?= e($seoDesc) ?>">
    <meta name="keywords" content="<?= e($seoKeys) ?>">
    <meta name="author" content="Developed by Karthikeyan Ramesh @ iTrend Solution">
    <meta name="publisher" content="iTrend Solution">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="theme-color" content="#7c2e85">
    <meta name="application-name" content="iTrend Solution">
    <meta name="format-detection" content="telephone=no">
    <link rel="canonical" href="<?= e($canonical) ?>">

    <!-- Open Graph -->
    <meta property="og:site_name" content="iTrend Solution">
    <meta property="og:title" content="<?= e($seoTitle) ?>">
    <meta property="og:description" content="<?= e($seoDesc) ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= e($canonical) ?>">
    <meta property="og:locale" content="en_US">
    <meta property="og:image" content="<?= e($seoImage) ?>">
    <meta property="og:image:alt" content="iTrend Solution — global product commerce company">

    <!-- Twitter / X -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@itrendsolution">
    <meta name="twitter:title" content="<?= e($seoTitle) ?>">
    <meta name="twitter:description" content="<?= e($seoDesc) ?>">
    <meta name="twitter:image" content="<?= e($seoImage) ?>">

    <!-- Organization structured data (rich results) -->
    <script type="application/ld+json"><?= json_encode([
        '@context' => 'https://schema.org',
        '@type'    => 'Organization',
        'name'     => 'iTrend Solution',
        'url'      => site_base(),
        'logo'     => abs_url('assets/img/itrend-logo.png'),
        'foundingDate' => '2016',
        'description'  => 'A product-first commerce company that builds and delivers quality products across global marketplaces.',
        'email'    => 'hr@itrendsolution.com',
        'address'  => [
            '@type' => 'PostalAddress',
            'streetAddress'   => 'Tower A, 3rd Floor, Tek Meadows Campus, 51 Rajiv Gandhi Salai (OMR), Sholinganallur',
            'addressLocality' => 'Chennai',
            'addressRegion'   => 'Tamil Nadu',
            'postalCode'      => '600119',
            'addressCountry'  => 'IN',
        ],
        'sameAs' => [
            'https://www.facebook.com/itrendsolution',
            'https://www.instagram.com/itrendsolution',
            'https://www.linkedin.com/company/itrendsolution',
            'https://www.youtube.com/@itrendsolution',
            'https://x.com/itrendsolution',
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
    <!-- WebSite structured data -->
    <script type="application/ld+json"><?= json_encode([
        '@context' => 'https://schema.org',
        '@type'    => 'WebSite',
        'name'     => 'iTrend Solution',
        'url'      => site_base(),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>

    <link rel="icon" type="image/x-icon" href="<?= asset('assets/img/favicon.ico') ?>">
    <link rel="icon" type="image/png" sizes="512x512" href="<?= asset('assets/img/favicon.png') ?>">
    <link rel="apple-touch-icon" href="<?= asset('assets/img/apple-touch-icon.png') ?>">

    <!-- Premium typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap, Icons, AOS — self-hosted (no third-party CDN; keeps CSP locked to 'self') -->
    <link href="<?= asset('assets/vendor/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= asset('assets/vendor/aos/aos.css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') ?>">

    <!-- Site styles: original design language + premium enterprise layer -->
    <link href="<?= asset('assets/css/index.css') ?>" rel="stylesheet">
    <link href="<?= asset('assets/css/premium.css') ?>" rel="stylesheet">
  </head>
  <body data-page="<?= e($page ?? '') ?>">

    <a href="#main-content" class="skip-link">Skip to content</a>
    <?php partial('nav', ['page' => $page ?? '']); ?>
    <span id="main-content"></span>

    <?php
      $ok = flash('success');
      $err = flash('error');
      if ($ok || $err):
    ?>
      <div class="flash-toast <?= $ok ? 'flash-ok' : 'flash-err' ?>" role="status">
        <i class="bi <?= $ok ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill' ?>"></i>
        <span><?= e($ok ?: $err) ?></span>
      </div>
    <?php endif; ?>

    <?= $content ?>

    <?php partial('footer', ['page' => $page ?? '']); ?>
    <?php partial('modals'); ?>

    <!-- Lightbox -->
    <div class="lightbox" id="lightbox" aria-hidden="true">
      <button class="lightbox-btn lightbox-close" aria-label="Close"><i class="bi bi-x-lg"></i></button>
      <button class="lightbox-btn lightbox-prev" aria-label="Previous"><i class="bi bi-chevron-left"></i></button>
      <img alt="" decoding="async">
      <button class="lightbox-btn lightbox-next" aria-label="Next"><i class="bi bi-chevron-right"></i></button>
      <div class="lightbox-caption"></div>
    </div>

    <?php partial('scripts'); ?>
  </body>
</html>
