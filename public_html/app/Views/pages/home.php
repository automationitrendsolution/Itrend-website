<?php /** Premium flagship landing page */ ?>

<!-- ============ HERO ============ -->
<section class="hero-pro" id="home">
  <div class="hero-bg" aria-hidden="true">
    <video class="hero-video" autoplay muted loop playsinline preload="auto">
      <source src="<?= asset('assets/img/hero-video.mp4') ?>" type="video/mp4">
    </video>
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
        iTrend Solution is a global technology company built on the people inside it. Since 2016,
        our teams have grown by learning across crafts, taking on real ownership early, and
        building careers faster than they thought possible.
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
    <p class="section-desc" data-aos="fade-up" data-aos-delay="50">Nine years of building great products — and a team that keeps learning, leading, and growing together.</p>
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
    <p class="section-desc" data-aos="fade-up" data-aos-delay="50">iTrend brings every capability of a modern product company in-house. Whatever your craft, there's real work, real ownership, and the room — and mentors — to master it here.</p>
    <div class="why-choose-grid">
      <?php
        $capabilities = [
          ['bi-box-seam', 'Product Sourcing', 'Discovering and quality-checking new products alongside our China operations base.'],
          ['bi-card-checklist', 'Cataloguing &amp; Listings', 'Building and managing rich product catalogues across international marketplaces.'],
          ['bi-palette', 'Creative &amp; Design', 'Product photography, infographics, and brand creative, all crafted in-house.'],
          ['bi-megaphone', 'Digital Marketing', 'A data-driven marketing team growing our brands and sharpening its craft daily.'],
          ['bi-truck', 'Supply Chain &amp; Logistics', 'Inventory planning and worldwide logistics, from supplier to final fulfilment.'],
          ['bi-bag-check', 'Order Management', 'Day-to-day operations and fulfilment, owned end-to-end by our own teams.'],
          ['bi-cpu', 'Technology &amp; R&amp;D', 'In-house software, automation, and a dedicated R&amp;D practice building what\'s next.'],
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
    <h3 class="showcase-title" id="glimpse" data-aos="fade-up">A Glimpse <em>Inside iTrend</em></h3>
    <div class="gallery-block">
      <div class="gallery-grid">
        <?php
          $showcase = [
            ['aboutus.JPG', 'wide', 'Our products and operations'],
            ['careers/024A0790.JPG', '', 'Team collaboration at our Chennai Head Office'],
            ['careers/024A3591.JPG', 'tall', 'Learning and building, side by side'],
            ['aboutus2.jpg', '', 'Craft, quality, and execution'],
            ['careers/024A6127.JPG', '', 'One team, one vision'],
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
        <p class="tagline-lead" data-aos="fade-up" data-aos-delay="50">iTrend Solution has grown from a small team in Chennai into a global technology company — built entirely on the people inside it. We develop our own expertise, our own teams, and our own way of working.</p>
        <p data-aos="fade-up" data-aos-delay="100">Our people span Software Development, Research &amp; Development, Product Sourcing, Cataloguing, Content &amp; Design, Supply Chain, and Operations — a rare place to learn many crafts and grow fast, all under one roof.</p>
        <div class="about-pillars">
          <div class="pillar" data-aos="fade-up" data-aos-delay="120">
            <div class="pillar-icon">🎯</div>
            <div class="pillar-text"><h4>Learn the Whole Picture</h4><p>Every discipline is in-house, so you see how a real product company runs — not just one slice of it.</p></div>
          </div>
          <div class="pillar" data-aos="fade-up" data-aos-delay="180">
            <div class="pillar-icon">🚀</div>
            <div class="pillar-text"><h4>Grow Fast</h4><p>Many of our team leads started as trainees. Here, growth follows impact — not tenure.</p></div>
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
    <p class="section-desc" data-aos="fade-up" data-aos-delay="50">At iTrend, recognition is direct and transparent — every effort is noticed and rewarded. Here's a glimpse of our best performers and proudest moments.</p>
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
    <p class="section-desc" data-aos="fade-up" data-aos-delay="50">We're a team of innovators and builders. Whether you're an engineer, marketer, designer, or analyst, there's a place for you to do your best work and grow fast.</p>

    <div class="careers-spotlight" data-aos="fade-up" data-aos-delay="60">
      <div class="cs-media"><img src="<?= asset('assets/img/careers/024A0790.JPG') ?>" alt="Life at iTrend — team collaboration" loading="lazy"></div>
      <div class="careers-cta-grid perks-2col">
        <div class="career-perk glass"><span class="perk-emoji">🚀</span><h4>Ownership From Day One</h4><p>Real work and real impact from week one — your contribution is seen and rewarded.</p></div>
        <div class="career-perk glass"><span class="perk-emoji">📈</span><h4>Fast Growth</h4><p>Many of our senior professionals and team leaders began their journeys as trainees. Progress here is about impact, not tenure.</p></div>
        <div class="career-perk glass"><span class="perk-emoji">🌍</span><h4>Global Exposure</h4><p>Work alongside teams operating across the USA, Canada, UK, Germany, Italy, and Brazil.</p></div>
        <div class="career-perk glass"><span class="perk-emoji">🎉</span><h4>A Real Community</h4><p>Festivals, outings, and celebrations all year — a workplace that feels like family.</p></div>
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
      <p>Join a team where you'll own real work from day one, learn across crafts, and grow with a company that's been building for nine years.</p>
      <div class="cta-actions cta-actions--pair">
        <a href="<?= url('/careers') ?>" class="btn-primary-glow">View Open Roles <span class="arrow">→</span></a>
        <button class="btn-ghost" data-bs-toggle="modal" data-bs-target="#careerModal">Apply Now</button>
      </div>
    </div>
  </section>
</div>

<button class="back-to-top" id="backToTop" aria-label="Back to top" title="Back to top"><i class="bi bi-arrow-up"></i></button>
