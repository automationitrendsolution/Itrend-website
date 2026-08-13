<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Session hardening, security response headers, and small crypto helpers.
 */
final class Security
{
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $secure = self::isHttps();
        // On HTTPS use the __Host- cookie prefix — the browser then enforces that the
        // cookie is Secure, Path=/ and host-locked, blocking sub-domain/downgrade
        // cookie injection. Falls back to a plain name over plain HTTP (local dev).
        session_name($secure ? '__Host-itrend_sid' : 'itrend_sid');
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();

        // Rotate the id periodically to limit fixation windows.
        if (!isset($_SESSION['_started'])) {
            session_regenerate_id(true);
            $_SESSION['_started'] = time();
        }
    }

    public static function sendHeaders(): void
    {
        if (headers_sent()) {
            return;
        }
        header_remove('X-Powered-By');
        header_remove('Server');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=(), usb=(), browsing-topics=(), interest-cohort=()');
        header('Cross-Origin-Opener-Policy: same-origin');
        // cross-origin: reCAPTCHA pulls its runtime from www.gstatic.com and runs
        // the challenge in a www.google.com iframe; same-origin blocks both.
        header('Cross-Origin-Resource-Policy: cross-origin');
        header('X-Permitted-Cross-Domain-Policies: none');
        // CSP — only the origins the site actually loads (AOS/CSS/JS are self-hosted).
        // object-src 'none' + base-uri 'self' + frame-ancestors 'self' kill the common
        // XSS/clickjacking/base-tag vectors; upgrade-insecure-requests forces HTTPS.
        //
        // google.com/gstatic.com appear in script-/frame-/connect-src only because
        // reCAPTCHA v2 loads its script from www.google.com, pulls its runtime from
        // www.gstatic.com, and renders the checkbox/challenge in an iframe.
        // recaptcha.net is Google's own mirror of the widget; it is used
        // automatically in regions where google.com is unreachable, so it must be
        // allowed alongside google.com or the widget silently fails for those users.
        $recaptcha = Recaptcha::enabled();
        $gScript   = $recaptcha ? ' https://www.google.com https://www.gstatic.com https://www.recaptcha.net' : '';
        $gConnect  = $recaptcha ? ' https://www.google.com https://www.gstatic.com https://www.recaptcha.net' : '';
        $gFrame    = $recaptcha ? ' https://www.google.com https://recaptcha.google.com https://www.recaptcha.net' : '';

        header(
            "Content-Security-Policy: default-src 'self'; "
            . "script-src 'self'" . $gScript . '; '
            . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
            . "font-src 'self' https://fonts.gstatic.com data:; "
            . "img-src 'self' data: https:; media-src 'self'; "
            . "frame-src 'self' https://maps.google.com" . $gFrame . '; '
            . "object-src 'none'; "
            . "connect-src 'self'" . $gConnect . '; '
            . "base-uri 'self'; form-action 'self'; frame-ancestors 'self'; "
            . "upgrade-insecure-requests"
        );
        if (self::isHttps()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }
    }

    public static function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
            || (($_SERVER['SERVER_PORT'] ?? '') === '443');
    }

    /** App-key-salted hash, handy for non-reversible identifiers. */
    public static function hash(string $value): string
    {
        return hash_hmac('sha256', $value, (string) Config::get('APP_KEY', 'itrend-fallback-key'));
    }
}
