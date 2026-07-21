<?php /** @var string $heading @var string $updated @var string $doc — shared Privacy / Terms page */ ?>
<section class="page-hero">
  <div class="page-hero-inner">
    <div class="breadcrumb-nav"><a href="<?= url('/') ?>">Home</a><span class="sep">›</span><span class="current"><?= e($heading) ?></span></div>
    <h1 class="page-hero-title"><?= e($heading) ?></h1>
    <p class="page-hero-subtitle">Last updated: <?= e($updated) ?></p>
  </div>
</section>

<div class="dark-canvas">
  <div class="orb orb-1"></div>
  <div class="orb orb-2"></div>
  <section class="legal-doc">
    <div class="legal-card glass" data-aos="fade-up">
    <?php
      // Single source of truth for company identity (kept consistent with the Company Brochure & letterhead).
      $company  = 'iTrend Solution';
      $address  = 'Tower A, 3rd Floor, Tek Meadows Campus, 51, Rajiv Gandhi Salai (OMR), Sholinganallur, Chennai, Tamil Nadu 600119, India';
      $email    = 'hr@itrendsolution.com';
      $brochure = asset('assets/downloads/iTrend-Company-Brochure-2025.pdf');
    ?>
    <?php if ($doc === 'privacy'): ?>
      <p><strong><?= e($company) ?></strong> ("we", "us", "our") respects your privacy. This Privacy Policy explains what information we collect through this website, how we use and protect it, and the choices available to you. It applies to <strong>itrendsolution.com</strong> and any forms or services offered on it.</p>

      <h3>1. Who we are</h3>
      <p><?= e($company) ?> is a product-based commerce and technology company. Our registered office is at <?= e($address) ?>. For an overview of our products and capabilities, see our <a href="<?= e($brochure) ?>" target="_blank" rel="noopener">Company Brochure</a>.</p>

      <h3>2. Information we collect</h3>
      <ul>
        <li><strong>Information you provide</strong> — when you submit a contact, feedback, or job-application form: your name, email, phone, company, role/subject, message, rating, and (for applications) your resume and supporting documents.</li>
        <li><strong>Technical information</strong> — basic data such as your IP address, browser type, and timestamp, collected to secure our forms and prevent abuse.</li>
      </ul>

      <h3>3. How we use your information</h3>
      <ul>
        <li>To respond to your enquiry, review your job application, or act on your feedback.</li>
        <li>To carry out recruitment and onboarding for shortlisted candidates.</li>
        <li>To protect our forms and services from spam, fraud, and abuse.</li>
        <li>To comply with applicable legal and regulatory obligations.</li>
      </ul>

      <h3>4. How we protect it</h3>
      <p>When you submit a form on this website, the details (and any attached resume) are <strong>emailed directly to our HR team over a secure connection</strong>. This website itself does not retain a database of submissions. <strong>We do not sell your personal information.</strong></p>

      <h3>5. Data retention</h3>
      <p>Submissions received by email are retained by our HR team only as long as necessary for the purpose they were sent (for example, to evaluate a candidate), or as required by law, after which they are deleted.</p>

      <h3>6. Sharing &amp; third parties</h3>
      <p>We do not share your personal information with third parties except where necessary to operate our services (e.g. secure hosting / email delivery), to comply with the law, or with your consent.</p>

      <h3>7. Cookies</h3>
      <p>This website uses only essential cookies required for session management and security (such as the login session and CSRF protection). We do not use cookies for advertising.</p>

      <h3>8. Your rights</h3>
      <p>You may request access to, correction of, or deletion of your information at any time by emailing <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a>. We will respond within a reasonable period.</p>

      <h3>9. Changes to this policy</h3>
      <p>We may update this policy from time to time. The "Last updated" date above reflects the latest revision; significant changes will be highlighted on this page.</p>

      <h3>10. Contact</h3>
      <p>Questions about this policy? Write to us at <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a>, visit our <a href="<?= url('/contact') ?>">contact page</a>, or reach us at <?= e($address) ?>.</p>
    <?php else: ?>
      <p>These Terms of Service ("Terms") govern your access to and use of the <strong><?= e($company) ?></strong> website at <strong>itrendsolution.com</strong>. By accessing or using this site, you agree to these Terms. If you do not agree, please do not use the site.</p>

      <h3>1. About us</h3>
      <p><?= e($company) ?> is a product-based commerce and technology company with its registered office at <?= e($address) ?>. To learn more about our products and services, download our <a href="<?= e($brochure) ?>" target="_blank" rel="noopener">Company Brochure</a>.</p>

      <h3>2. Use of the site</h3>
      <p>You may browse and use this website for lawful purposes only. You agree not to disrupt the site, probe or breach its security, scrape its content, or submit fraudulent, unlawful, or abusive content through our forms.</p>

      <h3>3. Intellectual property</h3>
      <p>All content on this site — including text, graphics, logos, imagery, the Company Brochure, and the <?= e($company) ?> brand — is owned by or licensed to <?= e($company) ?> and is protected by applicable intellectual-property laws. It may not be copied, reproduced, or redistributed without our prior written permission.</p>

      <h3>4. Submissions &amp; recruitment</h3>
      <p>Information you submit through our forms must be accurate and your own. We handle it as described in our <a href="<?= url('/privacy') ?>">Privacy Policy</a>. Job applications and any onboarding steps are reviewed at our sole discretion and <strong>do not constitute an offer of employment</strong> unless confirmed in writing through a formal letter of appointment.</p>

      <h3>5. Disclaimer of warranties</h3>
      <p>This website and its content are provided on an "as is" and "as available" basis. While we work to keep information accurate and current, we make no warranties as to its completeness, reliability, or uninterrupted availability.</p>

      <h3>6. Limitation of liability</h3>
      <p>To the fullest extent permitted by law, <?= e($company) ?> shall not be liable for any indirect, incidental, or consequential damages arising from your use of, or inability to use, this website.</p>

      <h3>7. Governing law</h3>
      <p>These Terms are governed by the laws of India, and any disputes shall be subject to the exclusive jurisdiction of the courts of Chennai, Tamil Nadu.</p>

      <h3>8. Changes to these terms</h3>
      <p>We may revise these Terms at any time. Continued use of the site after changes are posted constitutes your acceptance of the revised Terms.</p>

      <h3>9. Contact</h3>
      <p>Questions about these Terms? Write to us at <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a> or at <?= e($address) ?>.</p>
    <?php endif; ?>
    </div>
  </section>
</div>
