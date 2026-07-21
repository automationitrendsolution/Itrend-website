<?php

declare(strict_types=1);

namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Email sender built on PHPMailer over SMTP (e.g. Gmail SMTP: smtp.gmail.com).
 * Enabled only when MAIL_HOST is configured; otherwise send() is a no-op that
 * reports false so callers know nothing was emailed. Credentials come from
 * itrend-secrets.json (mapped to MAIL_* by Secrets/Config).
 *
 * PHPMailer is self-hosted under app/lib/PHPMailer/src (no Composer needed).
 */
final class Mailer
{
    /** Human-readable reason the most recent send() failed ('' when it succeeded). */
    private static string $lastError = '';

    public static function enabled(): bool
    {
        return (string) Config::get('MAIL_HOST', '') !== '';
    }

    /** Why the last send() returned false — for diagnostics / logging. */
    public static function lastError(): string
    {
        return self::$lastError;
    }

    /**
     * @param array{path:string,name:string}|null $attachment Optional file to attach (e.g. a resume).
     */
    public static function send(string $subject, string $htmlBody, ?string $replyTo = null, ?string $toOverride = null, ?array $attachment = null, ?string $fromNameOverride = null): bool
    {
        if (!self::enabled()) {
            self::$lastError = 'MAIL_HOST is not configured';
            return false;
        }
        self::$lastError = '';
        self::requirePhpMailer();

        // The From address must stay the authenticated SMTP account (Gmail rejects any
        // other sender). We CAN vary the display name — e.g. show the person who filled
        // the form — while their actual address rides in Reply-To below.
        $from     = (string) Config::get('MAIL_FROM', 'hr@itrendsolution.com');
        $fromName = ($fromNameOverride !== null && trim($fromNameOverride) !== '')
            ? trim($fromNameOverride)
            : (string) Config::get('MAIL_FROM_NAME', 'iTrend Website');
        $to       = ($toOverride && filter_var($toOverride, FILTER_VALIDATE_EMAIL))
            ? $toOverride
            : (string) Config::get('MAIL_TO', $from);

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = (string) Config::get('MAIL_HOST');
            $mail->Port       = Config::int('MAIL_PORT', 587);
            $mail->Username   = (string) Config::get('MAIL_USERNAME', '');
            $mail->Password   = (string) Config::get('MAIL_PASSWORD', '');
            $mail->SMTPAuth   = $mail->Username !== '';

            // "ssl" → implicit TLS on 465; anything else → STARTTLS (587, Gmail's default).
            $enc = strtolower((string) Config::get('MAIL_ENCRYPTION', 'tls'));
            $mail->SMTPSecure = $enc === 'ssl'
                ? PHPMailer::ENCRYPTION_SMTPS
                : PHPMailer::ENCRYPTION_STARTTLS;

            $mail->Timeout = 15;
            $mail->CharSet = PHPMailer::CHARSET_UTF8;

            $mail->setFrom($from, $fromName);
            $mail->addAddress($to);
            if ($replyTo !== null && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
                // So HR can reply straight to the person who submitted the form.
                $mail->addReplyTo($replyTo);
            }

            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body    = $htmlBody;
            $mail->AltBody = self::htmlToText($htmlBody);

            if (is_array($attachment) && !empty($attachment['path']) && is_file($attachment['path'])) {
                $name = preg_replace('/[\r\n"]/', '', (string) ($attachment['name'] ?? basename($attachment['path'])));
                $mail->addAttachment($attachment['path'], (string) $name);
            }

            $mail->send();
            return true;
        } catch (\Throwable $e) {
            // PHPMailer's ErrorInfo is the most descriptive; fall back to the exception.
            self::$lastError = ($mail->ErrorInfo !== '') ? $mail->ErrorInfo : $e->getMessage();
            error_log('[mail] ' . self::$lastError);
            return false;
        }
    }

    /** Load the self-hosted PHPMailer classes once (no Composer autoloader in this project). */
    private static function requirePhpMailer(): void
    {
        if (class_exists(PHPMailer::class, false)) {
            return;
        }
        $dir = __DIR__ . '/../lib/PHPMailer/src/';
        require_once $dir . 'Exception.php';
        require_once $dir . 'PHPMailer.php';
        require_once $dir . 'SMTP.php';
    }

    /**
     * Derive a readable plain-text fallback from the HTML body (used as AltBody so
     * the email has both an HTML and a text part for strict clients/spam filters).
     */
    private static function htmlToText(string $html): string
    {
        $text = preg_replace('#<(style|script)\b[^>]*>.*?</\1>#is', '', $html) ?? $html;
        $text = preg_replace('#<br\s*/?>#i', "\n", $text) ?? $text;
        $text = preg_replace('#</td>#i', ' ', $text) ?? $text;
        $text = preg_replace('#</(p|div|h[1-6]|tr|li|table)>#i', "\n", $text) ?? $text;
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[ \t]+/', ' ', $text) ?? $text;
        $text = preg_replace('/ *\n */', "\n", $text) ?? $text;
        $text = preg_replace('/\n{3,}/', "\n\n", $text) ?? $text;
        return trim($text);
    }
}
