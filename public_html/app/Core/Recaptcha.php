<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Google reCAPTCHA v2 ("I'm not a robot" checkbox) verification for every
 * public form post.
 *
 * v2 is challenge-based, not score-based: the visitor ticks a widget rendered
 * inside the form, the widget produces a g-recaptcha-response token, and this
 * class asks Google whether that token is genuine. There is no score to
 * threshold — the answer is simply pass or fail.
 *
 * Configuration lives in itrend-secrets.json under "recaptcha" (mapped to
 * RECAPTCHA_* by Secrets/Config):
 *   site_key   — public, rendered into the widget
 *   secret_key — private, used only in this server-side verify call
 *   enabled    — master switch; when false every check passes
 *
 * Fail-open policy: if Google itself is unreachable (timeout, DNS, outage) we
 * let the submission through rather than silently losing a real enquiry. An
 * unticked or forged token is still rejected. CSRF + honeypot + rate limiting
 * remain in place underneath, so this is defence in depth, not the only gate.
 *
 * Note: each token is single-use and expires roughly two minutes after the
 * visitor ticks the box, so the front end resets the widget after every submit.
 */
final class Recaptcha
{
    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';
    private const TIMEOUT = 5;

    /** Reason the last verify() returned false ('' when it passed). */
    private static string $lastError = '';

    /** Configured and switched on? */
    public static function enabled(): bool
    {
        return Config::bool('RECAPTCHA_ENABLED', false)
            && self::siteKey() !== ''
            && self::secretKey() !== '';
    }

    /** Public key for the widget (safe to print in HTML). */
    public static function siteKey(): string
    {
        return trim((string) Config::get('RECAPTCHA_SITE_KEY', ''));
    }

    private static function secretKey(): string
    {
        return trim((string) Config::get('RECAPTCHA_SECRET_KEY', ''));
    }

    public static function lastError(): string
    {
        return self::$lastError;
    }

    /**
     * Verify a widget token against Google's siteverify API.
     *
     * @param string|null $token The g-recaptcha-response value from the form.
     * @param string      $type  Form type, used for log context only.
     * @param string      $ip    Remote IP, passed to Google as remoteip.
     */
    public static function verify(?string $token, string $type, string $ip = ''): bool
    {
        self::$lastError = '';

        if (!self::enabled()) {
            return true; // not configured — behave exactly as before
        }

        $token = is_string($token) ? trim($token) : '';
        if ($token === '') {
            self::$lastError = 'missing-input-response';
            return false;
        }

        $response = self::post([
            'secret'   => self::secretKey(),
            'response' => $token,
            'remoteip' => $ip,
        ]);

        if ($response === null) {
            // Could not reach Google — fail open (see class docblock).
            Log::warning('reCAPTCHA verify unreachable — allowing submission', [
                'form' => $type,
                'ip'   => $ip,
            ]);
            return true;
        }

        $data = json_decode($response, true);
        if (!is_array($data)) {
            Log::warning('reCAPTCHA returned unparseable response — allowing submission', [
                'form' => $type,
            ]);
            return true;
        }

        if (!empty($data['success'])) {
            return true;
        }

        $codes = $data['error-codes'] ?? [];
        $codes = is_array($codes) ? implode(',', array_map('strval', $codes)) : (string) $codes;

        // These mean OUR configuration is broken (wrong/blank secret, malformed
        // request) rather than that the visitor is a bot — never punish a real
        // person for a server-side misconfiguration.
        if (str_contains($codes, 'invalid-input-secret')
            || str_contains($codes, 'missing-input-secret')
            || str_contains($codes, 'bad-request')
        ) {
            Log::error('reCAPTCHA is misconfigured — allowing submission', [
                'form'  => $type,
                'codes' => $codes,
            ]);
            return true;
        }

        self::$lastError = $codes !== '' ? $codes : 'verification-failed';
        return false;
    }

    /**
     * A visitor-friendly message for the reason the last verify() failed.
     * An expired or reused token is a normal thing that happens to real people,
     * so it gets its own wording rather than "you look like a bot".
     */
    public static function failureMessage(): string
    {
        if (str_contains(self::$lastError, 'timeout-or-duplicate')) {
            return 'Your verification expired. Please tick "I\'m not a robot" again and resubmit.';
        }
        return 'Please confirm you are not a robot before submitting.';
    }

    /**
     * POST to Google using cURL, falling back to a stream context when the cURL
     * extension is unavailable (common on locked-down shared hosting).
     *
     * @param array<string,string> $fields
     * @return string|null Raw response body, or null when Google is unreachable.
     */
    private static function post(array $fields): ?string
    {
        $body = http_build_query($fields, '', '&');

        if (function_exists('curl_init')) {
            $ch = curl_init(self::VERIFY_URL);
            if ($ch !== false) {
                curl_setopt_array($ch, [
                    CURLOPT_POST           => true,
                    CURLOPT_POSTFIELDS     => $body,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT        => self::TIMEOUT,
                    CURLOPT_CONNECTTIMEOUT => self::TIMEOUT,
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_SSL_VERIFYHOST => 2,
                    CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
                ]);
                $result = curl_exec($ch);
                $error  = curl_error($ch);
                curl_close($ch);
                if (is_string($result) && $result !== '') {
                    return $result;
                }
                if ($error !== '') {
                    Log::warning('reCAPTCHA cURL error', ['error' => $error]);
                }
                return null;
            }
        }

        $context = stream_context_create([
            'http' => [
                'method'        => 'POST',
                'header'        => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content'       => $body,
                'timeout'       => self::TIMEOUT,
                'ignore_errors' => true,
            ],
            'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
        ]);

        $result = @file_get_contents(self::VERIFY_URL, false, $context);
        return is_string($result) && $result !== '' ? $result : null;
    }
}
