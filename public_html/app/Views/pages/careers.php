<?php /** @var array $jobs */ ?>
<section class="page-hero">
  <div class="page-hero-inner">
    <div class="breadcrumb-nav"><a href="<?= url('/') ?>">Home</a><span class="sep">›</span><span class="current">Careers</span></div>
    <h1 class="page-hero-title">Shape the Future of <em>E-Commerce</em> With Us</h1>
    <p class="page-hero-subtitle">We hire people who want to learn the whole business, not just one slice of it. Freshers and experienced professionals both start with real work, and we train you as you go.</p>
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
    <p class="section-desc" data-aos="fade-up" data-aos-delay="50">The company is large enough that the work matters, and small enough that people notice who did it.</p>
    <div class="why-grid">
      <div class="why-card" data-aos="fade-up">
        <div class="why-icon">🤝</div>
        <h3>Culture</h3>
        <p>We keep things open. Decisions get explained, questions get answered, and nobody has to guess where they stand. We also make time for the non-work part: outings, festivals, and a proper celebration when something goes well.</p>
      </div>
      <div class="why-card" data-aos="fade-up" data-aos-delay="100">
        <div class="why-icon">🧭</div>
        <h3>Management That Backs You</h3>
        <p>Our managers give people room to own their work and step in when it is needed. If you show you can take more on, you will be given it.</p>
      </div>
      <div class="why-card" data-aos="fade-up" data-aos-delay="200">
        <div class="why-icon">📈</div>
        <h3>Professional Growth</h3>
        <p>You learn by doing here, starting in your first week. The company is still growing, which means new responsibilities come up faster than they would somewhere settled.</p>
      </div>
    </div>
  </section>

  <!-- TEAMS YOU CAN JOIN (from brochure department wheel) -->
  <section id="teams">
    <div class="section-label" data-aos="fade-up">Our Teams</div>
    <h2 class="section-title" data-aos="fade-up">Every capability, <em>under one roof.</em></h2>
    <p class="section-desc" data-aos="fade-up" data-aos-delay="50">Eight departments, 60+ people, all in one office. Whatever you do, there is a team here doing it.</p>
    <div class="dept-grid">
      <?php
        $teams = [
          ['💻','IT &amp; Software','Tools, dashboards &amp; automation'],
          ['📦','SCM &amp; Logistics','Sourcing to FBA fulfillment'],
          ['📋','Order Management','Operations &amp; fulfillment'],
          ['💰','Accounts','Finance &amp; payouts'],
          ['🎨','Cataloging &amp; Graphic Design','Listings &amp; A+ content'],
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
          <?php /*
            Applying from a specific role should prefill THAT role. data-role
            carries the job title (what the visitor clicked); data-role-fallback
            carries the department, used only when the title has no matching
            option in the dropdown. Previously only the department was sent, so
            clicking "Full Stack Developer" selected "IT & Software".
          */ ?>
          <button class="job-apply" data-bs-toggle="modal" data-bs-target="#careerModal"
                  data-role="<?= e($job['title']) ?>"
                  data-role-fallback="<?= e($job['dept']) ?>"
                  aria-label="Apply for <?= e($job['title']) ?>">Apply <i class="bi bi-arrow-right"></i></button>
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
        ['Month 0–3','Trainee / Associate','You are on live marketplace work in your first week, with someone senior alongside you.'],
        ['Month 3–12','Specialist','You take a brand, marketplace, or workflow and run it yourself.'],
        ['Year 1–2','Senior Specialist','You become the person others ask, and you start training the newer joiners.'],
        ['Year 2+','Team Lead &amp; Beyond','You run a department, hire your own team, and have a say in where the business goes.'],
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
    <p class="section-desc" data-aos="fade-up" data-aos-delay="50">We eat together, travel together, and mark every festival properly. The work is demanding, but it is not the kind of place where you keep your head down and say nothing.</p>
    <div class="gallery-block">
      <div class="gallery-grid">
        <?php
          /*
            Intrinsic dimensions are declared on every tile. In this masonry
            (CSS multi-column) layout the tiles have no fixed height, so
            without width/height the browser reserves no space: a 0px-tall
            tile never intersects the viewport, loading="lazy" therefore never
            fetches the image, and the gallery stays permanently empty.
            Declaring the real size breaks that deadlock and also removes the
            layout shift as each image arrives.
          */
          $gallery = [
            ['024A0790.JPG','wide','Team collaboration at the Chennai HQ', 1500, 1000],
            ['024A1277.JPG','','Celebrating a successful quarter',         1500, 2250],
            ['024A3591.JPG','tall','Working the marketplaces, side by side',1500, 1000],
            ['024A3603.JPG','','Festivals & celebrations together',        1500, 1000],
            ['024A6127.JPG','','The team at our Chennai office',          1500, 1000],
            ['024A6318.JPG','','Recognition that\'s earned and shared',    1500, 1000],
            ['img1.jpg','','Where the work gets done',                     1500, 1060],
            ['life-1.jpg','','Moments at iTrend',                          1500, 1000],
            ['life-2.jpg','','Moments at iTrend',                          1500, 1000],
            ['life-3.jpg','tall','Moments at iTrend',                      1500, 1000],
          ];
          foreach ($gallery as $i => [$file,$cls,$cap,$w,$h]):
        ?>
          <div class="gallery-item <?= $cls ?>" data-aos="zoom-in" data-aos-delay="<?= ($i%3)*60 ?>" data-caption="<?= e($cap) ?>">
            <span class="zoom-badge"><i class="bi bi-arrows-fullscreen"></i></span>
            <img src="<?= asset('assets/img/careers/' . rawurlencode($file)) ?>" alt="<?= e($cap) ?>"
                 width="<?= $w ?>" height="<?= $h ?>" loading="lazy" decoding="async">
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- RECOGNITION (brochure: Recognition at iTrend / Best Performers) -->
  <section id="recognition" class="band">
    <div class="section-label" data-aos="fade-up">Recognition at iTrend</div>
    <h2 class="section-title" data-aos="fade-up">Effort that's <em>noticed</em> &amp; rewarded</h2>
    <p class="section-desc" data-aos="fade-up" data-aos-delay="50">We hand out Best Performer awards every quarter in front of the whole company, and we say thank you the rest of the time too.</p>
    <div class="why-choose-grid">
      <?php foreach ([
        ['bi-trophy','Best Performers','Given every quarter, in front of everyone.'],
        ['bi-hand-thumbs-up','Direct &amp; Transparent','Based on what you did, and explained so everyone understands why.'],
        ['bi-rocket-takeoff','Rapid Advancement','Most of our team leads started as trainees. That route is still open.'],
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
      <p>We are hiring across every department. If you want to learn how a product company actually works, this is a good place to do it.</p>
      <div class="cta-actions">
        <button class="btn-primary-glow" data-bs-toggle="modal" data-bs-target="#careerModal">Apply Now <span class="arrow">→</span></button>
        <a href="#open-roles" class="btn-ghost">View Open Roles</a>
        <a href="<?= url('/contact') ?>" class="btn-ghost">Contact HR</a>
      </div>
    </div>
  </section>
</div>
