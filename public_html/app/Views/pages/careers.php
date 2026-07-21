<?php /** @var array $jobs */ ?>
<section class="page-hero">
  <div class="page-hero-inner">
    <div class="breadcrumb-nav"><a href="<?= url('/') ?>">Home</a><span class="sep">›</span><span class="current">Careers</span></div>
    <h1 class="page-hero-title">Shape the Future of <em>E-Commerce</em> With Us</h1>
    <p class="page-hero-subtitle">Careers at iTrend offer a dynamic, growth-driven environment where innovation meets opportunity. We provide hands-on experience, rapid learning, and the chance to make a real impact from day one — whether you're a fresher kickstarting your journey or an experienced professional seeking new challenges.</p>
    <div class="page-hero-cta" data-aos="fade-up" data-aos-delay="80">
      <a href="#open-roles" class="btn-primary-glow">View Open Roles <span class="arrow">→</span></a>
    </div>
  </div>
</section>

<div class="dark-canvas">
  <div class="orb orb-1"></div>
  <div class="orb orb-2"></div>
  <div class="orb orb-3"></div>

  <!-- WHY WORK HERE -->
  <section id="why" class="band">
    <div class="section-label" data-aos="fade-up">Why Work at iTrend</div>
    <h2 class="section-title" data-aos="fade-up">A place where careers <em>accelerate</em>.</h2>
    <p class="section-desc" data-aos="fade-up" data-aos-delay="50">Big enough to do serious work for global brands, lean enough that what you do is seen, valued, and rewarded fast.</p>
    <div class="why-grid">
      <div class="why-card" data-aos="fade-up">
        <div class="why-icon">🤝</div>
        <h3>Culture</h3>
        <p>Our culture is built on innovation, collaboration, and transparency. We embrace agility and adaptability, encourage every individual to take ownership, and keep open communication at the heart of everything — through brainstorming sessions, team outings, and celebrating achievements together.</p>
      </div>
      <div class="why-card" data-aos="fade-up" data-aos-delay="100">
        <div class="why-icon">🧭</div>
        <h3>Management That Backs You</h3>
        <p>Our leadership empowers individuals by fostering ownership, attention to detail, and proactiveness. With a keen eye for talent, our management recognises potential instantly and provides unwavering support — so the people who show drive are noticed and invested in.</p>
      </div>
      <div class="why-card" data-aos="fade-up" data-aos-delay="200">
        <div class="why-icon">📈</div>
        <h3>Professional Growth</h3>
        <p>As a fast-growing company, we provide hands-on experience and rapid learning from day one. Whether you're a fresher kickstarting your journey or an experienced professional seeking new challenges, iTrend is a place to grow, innovate, and advance quickly across crafts.</p>
      </div>
    </div>
  </section>

  <!-- TEAMS YOU CAN JOIN (from brochure department wheel) -->
  <section id="teams">
    <div class="section-label" data-aos="fade-up">Our Teams</div>
    <h2 class="section-title" data-aos="fade-up">Every capability, <em>under one roof.</em></h2>
    <p class="section-desc" data-aos="fade-up" data-aos-delay="50">iTrend is a multidisciplinary team of 60+ specialists across eight departments working in perfect sync. Wherever your craft fits, there's a team here for you.</p>
    <div class="dept-grid">
      <?php
        $teams = [
          ['💻','IT &amp; Software','Tools, dashboards &amp; automation'],
          ['📦','SCM &amp; Logistics','Sourcing to FBA fulfilment'],
          ['📋','Order Management','Operations &amp; fulfilment'],
          ['💰','Accounts','Finance &amp; payouts'],
          ['🎨','Cataloguing &amp; Graphic Design','Listings &amp; A+ content'],
          ['📣','Digital Marketing','PPC &amp; brand growth'],
          ['🔬','R&amp;D','New products &amp; research'],
          ['👥','Human Resources','People &amp; culture'],
        ];
        foreach ($teams as $i => [$icon,$name,$desc]):
      ?>
        <div class="dept-chip" data-aos="zoom-in" data-aos-delay="<?= ($i % 4) * 60 ?>">
          <span class="dept-icon"><?= $icon ?></span>
          <h4><?= $name ?></h4>
          <span class="dept-count"><?= $desc ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- OPEN ROLES -->
  <section id="open-roles">
    <div class="section-label" data-aos="fade-up">Open Roles</div>
    <h2 class="section-title" data-aos="fade-up">Find your <em>next role</em></h2>

    <div class="jobs-search" data-aos="fade-up" data-aos-delay="40">
      <i class="bi bi-search"></i>
      <input type="search" id="jobSearch" placeholder="Search roles by title, team, or location…" aria-label="Search open roles" autocomplete="off">
      <span class="jobs-count" id="jobsCount"></span>
    </div>

    <div class="jobs-grid" id="jobsGrid">
      <?php foreach ($jobs as $i => $job): ?>
        <div class="job-card" data-aos="fade-up" data-aos-delay="<?= ($i % 3) * 60 ?>"
             data-search="<?= e(strtolower($job['title'] . ' ' . $job['dept'] . ' ' . $job['location'] . ' ' . $job['type'] . ' ' . $job['exp'])) ?>">
          <div class="job-info">
            <h3><?= e($job['title']) ?></h3>
            <div class="job-meta">
              <span class="job-tag dept"><?= e($job['dept']) ?></span>
              <span class="job-tag"><i class="bi bi-geo-alt"></i> <?= e($job['location']) ?></span>
              <span class="job-tag"><i class="bi bi-briefcase"></i> <?= e($job['type']) ?></span>
              <span class="job-tag"><i class="bi bi-clock"></i> <?= e($job['exp']) ?></span>
            </div>
          </div>
          <button class="job-apply" data-bs-toggle="modal" data-bs-target="#careerModal" data-role="<?= e($job['dept']) ?>">Apply <i class="bi bi-arrow-right"></i></button>
        </div>
      <?php endforeach; ?>
      <p class="jobs-empty" id="jobsEmpty" hidden>No roles match your search. Try a different keyword, or <a href="#" data-bs-toggle="modal" data-bs-target="#careerModal" class="inline-link">send us your profile anyway →</a></p>
    </div>
    <p class="section-desc" style="margin-top:2rem" data-aos="fade-up">Don't see a perfect fit? <a href="<?= url('/careers#open-roles') ?>" data-bs-toggle="modal" data-bs-target="#careerModal" class="inline-link">Send us your profile anyway →</a></p>
  </section>

  <!-- GROWTH PATH -->
  <section id="growth" class="band">
    <div class="section-label" data-aos="fade-up">Growth Path</div>
    <h2 class="section-title" data-aos="fade-up">Where a career here can <em>take you</em>.</h2>
    <div class="timeline">
      <?php foreach ([
        ['Month 0–3','Trainee / Associate','Onboard fast and get hands-on with live marketplace work from week one.'],
        ['Month 3–12','Specialist','Own a brand, marketplace, or workflow end-to-end and drive measurable results.'],
        ['Year 1–2','Senior Specialist','Become the go-to expert in your craft and mentor newcomers.'],
        ['Year 2+','Team Lead &amp; Beyond','Lead a discipline, build your team, and influence the business.'],
      ] as $i => [$stage,$title,$desc]): ?>
        <div class="timeline-step" data-aos="fade-left" data-aos-delay="<?= $i*80 ?>">
          <div class="timeline-dot"><?= sprintf('%02d', $i+1) ?></div>
          <div class="timeline-body"><span class="stage"><?= $stage ?></span><h4><?= $title ?></h4><p><?= e($desc) ?></p></div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- CULTURE (brochure: Culture at iTrend) -->
  <section id="culture">
    <div class="section-label" data-aos="fade-up">Culture at iTrend</div>
    <h2 class="section-title" data-aos="fade-up">More than a workplace — a <em>family.</em></h2>
    <p class="section-desc" data-aos="fade-up" data-aos-delay="50">Our culture is built on innovation, collaboration, and transparency. We embrace agility, encourage every individual to take ownership, and keep open communication at the heart of what we do — so ideas are heard, efforts are recognised, and contributions make a real impact.</p>
    <div class="gallery-block">
      <div class="gallery-grid">
        <?php
          $gallery = [
            ['024A0790.JPG','wide','Team collaboration at the Chennai HQ'],
            ['024A1277.JPG','','Celebrating a successful quarter'],
            ['024A3591.JPG','tall','Working the marketplaces, side by side'],
            ['024A3603.JPG','','Festivals & celebrations together'],
            ['024A6127.JPG','','One team, one vision'],
            ['024A6318.JPG','','Recognition that\'s earned and shared'],
            ['img1.jpg','','Where the work gets done'],
            ['life-1.jpg','','Moments at iTrend'],
            ['life-2.jpg','','Moments at iTrend'],
            ['life-3.jpg','tall','Moments at iTrend'],
          ];
          foreach ($gallery as $i => [$file,$cls,$cap]):
        ?>
          <div class="gallery-item <?= $cls ?>" data-aos="zoom-in" data-aos-delay="<?= ($i%3)*60 ?>" data-caption="<?= e($cap) ?>">
            <span class="zoom-badge"><i class="bi bi-arrows-fullscreen"></i></span>
            <img src="<?= asset('assets/img/careers/' . rawurlencode($file)) ?>" alt="<?= e($cap) ?>" loading="lazy">
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- RECOGNITION (brochure: Recognition at iTrend / Best Performers) -->
  <section id="recognition" class="band">
    <div class="section-label" data-aos="fade-up">Recognition at iTrend</div>
    <h2 class="section-title" data-aos="fade-up">Effort that's <em>noticed</em> &amp; rewarded</h2>
    <p class="section-desc" data-aos="fade-up" data-aos-delay="50">At iTrend, recognition is direct and transparent — every effort is noticed and appreciated. From "Best Performers" awards to everyday shout-outs, your contribution never goes unseen.</p>
    <div class="why-choose-grid">
      <?php foreach ([
        ['bi-trophy','Best Performers','Quarterly awards celebrating the people driving real results.'],
        ['bi-hand-thumbs-up','Direct &amp; Transparent','Recognition tied to contribution — visible and merit-based.'],
        ['bi-rocket-takeoff','Rapid Advancement','Many team leads started as trainees; growth follows impact.'],
      ] as $i => [$icon,$t,$d]): ?>
        <div class="reason-card glass" data-aos="fade-up" data-aos-delay="<?= $i*80 ?>">
          <i class="bi <?= $icon ?>"></i><h4><?= $t ?></h4><p><?= $d ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- JOIN MISSION (brochure: Join iTrend Solution) -->
  <section class="cta-band" id="join">
    <div class="cta-inner glass" data-aos="zoom-in">
      <h2>Join iTrend — Shape the Future of <em>E-Commerce</em></h2>
      <p>We're on a mission to revolutionise e-commerce by empowering brands to sell anywhere, faster. Whether you're a developer, marketer, sales expert, or specialist — there's a place for you to grow and thrive.</p>
      <div class="cta-actions">
        <button class="btn-primary-glow" data-bs-toggle="modal" data-bs-target="#careerModal">Apply Now <span class="arrow">→</span></button>
        <a href="#open-roles" class="btn-ghost">View Open Roles</a>
        <a href="<?= url('/contact') ?>" class="btn-ghost">Contact HR</a>
      </div>
    </div>
  </section>
</div>
