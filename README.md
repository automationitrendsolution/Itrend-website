<div align="center">

# iTrend Solution — Marketing Website

**A fast, secure, plain-PHP marketing/landing website.** No database, no Docker — it runs on any standard PHP web host. Form submissions are **emailed to HR** (with the resume attached); nothing is stored on the server.

![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4?logo=php&logoColor=white)
![Framework-free](https://img.shields.io/badge/framework-none%20(vanilla%20MVC)-8e2b8e)
![Hosting](https://img.shields.io/badge/hosting-shared%20%2F%20cPanel-success)
![Status](https://img.shields.io/badge/status-production--ready-success)

</div>

> The HR-Management portal (applicant tracking, onboarding, MongoDB, Docker) is a **separate project** — see `../HR-Management-Website/`.

---

## Table of contents

- [What this is](#what-this-is)
- [Architecture](#architecture)
- [Tech stack](#tech-stack)
- [Requirements](#requirements)
- [Project structure](#project-structure)
- [Configuration](#configuration-workspaceitrend-secretsjson)
- [Deploying to shared hosting / cPanel](#deploying-to-shared-hosting--cpanel)
- [Running locally](#running-locally-no-docker)
- [Security](#security)

---

## What this is

- Public pages: **Home, About, Careers, Contact, Privacy, Terms**.
- Public forms: **Contact, Apply Now (careers + resume), Feedback, Quote, Newsletter**.
- Each form is **CSRF-protected, honeypot-guarded, and IP rate-limited**, then **emailed to HR** over SMTP. The career form's resume is **attached to the email**. The temporary upload is deleted right after sending — **this site keeps no database and no permanent files.**

---

## Architecture

A tiny, framework-free **MVC** front controller — every request enters through `index.php`, is routed to a controller, and renders a view. The only persistent state is a small file-based rate-limit ledger.

```
                ┌──────────────────────────────────────────────┐
  Visitor ─────►│  Apache + .htaccess  (clean URLs, CSP, HSTS)  │
                └───────────────┬──────────────────────────────┘
                                │  all requests → public_html/index.php
                                ▼
                ┌──────────────────────────────────────────────┐
                │  bootstrap.php → Secrets::load(JSON) → session │
                │  Router → Controller → View                    │
                └───────────────┬──────────────────────────────┘
            page render ────────┤
            form POST ──────────▼
                ┌──────────────────────────────────────────────┐
                │  FormController: CSRF · honeypot · rate-limit  │
                │  · validate · (stage resume in temp)           │
                │            → Mailer (SMTP) → HR inbox           │
                │            → delete temp file                  │
                └──────────────────────────────────────────────┘
```

**How a submission becomes an email**

1. The visitor submits a form (AJAX) → `POST /submit/{type}`.
2. `FormController` runs the pipeline: **CSRF → honeypot → rate-limit → validation**.
3. For a job application, the resume is validated and staged in the **system temp dir** (never a public folder).
4. The submission is **emailed to `MAIL_TO`** (HR) via the built-in SMTP `Mailer` (resume attached), with the sender's address as `Reply-To`.
5. The visitor is **auto-acknowledged** — a confirmation email is sent **from `hr@itrendsolution.com` to the address they entered** (no resume, no internal data). Skipped when no valid email was supplied (e.g. feedback without one).
6. The temp resume is **deleted immediately** — nothing is stored. The visitor sees a success toast.

> If SMTP isn't configured, the form still validates and shows success — it simply won't deliver until you fill in `workspace/itrend-secrets.json`.

---

## Tech stack

| Layer | Technology |
|-------|------------|
| Language | PHP 8.1+ — custom MVC (Router · Controller · View · Request); `spl_autoload`, **no Composer** |
| Front-end | HTML5, CSS3, vanilla JS, Bootstrap, jQuery, AOS scroll-reveal |
| Email | Built-in dependency-free SMTP client (STARTTLS / SSL), with file attachments |
| Web server | Apache (`mod_rewrite` + `mod_headers`) or any PHP host |
| Persistence | **None** — file-based rate-limit ledger only |

---

## Requirements

- **PHP 8.1+** with the standard `fileinfo` extension (built in). No Composer, no extensions to install, no database.
- Any host that runs PHP: shared hosting, cPanel, Apache, Nginx+PHP-FPM, etc.

---

## Project structure

```
iTrend-Solution-Website/
├── public_html/            # 📂 THIS is your cPanel document root — upload its CONTENTS to public_html/
│   ├── index.php           # Front controller
│   ├── .htaccess           # Clean URLs + security headers (Apache/cPanel)
│   ├── router.php          # Local-dev router for `php -S` only (blocked in production)
│   ├── app/                # 🔒 not web-accessible (blocked by .htaccess)
│   │   ├── bootstrap.php    # Paths, env, session, security headers
│   │   ├── routes.php       # Pages + /submit/* form endpoints
│   │   ├── Core/            # Router, Controller, View, Request, Config, Csrf, Security, RateLimiter, Mailer, Validator
│   │   ├── Controllers/     # Home, Page, Form
│   │   └── Views/           # pages/ · partials/ · layouts/main.php
│   ├── assets/              # css/ · js/ · img/
│   └── storage/             # logs/ · cache/ (rate-limit ledger) — must be writable
└── workspace/              # 🔒 secrets location — upload ABOVE public_html (git-ignored)
    ├── itrend-secrets.json          # APP + MAIL settings (real file — never committed)
    └── itrend-secrets.example.json  # committed template
```

The app finds its secrets file in this order (first readable one wins):

1. `../workspace/itrend-secrets.json` — one level **above** `public_html` (recommended on cPanel — not web-accessible).
2. `../itrend-secrets.json` — directly in your home directory, above `public_html`.

---

## Configuration (`workspace/itrend-secrets.json`)

All confidential settings (SMTP credentials, etc.) live in a **single JSON file**: `itrend-secrets.json`. Keep it **above** the document root (not web-accessible). Copy the template and fill it in:

```bash
cp workspace/itrend-secrets.example.json workspace/itrend-secrets.json
```

```jsonc
{
  "app":  { "env": "production", "debug": false, "url": "https://www.itrendsolution.com",
            "key": "<64-hex-random — generate with: php -r \"echo bin2hex(random_bytes(32));\">" },
  "mail": {
    "host": "smtp.yourhost.com",   // SMTP server — leave "" to disable email
    "port": 587,
    "username": "you@itrendsolution.com",
    "password": "••••••••",
    "encryption": "tls",            // tls | ssl | ''
    "from": "no-reply@itrendsolution.com",
    "from_name": "iTrend Solution",
    "to": "hr@itrendsolution.com",  // the HR inbox that receives submissions
    "noreply": "no-reply@itrendsolution.com"
  }
}
```

> Leave `mail.host` empty to disable email — forms still validate and show success, they just won't deliver. Fill it in to start receiving submissions (with the resume attached). Real host environment variables (e.g. `MAIL_HOST`) still override the file if set.

---

## Deploying to shared hosting / cPanel

This project is laid out to drop straight into a cPanel / shared-hosting account.

**1. Upload the site files.** Copy **everything inside `public_html/`** into your account's `public_html/` (the folder names map 1:1). The quickest way:

- In cPanel **File Manager**, open `public_html`, click **Upload**, and upload a ZIP of the `public_html/` contents, then **Extract** it. Make sure the hidden `.htaccess` file is included — File Manager shows it under *Settings → Show Hidden Files*.
- Or use FTP/SFTP (FileZilla) and drag the contents of `public_html/` into the remote `public_html/`.

**2. Add your secret settings.** Upload the `workspace/` folder **one level above** `public_html` (i.e. into your home directory, alongside `public_html`), then copy `itrend-secrets.example.json` → `itrend-secrets.json` and fill in your SMTP details. It is then *outside* the web root and cannot be downloaded.

**3. Make `storage/` writable.** In File Manager, set `public_html/storage/` and its `logs/` + `cache/` subfolders to **0755** (or 0775 if your host needs it). This is where the rate-limit ledger and error log are written.

**4. Point your domain.** On a primary domain this is automatic (the domain already serves `public_html`). For an addon/subdomain installed in a subfolder, uncomment and set `RewriteBase /your-subfolder/` in `.htaccess`, and set `app.url` in your secrets file.

**5. Verify with `/health`.** Visit `https://yourdomain.com/health` — it returns a JSON report of every requirement (see below).

> No Composer, no database, no build step — it runs on stock cPanel PHP 8.1+. The `app/`, `storage/`, `workspace/` folders and `router.php` are all blocked from direct web access by `.htaccess`.

### Post-upload checklist

Tick these off after uploading — `/health` confirms most of them automatically:

- [ ] **Homepage loads** — `https://yourdomain.com/` shows the site.
- [ ] **Clean URLs work** — `https://yourdomain.com/contact` loads (not a 404). If it 404s, your host needs `AllowOverride All` / `mod_rewrite` enabled.
- [ ] **`/health` reports `"status":"ok"`** — visit `https://yourdomain.com/health`.
- [ ] **Secrets are hidden** — `https://yourdomain.com/workspace/itrend-secrets.json` and `/app/bootstrap.php` should both return **404/403**, never your file contents.
- [ ] **SMTP password is set** — in `itrend-secrets.json`, `mail.password` is the **cPanel mailbox password** for `hr@itrendsolution.com` (Email Accounts → Manage). Everything else is pre-filled for cPanel mail (`mail.itrendsolution.com:465` SSL). If 465/SSL is blocked, try `port 587` + `"encryption":"tls"`.
- [ ] **SPF + DKIM are green** — cPanel → **Email Deliverability** → Repair, so the *candidate acknowledgement* (sent to external Gmail/Outlook addresses) lands in the inbox, not spam.
- [ ] **A test submission arrives** — send a message via the Contact form and confirm **(a)** HR receives it at `mail.to` (résumé attached for job applications) **and (b)** the address you submitted with receives the acknowledgement from `hr@`.

### Reading `/health`

```jsonc
{
  "status": "ok",                 // "ok" = ready; "check-config" = something below needs attention
  "checks": {
    "php":     "8.1.27",          // your host's PHP version (needs 8.1+)
    "mail":    "configured",      // "not-configured" → fill in mail.host in itrend-secrets.json
    "storage": "writable",        // "not-writable"   → chmod public_html/storage to 0755/0775
    "secrets": "found"            // "none"            → secrets file not found above public_html
  },
  "time": "2026-06-18T12:00:00+05:30"
}
```

| Field | Good value | If it's wrong |
|-------|-----------|---------------|
| `php` | `8.1`+ | cPanel → **Select PHP Version** → choose 8.1 or newer |
| `mail` | `configured` | Edit `workspace/itrend-secrets.json` → set `mail.host`, `username`, `password` |
| `storage` | `writable` | File Manager → set `public_html/storage` (+ `logs/`, `cache/`) to **0755** |
| `secrets` | `found` | Put `itrend-secrets.json` in `workspace/` one level **above** `public_html` |

`status` is `ok` only when `storage` is writable **and** `mail` is configured — i.e. when the site can actually deliver submissions.

---

## Running locally (no Docker)

PHP's built-in server on **port 5050**, using the included dev router:

```bash
php -S 0.0.0.0:5050 -t public_html public_html/router.php
```

Then open <http://localhost:5050>. (`router.php` serves static files directly and sends everything else through `index.php`; it's only used by `php -S` — production uses `.htaccess`. A custom domain can be pointed at the docroot later.)

---

## Security

- **No database & no stored data** — the smallest possible attack surface.
- CSRF synchronizer tokens (`random_bytes` + `hash_equals`), honeypot fields, and per-IP rate-limiting (file-based ledger) on every form.
- **Hardened session cookie** — `HttpOnly`, `SameSite=Lax`, `Secure` over HTTPS, id regenerated on start, and the `__Host-` cookie prefix on HTTPS (host-locked, downgrade-proof).
- **Strict security headers** (in `.htaccess` *and* `app/Core/Security.php`): a tight **Content-Security-Policy** scoped to only the origins actually used (`object-src 'none'`, `base-uri 'self'`, `frame-ancestors 'self'`, `upgrade-insecure-requests`), **HSTS** (`includeSubDomains; preload`), `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy` (camera/mic/geo/USB/topics off), `Cross-Origin-Opener/Resource-Policy`, and `X-Permitted-Cross-Domain-Policies: none`.
- All vendor assets (AOS, CSS, JS) are **self-hosted** — no third-party script execution beyond pinned Bootstrap/jQuery CDNs.
- `APP_KEY` (64-hex) salts the HMAC helper — set it in your secrets file (a hardcoded fallback is used only if unset).
- Secrets live only in `workspace/itrend-secrets.json`, above the web root and git-ignored; the `app/`, `storage/`, `workspace/` folders and `router.php` are blocked from the web by `.htaccess`.
- Resume uploads are validated (type/size), staged in the system temp dir, emailed, then immediately deleted — the site keeps no uploads folder.
- **No file-inclusion surface (RFI/LFI)** — the autoloader, router, and view layer only ever load **fixed, code-defined paths**; nothing user-supplied is `include`/`require`d. As defence in depth, `.htaccess` rejects classic probe signatures (`php://`, `../`, `/etc/passwd`, null bytes) and non-`GET/HEAD/POST` methods, and `.user.ini` turns off `allow_url_fopen`/`allow_url_include` (also set these in cPanel **MultiPHP INI Editor**, as they're `PHP_INI_SYSTEM`).
- **Mail-header-injection guarded** — every address/subject placed into the SMTP envelope is stripped of CR/LF/NUL, and recipient addresses are `FILTER_VALIDATE_EMAIL`-checked, so a crafted form value can't inject extra headers or extra recipients.

---

## Updating this README

Update the **Features**, **Configuration**, or **Deploying** sections whenever forms, env keys, or hosting steps change. Document new secrets keys (never paste real values), and commit README changes together with the code they describe.

---

<div align="center">© iTrend Solution — marketing website.</div>
