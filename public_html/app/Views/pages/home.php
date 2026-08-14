<?php /** Premium flagship landing page */ ?>

<!-- ============ HERO ============ -->
<section class="hero-pro" id="home">
  <div class="hero-bg" aria-hidden="true">
    <?php /*
      The hero video is decorative (it sits at low opacity beneath a scrim).
      Its src is held in data-src so the 2.7 MB file is not fetched on phones,
      on metered connections, or when the visitor prefers reduced motion —
      assets/js/ui.js attaches it at runtime only when it is worth the bytes.
      The mesh/gradient beneath renders the same composition without it.
    */ ?>
    <video class="hero-video" muted loop playsinline preload="none"
           data-src="<?= asset('assets/img/hero-video.mp4') ?>"></video>
    <div class="hero-mesh"></div>
    <div class="hero-grid-overlay"></div>
  </div>

  <div class="hero-pro-inner container">
    <div class="hero-pro-content" data-aos="fade-up">
      <span class="hero-badge"><span class="pulse"></span> Global Product Technology Company · Since 2016</span>
      <h1 class="hero-pro-title">
        <?php /* NBSP keeps the em-dash tied to "Products" so it never begins a wrapped line. */ ?>
        Where Ambitious People Build Great&nbsp;Products&nbsp;— and <span class="grad-text">Grow&nbsp;Fast</span>
      </h1>
      <p class="hero-pro-sub">
        We design, source, and sell our own products to customers in seven countries. Since 2016
        we have built the whole operation in Chennai, and we train our own people to run it.
      </p>
      <div class="hero-pro-cta">
        <a href="<?= url('/careers') ?>" class="btn-primary-glow">Explore Careers <span class="arrow">→</span></a>
        <a href="<?= url('/about') ?>" class="btn-ghost">About iTrend</a>
      </div>
    </div>
  </div>
</section>

<div class="dark-canvas">
  <div class="orb orb-1"></div>
  <div class="orb orb-2"></div>
  <div class="orb orb-3"></div>

  <!-- ============ TRUST & CREDIBILITY ============ -->
  <section class="trust-section" id="trust">
    <div class="section-label" data-aos="fade-up">Why iTrend</div>
    <h2 class="section-title" data-aos="fade-up">Proven, and still <em>growing</em></h2>
    <p class="section-desc" data-aos="fade-up" data-aos-delay="50">We started in 2016 with a handful of people. Today eight teams handle everything from sourcing to software, and our products reach customers on four continents.</p>
    <div class="trust-grid">
      <?php
        $metrics = [
          ['2016', '', 'Founded', 'bi-flag', false],
          ['9', '+', 'Years of Operations', 'bi-calendar3'],
          ['8', '', 'Specialist Teams', 'bi-diagram-3'],
          ['7', '+', 'Countries', 'bi-globe2'],
          ['4', '', 'Continents', 'bi-globe-americas'],
          ['100', '%', 'In-house Expertise', 'bi-buildings'],
        ];
        foreach ($metrics as $i => [$num, $suf, $label, $icon]):
          // Years render statically — the counter would animate them like a quantity.
          $animate = $metrics[$i][4] ?? true;
      ?>
        <div class="trust-card glass" data-aos="zoom-in" data-aos-delay="<?= $i * 60 ?>">
          <i class="bi <?= $icon ?>"></i>
          <h3 class="stat-number"<?= $animate ? ' data-count="' . $num . '" data-suffix="' . $suf . '"' : '' ?>><?= $num . $suf ?></h3>
          <p><?= $label ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- ============ WHAT WE DO ============ -->
  <section class="whatwedo band" id="what-we-do">
    <div class="section-label" data-aos="fade-up">What We Do</div>
    <h2 class="section-title" data-aos="fade-up">Every discipline, <em>under one roof</em></h2>
    <p class="section-desc" data-aos="fade-up" data-aos-delay="50">Sourcing, design, marketing, logistics, and software all sit in the same office. We do not outsource the parts that matter, which is why people who join us end up understanding the whole business rather than one corner of it.</p>
    <div class="why-choose-grid">
      <?php
        $capabilities = [
          ['bi-box-seam', 'Product Sourcing', 'Our team finds new products and inspects quality directly, working with our base in China.'],
          ['bi-card-checklist', 'Cataloging &amp; Listings', 'We write and maintain every listing, image, and specification across the marketplaces we sell on.'],
          ['bi-palette', 'Creative &amp; Design', 'Product photography, infographics, and brand work, shot and designed in our own studio.'],
          ['bi-megaphone', 'Digital Marketing', 'Our marketing team runs the ad spend, watches the numbers daily, and owns the results.'],
          ['bi-truck', 'Supply Chain &amp; Logistics', 'We plan inventory and move stock from the supplier to the customer\'s door across seven countries.'],
          ['bi-bag-check', 'Order Management', 'Orders, returns, and fulfillment are handled here, so problems reach the people who can fix them.'],
          ['bi-cpu', 'Technology &amp; R&amp;D', 'We build our own tools and automation, and keep a dedicated R&amp;D team working on what comes next.'],
        ];
        foreach ($capabilities as $i => [$icon, $title, $desc]):
      ?>
        <div class="reason-card glass" data-aos="fade-up" data-aos-delay="<?= ($i % 4) * 60 ?>">
          <i class="bi <?= $icon ?>"></i>
          <h4><?= $title ?></h4>
          <p><?= $desc ?></p>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- iTrend Glimpse — image showcase (click to enlarge) -->
    <h3 class="showcase-title" id="glimpse" data-aos="fade-up">A Glimpse <em>of iTrend</em></h3>
    <div class="gallery-block">
      <div class="gallery-grid">
        <?php
          $showcase = [
            ['aboutus.JPG', 'wide', 'Our products and operations'],
            ['careers/024A0790.JPG', '', 'Team collaboration at our Chennai Head Office'],
            ['careers/024A3591.JPG', 'tall', 'Working together on the floor'],
            ['aboutus2.jpg', '', 'Checking product quality'],
            ['careers/024A6127.JPG', '', 'The team at our Chennai office'],
            ['careers/img1.jpg', '', 'Where the work gets done'],
          ];
          foreach ($showcase as $i => [$file, $cls, $cap]):
        ?>
          <div class="gallery-item <?= $cls ?>" data-aos="zoom-in" data-aos-delay="<?= ($i % 3) * 60 ?>" data-caption="<?= $cap ?>">
            <span class="zoom-badge"><i class="bi bi-arrows-fullscreen"></i></span>
            <img src="<?= asset('assets/img/' . $file) ?>" alt="<?= e($cap) ?>" loading="lazy">
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ============ WHO WE ARE ============ -->
  <section class="about" id="about">
    <div class="section-label" data-aos="fade-up">Who We Are</div>
    <div class="about-grid">
      <div class="about-text">
        <h2 class="section-title" data-aos="fade-up">A company built on its <em>people</em></h2>
        <p class="tagline-lead" data-aos="fade-up" data-aos-delay="50">iTrend started in 2016 with a dedicated team in Chennai. We now run eight departments and sell in seven countries, and we got here by training people rather than hiring expertise in.</p>
        <p data-aos="fade-up" data-aos-delay="100">Software, R&amp;D, sourcing, cataloging, design, supply chain, and operations all work from the same floor. If you want to see how a product business actually runs, that is hard to find anywhere else.</p>
        <div class="about-pillars">
          <div class="pillar" data-aos="fade-up" data-aos-delay="120">
            <div class="pillar-icon">🎯</div>
            <div class="pillar-text"><h4>See the whole business</h4><p>You sit near the people who source the product, list it, market it, and ship it. Most jobs only show you one of those.</p></div>
          </div>
          <div class="pillar" data-aos="fade-up" data-aos-delay="180">
            <div class="pillar-icon">🚀</div>
            <div class="pillar-text"><h4>Move up on merit</h4><p>Most of our team leads joined as trainees. What counts here is the work you do, not how long you have been here.</p></div>
          </div>
        </div>
      </div>
      <div class="about-right-stack">
        <div class="about-image-card" data-aos="zoom-in">
          <img src="<?= asset('assets/img/aboutus.JPG') ?>" alt="iTrend Solution team and products" loading="lazy">
        </div>
        <div class="about-image-card" data-aos="zoom-in" data-aos-delay="80">
          <img src="<?= asset('assets/img/aboutus2.jpg') ?>" alt="iTrend Solution — proven results and execution" loading="lazy">
        </div>
        <a href="<?= url('/about') ?>" class="about-more-link" data-aos="fade-up" data-aos-delay="120">Read our full story <i class="bi bi-arrow-right"></i></a>
      </div>
    </div>
  </section>

  <!-- ============ AWARDS & RECOGNITION ============ -->
  <section class="awards-section" id="awards">
    <div class="section-label" data-aos="fade-up">Awards &amp; Recognition</div>
    <h2 class="section-title" data-aos="fade-up">Celebrating our <em>best performers</em></h2>
    <p class="section-desc" data-aos="fade-up" data-aos-delay="50">We give out Best Performer awards every quarter, in front of the whole company. These are some of the people who earned them.</p>
    <div class="gallery-block">
      <div class="gallery-grid awards-grid">
        <?php for ($i = 1; $i <= 6; $i++):
          $f = 'assets/img/awards/award-' . $i . '.jpg';
          if (!is_file(BASE_PATH . '/' . $f)) continue;
        ?>
          <div class="gallery-item" data-aos="zoom-in" data-aos-delay="<?= (($i - 1) % 3) * 60 ?>" data-caption="Best Performer Award · iTrend Solution">
            <span class="zoom-badge"><i class="bi bi-arrows-fullscreen"></i></span>
            <img src="<?= asset($f) ?>" alt="iTrend Solution — Best Performer Award" loading="lazy">
          </div>
        <?php endfor; ?>
      </div>
    </div>
  </section>

  <!-- ============ CAREERS FOCUS ============ -->
  <section class="careers-band band" id="careers">
    <div class="section-label" data-aos="fade-up">Build Your Career</div>
    <h2 class="section-title" data-aos="fade-up">Explore your potential and <em>grow with us</em> at iTrend</h2>
    <p class="section-desc" data-aos="fade-up" data-aos-delay="50">We hire developers, analysts, designers, marketers, and operations people, including freshers. If you are willing to learn, we will teach you the rest.</p>

    <div class="careers-spotlight" data-aos="fade-up" data-aos-delay="60">
      <div class="cs-media"><img src="<?= asset('assets/img/careers/024A0790.JPG') ?>" alt="Life at iTrend — team collaboration" loading="lazy"></div>
      <div class="careers-cta-grid perks-2col">
        <div class="career-perk glass"><span class="perk-emoji">🚀</span><h4>Real work early</h4><p>You get something that matters in your first few weeks. It is a small enough team that your work does not disappear.</p></div>
        <div class="career-perk glass"><span class="perk-emoji">📈</span><h4>Room to move up</h4><p>Several of our team leads and senior specialists started here as trainees. That path is still open.</p></div>
        <div class="career-perk glass"><span class="perk-emoji">🌍</span><h4>Work that ships abroad</h4><p>Our products sell in the USA, Canada, the UK, Germany, Italy, and Brazil, so you learn how each market differs.</p></div>
        <div class="career-perk glass"><span class="perk-emoji">🎉</span><h4>People you will like</h4><p>We mark the festivals properly, take trips, and eat together often. It is a friendly place to work.</p></div>
      </div>
    </div>

    <div class="cta-actions" style="justify-content:center;margin-top:2.5rem;" data-aos="fade-up">
      <a href="<?= url('/careers') ?>" class="btn-primary-glow">View Open Roles <span class="arrow">→</span></a>
      <button class="btn-ghost" data-bs-toggle="modal" data-bs-target="#careerModal">Apply Now</button>
    </div>
  </section>

  <!-- ============ CONVERSION CTA ============ -->
  <section class="cta-band" id="get-started">
    <div class="cta-inner glass" data-aos="zoom-in">
      <h2>Ready to build your <em>career</em> with us?</h2>
      <p>We are hiring across every department. If you want to learn how a product company works from the inside, start here.</p>
      <div class="cta-actions cta-actions--pair">
        <a href="<?= url('/careers') ?>" class="btn-primary-glow">View Open Roles <span class="arrow">→</span></a>
        <button class="btn-ghost" data-bs-toggle="modal" data-bs-target="#careerModal">Apply Now</button>
      </div>
    </div>
  </section>
</div>

<button class="back-to-top" id="backToTop" aria-label="Back to top" title="Back to top"><i class="bi bi-arrow-up"></i></button>
