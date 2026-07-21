<?php
/** @var string $page  — full-width professional navbar with in-page section links */
$home = ($page ?? '') === 'home';
$sec  = static fn (string $id): string => ($GLOBALS['__navHome'] ?? false) ? '#' . $id : '/#' . $id;
$GLOBALS['__navHome'] = $home;
?>
<nav class="navbar navbar-expand-lg fixed-top floating-navbar pro-navbar" id="siteNav">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center py-0" href="<?= url('/') ?>">
      <img src="<?= asset('assets/img/itrend-logo.png') ?>" alt="iTrend Solution" class="navbar-logo">
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
      <i class="bi bi-list"></i>
    </button>

    <div class="collapse navbar-collapse" id="mainNavbar">
      <ul class="navbar-nav nav-center mx-auto mb-2 mb-lg-0 align-items-lg-center">
        <li class="nav-item"><a class="nav-link <?= $home ? 'active' : '' ?>" href="<?= $home ? '#home' : url('/') ?>">Home</a></li>
        <li class="nav-item"><a class="nav-link <?= nav_active($page, 'about') ?>" href="<?= url('/about') ?>">About</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= $sec('trust') ?>">Why iTrend</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= $sec('what-we-do') ?>">What We Do</a></li>
        <li class="nav-item nav-dynamic" hidden><a class="nav-link" href="#"></a></li>
        <li class="nav-item"><a class="nav-link" href="<?= $sec('awards') ?>">Recognition</a></li>
        <li class="nav-item"><a class="nav-link <?= nav_active($page, 'careers') ?>" href="<?= url('/careers') ?>">Careers</a></li>
        <li class="nav-item"><a class="nav-link <?= nav_active($page, 'contact') ?>" href="<?= url('/contact') ?>">Contact</a></li>
      </ul>

      <div class="nav-actions d-flex align-items-center">
        <button class="nav-cta" type="button" data-bs-toggle="modal" data-bs-target="#careerModal">Apply Now <i class="bi bi-arrow-right"></i></button>
      </div>
    </div>
  </div>
</nav>
