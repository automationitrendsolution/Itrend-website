<?php
/**
 * Shared modals. Forms post to PHP endpoints via fetch (app.js) with CSRF +
 * honeypot. They degrade to a normal POST if JS is disabled.
 */
?>
<!-- Contact / Get Started -->
<div class="modal fade glass-modal-wrap" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <button type="button" class="modal-close" data-bs-dismiss="modal" aria-label="Close"><i class="bi bi-x-lg"></i></button>
    <div>
      <span class="modal-eyebrow">Let's Talk</span>
      <h3 class="modal-title" id="contactModalLabel">Start a Conversation</h3>
      <p class="modal-subtitle">Tell us about your goals — we'll get back within 24 hours.</p>
    </div>
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
        <option>Multiple / Not sure</option>
      </select>
      <textarea name="message" autocomplete="off" rows="4" placeholder="Tell us about your goals"></textarea>
      <div class="form-spacer" aria-hidden="true"></div>
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
  </div></div>
</div>

<!-- Career Application -->
<div class="modal fade glass-modal-wrap" id="careerModal" tabindex="-1" aria-labelledby="careerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <button type="button" class="modal-close" data-bs-dismiss="modal" aria-label="Close"><i class="bi bi-x-lg"></i></button>
    <div>
      <span class="modal-eyebrow">Join the Team</span>
      <h3 class="modal-title" id="careerModalLabel">Apply to iTrend</h3>
      <p class="modal-subtitle">Submit your details and we'll be in touch about open roles that fit.</p>
    </div>
    <form class="modal-form js-form" method="post" action="<?= url('/submit/career') ?>" data-endpoint="<?= url('/submit/career') ?>" enctype="multipart/form-data" novalidate>
      <?= csrf_field() ?>
      <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
      <p class="form-legend"><span class="req">*</span> Required fields</p>
      <div class="form-row">
        <input type="text" name="name" autocomplete="name" placeholder="Full Name *" required>
        <input type="email" name="email" autocomplete="email" placeholder="Email Address *" required>
      </div>
      <div class="form-row">
        <select name="level" autocomplete="off" required>
          <option value="" disabled selected>I am a… *</option>
          <option>Fresher</option>
          <option>Experienced</option>
        </select>
        <input type="text" name="experience" autocomplete="off" placeholder="Years of experience (if any)">
      </div>
      <input type="tel" name="phone" autocomplete="tel" placeholder="Phone Number *" required>
      <select name="role" autocomplete="off" required>
        <option value="" disabled selected>Role of interest *</option>
        <option>Data Analyst</option>
        <option>Full Stack Developer</option>
        <option>IT &amp; Software</option>
        <option>Cataloguing Executive</option>
        <option>Graphic Designer</option>
        <option>Digital Marketing</option>
        <option>SCM &amp; Logistics</option>
        <option>Order Management</option>
        <option>Accounts</option>
        <option>R&amp;D</option>
        <option>Human Resources</option>
        <option>Other</option>
      </select>
      <input type="url" name="linkedin" autocomplete="url" placeholder="LinkedIn / Portfolio URL">
      <label class="file-upload">
        <input type="file" name="resume" accept=".pdf,.doc,.docx" required>
        <span class="file-upload-content">
          <span class="file-upload-icon"><i class="bi bi-cloud-arrow-up-fill"></i></span>
          <span class="file-upload-text">
            <strong>Upload Resume / CV <span class="req">*</span></strong>
            <small>PDF, DOC or DOCX — max 5 MB</small>
          </span>
          <span class="file-upload-action">Browse</span>
        </span>
      </label>
      <textarea name="cover" autocomplete="off" rows="3" placeholder="A short note about yourself"></textarea>
      <div class="form-actions">
        <button type="submit" class="btn-primary-glow">Submit Application <span class="arrow">→</span></button>
        <button type="reset" class="btn-reset"><i class="bi bi-arrow-counterclockwise"></i> Reset</button>
      </div>
    </form>
    <div class="form-success">
      <div class="success-badge" aria-hidden="true">
        <svg class="success-check" viewBox="0 0 52 52" role="img" aria-label="Success">
          <circle class="sc-ring" cx="26" cy="26" r="24"></circle>
          <path class="sc-tick" d="M15 27 l7 7 l15 -16"></path>
        </svg>
      </div>
      <h4>Application sent to our HR team</h4>
      <p>Thank you — our HR team reviews every application personally and will reach out if there's a fit.</p>
      <ul class="success-steps">
        <li><i class="bi bi-check-circle-fill"></i> Delivered to the iTrend HR team</li>
        <li><i class="bi bi-envelope-check"></i> A confirmation email is on its way to you</li>
        <li><i class="bi bi-clock-history"></i> We typically respond within 3–5 working days</li>
      </ul>
    </div>
  </div></div>
</div>
