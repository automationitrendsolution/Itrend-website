<section class="page-hero">
  <div class="page-hero-inner">
    <div class="breadcrumb-nav"><a href="<?= url('/') ?>">Home</a><span class="sep">›</span><span class="current">Contact</span></div>
    <h1 class="page-hero-title">Let's start a <em>conversation</em></h1>
    <p class="page-hero-subtitle">Products, partnerships, demos, or careers — tell us what you need and we'll get back within 24 hours.</p>
  </div>
</section>

<div class="dark-canvas">
  <div class="orb orb-1"></div>
  <div class="orb orb-2"></div>
  <div class="orb orb-3"></div>

  <section>
    <div class="contact-grid">
      <div class="contact-info" data-aos="fade-up">
        <div class="contact-card glass">
          <span class="c-icon-wrap"><i class="bi bi-geo-alt-fill"></i></span>
          <div class="cc-body">
            <h5>Head Office</h5>
            <p>Tower A, 3rd Floor, Tek Meadows Campus,<br>51, Rajiv Gandhi Salai (OMR),<br>Sholinganallur, Chennai, Tamil Nadu 600119</p>
          </div>
        </div>
        <div class="contact-card glass">
          <span class="c-icon-wrap"><i class="bi bi-envelope-fill"></i></span>
          <div class="cc-body">
            <h5>Email Us</h5>
            <a href="mailto:hr@itrendsolution.com">hr@itrendsolution.com</a>
          </div>
        </div>
        <div class="contact-card glass">
          <span class="c-icon-wrap"><i class="bi bi-globe2"></i></span>
          <div class="cc-body">
            <h5>Global Operations</h5>
            <p>USA · UK · Canada · Germany<br>Italy · Brazil · India · China</p>
          </div>
        </div>
        <div class="contact-quick">
          <button class="btn-ghost" data-bs-toggle="modal" data-bs-target="#careerModal">Apply for a Job</button>
        </div>
      </div>

      <div class="contact-form-card glass" data-aos="fade-up" data-aos-delay="80">
        <span class="modal-eyebrow">Send a message</span>
        <h3 class="modal-title">Tell us about your goals</h3>
        <div class="contact-title-gap" aria-hidden="true"></div>
        <form class="modal-form js-form" method="post" action="<?= url('/submit/contact') ?>" data-endpoint="<?= url('/submit/contact') ?>" novalidate>
          <?= csrf_field() ?>
          <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
          <div class="form-row">
            <input type="text" name="name" autocomplete="name" placeholder="Full Name" required>
            <input type="email" name="email" autocomplete="email" placeholder="Email Address" required>
          </div>
          <input type="text" name="company" autocomplete="organization" placeholder="Company / Brand">
          <select name="service" autocomplete="off" required>
            <option value="" disabled selected>Area of Interest</option>
            <option>Product Demo</option>
            <option>Marketplace Management</option>
            <option>Technology &amp; Platform</option>
            <option>Partnership</option>
            <option>Investor Relations</option>
            <option>Careers</option>
            <option>Multiple / Not sure</option>
          </select>
          <textarea name="message" autocomplete="off" rows="5" placeholder="How can we help?"></textarea>
          <div class="form-actions">
            <button type="submit" class="btn-primary-glow">Send Message <span class="arrow">→</span></button>
            <button type="reset" class="btn-reset"><i class="bi bi-arrow-counterclockwise"></i> Reset</button>
          </div>
          <p class="terms">By submitting, you agree to our privacy policy.</p>
        </form>
        <div class="form-success">
          <div class="success-badge" aria-hidden="true">
            <svg class="success-check" viewBox="0 0 52 52" role="img" aria-label="Success">
              <circle class="sc-ring" cx="26" cy="26" r="24"></circle>
              <path class="sc-tick" d="M15 27 l7 7 l15 -16"></path>
            </svg>
          </div>
          <h4>Message sent to our HR team</h4>
          <p>Thanks for reaching out — we'll get back to you shortly.</p>
          <ul class="success-steps">
            <li><i class="bi bi-check-circle-fill"></i> Delivered to the iTrend HR team</li>
            <li><i class="bi bi-envelope-check"></i> A confirmation email is on its way to you</li>
            <li><i class="bi bi-clock-history"></i> We usually reply within 24 hours</li>
          </ul>
        </div>
      </div>
    </div>

    <div class="map-embed" data-aos="fade-up">
      <iframe
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3889.020624955606!2d80.2268208!3d12.906395250000001!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3a525c7b8054a399%3A0x554139191b13e304!2sTEK%20MEADOWS%2C%20OMR%2C%20Elcot%20Sez%2C%20Sholinganallur%2C%20Chennai%2C%20Tamil%20Nadu%20600119!5e0!3m2!1sen!2sin!4v1779799278374!5m2!1sen!2sin"
        loading="lazy" allowfullscreen referrerpolicy="no-referrer-when-downgrade" title="iTrend Solution — Chennai"></iframe>
    </div>
  </section>
</div>
