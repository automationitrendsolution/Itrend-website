<section class="page-hero">
  <div class="page-hero-inner">
    <div class="breadcrumb-nav"><a href="<?= url('/') ?>">Home</a><span class="sep">›</span><span class="current">About</span></div>
    <h1 class="page-hero-title">Built for the <em>Future</em> of Commerce</h1>
    <p class="page-hero-subtitle">We have been designing, sourcing, and selling our own products since 2016. Today that work is done by 60+ people in Chennai, for customers in seven countries.</p>
  </div>
</section>

<div class="dark-canvas">
  <div class="orb orb-1"></div>
  <div class="orb orb-2"></div>
  <div class="orb orb-3"></div>

  <section class="about" id="story">
    <div class="section-label" data-aos="fade-up">About Us</div>
    <div class="about-grid">
      <div class="about-text" data-aos="fade-up">
        <h2 class="section-title">A product company, built by its <em>people</em></h2>
        <p class="tagline-lead">iTrend Solution designs, sources, and sells its own products. We started in 2016, and everything from finding a product to shipping it is handled by our own teams rather than agencies or contractors.</p>
        <p>Our 60+ people cover software, R&amp;D, e-commerce operations, content, graphic design, marketing, sourcing, and supply chain. Because those teams sit together, most of them end up understanding far more of the business than their job title suggests.</p>
        <div class="about-pillars">
          <div class="pillar" data-aos="fade-up"><div class="pillar-icon">🎯</div><div class="pillar-text"><h4>Experience</h4><p>Nine years of designing, sourcing, and shipping products we own.</p></div></div>
          <div class="pillar" data-aos="fade-up"><div class="pillar-icon">🚀</div><div class="pillar-text"><h4>Growth</h4><p>Most of our team leads joined as trainees and were promoted from there.</p></div></div>
        </div>
        <div class="about-image-card" data-aos="fade-up" style="margin-top:2rem;">
          <img src="<?= asset('assets/img/aboutus2.jpg') ?>" alt="iTrend Solution — proven results" loading="lazy">
        </div>
      </div>
      <div class="about-right-stack">
        <div class="about-image-card" data-aos="fade-up"><img src="<?= asset('assets/img/aboutus.JPG') ?>" alt="iTrend Solution — team and products" loading="lazy"></div>
        <div class="about-visual" data-aos="fade-up">
          <div class="vmg-item"><div class="vmg-label">Vision</div><p>To keep building products good enough that customers come back, and to grow into new markets without losing the quality that got us there.</p></div>
          <div class="vmg-item"><div class="vmg-label">Mission</div><p>To sell genuinely good products at a fair price. We would rather earn a repeat customer than win a single sale.</p></div>
          <div class="vmg-item"><div class="vmg-label">Global Reach</div><p>We sell in the USA, Canada, the United Kingdom, Germany, Italy, and Brazil. Each market gets its own listings, pricing, and support rather than a translated copy of the last one.</p></div>
          <div class="vmg-footer">Experience. Execution. Excellence.</div>
        </div>
      </div>
    </div>
  </section>

  <section class="why-choose band">
    <div class="section-label" data-aos="fade-up">Why iTrend</div>
    <h2 class="section-title" data-aos="fade-up">What sets us <em>apart</em></h2>
    <div class="why-choose-grid">
      <?php foreach ([
        ['bi-patch-check','Reliability','Nine years of running without interruption, with agreed service levels behind it.'],
        // Raw "&" below: this column prints through e(), so a pre-escaped
        // &amp; would be escaped twice and render literally on the page.
        ['bi-lightbulb','Innovation','A dedicated R&D team whose job is finding the next product before the market gets crowded.'],
        ['bi-cpu','In-house Technology','The software, data, and automation we run on are written here.'],
        ['bi-arrows-fullscreen','Scalability','The same systems handle one product or several thousand across continents.'],
        ['bi-award','Quality','Content, design, and engineering held to the standard our biggest marketplaces demand.'],
        ['bi-life-preserver','Customer Trust','Support that answers quickly, and products that hold up. That is what brings people back.'],
      ] as $i => [$icon,$t,$d]): ?>
        <div class="reason-card glass" data-aos="fade-up" data-aos-delay="<?= ($i%3)*80 ?>">
          <i class="bi <?= $icon ?>"></i><h4><?= $t ?></h4><p><?= e($d) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="cta-band">
    <div class="cta-inner glass" data-aos="zoom-in">
      <h2>Want to grow with <em>us</em>?</h2>
      <p>Have a look at our open roles, or just get in touch. We read everything that comes in.</p>
      <div class="cta-actions">
        <a href="<?= url('/careers') ?>" class="btn-primary-glow">View Careers <span class="arrow">→</span></a>
        <a href="<?= url('/contact') ?>" class="btn-ghost">Contact Us</a>
      </div>
    </div>
  </section>
</div>
