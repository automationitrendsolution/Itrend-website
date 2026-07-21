<section class="page-hero">
  <div class="page-hero-inner">
    <div class="breadcrumb-nav"><a href="<?= url('/') ?>">Home</a><span class="sep">›</span><span class="current">About</span></div>
    <h1 class="page-hero-title">Built for the <em>Future</em> of Commerce</h1>
    <p class="page-hero-subtitle">A global product-technology company with 9+ years of expertise, serving our customers worldwide since 2016. 60+ specialists, 7+ countries, one vision.</p>
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
        <p class="tagline-lead">iTrend Solution is a global product-technology company. Since 2016, we've designed, built, and shipped our own high-quality products to customers around the world — powered entirely by in-house teams.</p>
        <p>Our 60+ specialists span Software Development, Research &amp; Development, E-Commerce Operations, Content Creation, Graphic Design, Digital Marketing, Product Sourcing, and Supply Chain Management — every capability a modern product company needs, and a rare place to learn many crafts under one roof.</p>
        <div class="about-pillars">
          <div class="pillar" data-aos="fade-up"><div class="pillar-icon">🎯</div><div class="pillar-text"><h4>Experience</h4><p>Nine years designing, building, and shipping our own products worldwide.</p></div></div>
          <div class="pillar" data-aos="fade-up"><div class="pillar-icon">🚀</div><div class="pillar-text"><h4>Growth</h4><p>Many of our leaders started as trainees — here, growth follows impact.</p></div></div>
        </div>
        <div class="about-image-card" data-aos="fade-up" style="margin-top:2rem;">
          <img src="<?= asset('assets/img/aboutus2.jpg') ?>" alt="iTrend Solution — proven results" loading="lazy">
        </div>
      </div>
      <div class="about-right-stack">
        <div class="about-image-card" data-aos="fade-up"><img src="<?= asset('assets/img/aboutus.JPG') ?>" alt="iTrend Solution — team and products" loading="lazy"></div>
        <div class="about-visual" data-aos="fade-up">
          <div class="vmg-item"><div class="vmg-label">Vision</div><p>To lead with innovation and creativity, expanding our footprint with pioneering products that redefine industry standards and create brand value.</p></div>
          <div class="vmg-item"><div class="vmg-label">Mission</div><p>To provide our customers with the best possible products and services at the most competitive prices — because everyone deserves access to high-quality products.</p></div>
          <div class="vmg-item"><div class="vmg-label">Global Reach</div><p>Serving our customers across the USA, Canada, the United Kingdom, Germany, Italy, and Brazil through strategic localisation and deep market insight.</p></div>
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
        ['bi-patch-check','Reliability','Nine years of uninterrupted, SLA-backed operations.'],
        ['bi-lightbulb','Innovation','A dedicated R&D practice shipping new products ahead of the market.'],
        ['bi-cpu','In-house Technology','Software, data, and automation built by our own teams.'],
        ['bi-arrows-fullscreen','Scalability','Systems that grow from one SKU to thousands across continents.'],
        ['bi-award','Quality','Enterprise-grade content, design, and engineering standards.'],
        ['bi-life-preserver','Customer Trust','Responsive support and quality that keep our customers coming back.'],
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
      <p>Explore our open roles, or simply say hello — we'd love to hear from you.</p>
      <div class="cta-actions">
        <a href="<?= url('/careers') ?>" class="btn-primary-glow">View Careers <span class="arrow">→</span></a>
        <a href="<?= url('/contact') ?>" class="btn-ghost">Contact Us</a>
      </div>
    </div>
  </section>
</div>
