<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Global error & exception handler.
 *
 * Registers three safety nets so nothing ever reaches the visitor as a white
 * screen or raw stack trace:
 *   1. set_error_handler       — PHP warnings / notices / deprecations (logged, non-fatal)
 *   2. set_exception_handler   — any uncaught Throwable
 *   3. register_shutdown_function — fatal errors (E_ERROR / parse / compile) that
 *                                   bypass the exception handler
 *
 * Every failure is logged WITH ITS AREA — the exact file:line, the request
 * (method + URI), and the client IP — so you can see where it came from. The
 * visitor always gets the branded error page; internal detail is shown only
 * when APP_DEBUG is on.
 */
final class ErrorHandler
{
    private static bool $rendered = false;

    public static function register(): void
    {
        error_reporting(E_ALL);
        // Buffer output so a mid-render failure can be discarded and replaced
        // cleanly with the branded error page.
        if (ob_get_level() === 0) {
            ob_start();
        }
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    /** PHP warnings/notices/deprecations — log the area, don't halt the request. */
    public static function handleError(int $errno, string $errstr, string $errfile = '', int $errline = 0): bool
    {
        // Respect the current error_reporting level and the @-suppression operator.
        if (!(error_reporting() & $errno)) {
            return false;
        }
        self::log('PHP ' . self::levelName($errno), $errstr, $errfile, $errline);

        // In debug, let PHP also surface it (display_errors); in production we've
        // logged it and we swallow it so a benign notice never breaks the page.
        return !Config::bool('APP_DEBUG', false);
    }

    /** Any uncaught exception → log with full context, then the branded 500 page. */
    public static function handleException(\Throwable $e): void
    {
        self::log(
            'EXCEPTION ' . (new \ReflectionClass($e))->getShortName(),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );
        self::renderErrorPage(500, self::detail($e));
    }

    /** Fatal errors (E_ERROR, parse, compile) skip the exception handler — catch them here. */
    public static function handleShutdown(): void
    {
        // Already handled by the exception handler? Don't log/render it twice.
        if (self::$rendered) {
            return;
        }
        $err = error_get_last();
        if ($err === null) {
            return;
        }
        $fatal = E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR;
        if (!($err['type'] & $fatal)) {
            return;
        }
        self::log('FATAL ' . self::levelName($err['type']), $err['message'], $err['file'] ?? '', (int) ($err['line'] ?? 0));
        self::renderErrorPage(500, sprintf(
            '%s: %s @ %s:%d',
            self::levelName($err['type']),
            $err['message'],
            $err['file'] ?? '?',
            (int) ($err['line'] ?? 0)
        ));
    }

    /**
     * Write one structured log line that makes the failure's AREA obvious:
     * timestamp · kind · message · file:line · request · ip  (+ trace for exceptions).
     */
    private static function log(string $kind, string $message, string $file, int $line, ?string $trace = null): void
    {
        $request = ($_SERVER['REQUEST_METHOD'] ?? 'CLI') . ' ' . ($_SERVER['REQUEST_URI'] ?? '-');
        $ip      = $_SERVER['REMOTE_ADDR'] ?? '-';
        $entry   = sprintf(
            '[%s] %s: %s | area %s:%d | request %s | ip %s',
            date('Y-m-d H:i:s'),
            $kind,
            self::clean($message),
            $file !== '' ? $file : '?',
            $line,
            $request,
            $ip
        );
        if ($trace !== null) {
            $entry .= "\n  trace: " . str_replace("\n", "\n  ", $trace);
        }
        error_log($entry);
    }

    /** Render the branded error page (or JSON for API/AJAX), guarded against re-entry. */
    private static function renderErrorPage(int $code, string $detail): void
    {
        if (self::$rendered) {
            return;
        }
        self::$rendered = true;

        $debug = Config::bool('APP_DEBUG', false);

        // Discard any partial output so the error page renders on a clean slate.
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        if (!headers_sent()) {
            http_response_code($code);
        }

        // API / AJAX callers get JSON, not an HTML page.
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $xrw    = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        if (str_contains($accept, 'application/json') || strcasecmp($xrw, 'XMLHttpRequest') === 0) {
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=UTF-8');
            }
            echo json_encode(['ok' => false, 'error' => $debug ? $detail : 'Server error'], JSON_UNESCAPED_SLASHES);
            return;
        }

        try {
            echo View::render('error', [
                'page'        => 'error',
                'title'       => '500 — Something went wrong',
                'description' => 'Something went wrong',
                'code'        => 500,
                'heading'     => 'Something went wrong',
                'message'     => 'An unexpected error occurred on our side. Please try again shortly.',
                'detail'      => $debug ? $detail : null,
            ]);
        } catch (\Throwable $e) {
            // The view system itself failed — fall back to self-contained HTML.
            echo self::fallbackHtml($debug ? $detail . ' | render failed: ' . $e->getMessage() : null);
        }
    }

    private static function detail(\Throwable $e): string
    {
        return sprintf('%s: %s @ %s:%d', (new \ReflectionClass($e))->getShortName(), $e->getMessage(), $e->getFile(), $e->getLine());
    }

    /** Minimal branded page used only if the template engine is unavailable. */
    private static function fallbackHtml(?string $detail): string
    {
        $extra = $detail !== null
            ? '<pre style="max-width:600px;margin:1.5rem auto 0;padding:1rem;background:rgba(200,30,44,.06);border:1px solid rgba(200,30,44,.16);border-radius:10px;color:#b91c1c;text-align:left;overflow:auto;font-size:.8rem;">'
                . htmlspecialchars($detail, ENT_QUOTES, 'UTF-8') . '</pre>'
            : '';
        return '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">'
            . '<meta name="viewport" content="width=device-width,initial-scale=1"><title>500 — iTrend Solution</title></head>'
            . '<body style="margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;text-align:center;'
            . 'font-family:-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:linear-gradient(180deg,#ffffff,#f3eef9);color:#1f2937;padding:2rem;">'
            . '<div><div style="font-size:clamp(4rem,14vw,8rem);font-weight:800;line-height:1;'
            . 'background:linear-gradient(100deg,#7c2e85,#ee942f);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;">500</div>'
            . '<h1 style="font-size:1.6rem;margin:.5rem 0 1rem;">Something went wrong</h1>'
            . '<p style="color:#6b7280;max-width:420px;margin:0 auto 1.5rem;">An unexpected error occurred on our side. Please try again shortly.</p>'
            . '<a href="/" style="display:inline-block;padding:.85rem 1.6rem;border-radius:12px;background:linear-gradient(135deg,#7c2e85,#ee942f);color:#fff;text-decoration:none;font-weight:700;">Back to Home</a>'
            . $extra . '</div></body></html>';
    }

    /** Strip CR/LF so a crafted message can't inject extra log lines. */
    private static function clean(string $s): string
    {
        return str_replace(["\r", "\n"], ' ', $s);
    }

    private static function levelName(int $errno): string
    {
        return match ($errno) {
            E_ERROR, E_USER_ERROR           => 'Error',
            E_WARNING, E_USER_WARNING       => 'Warning',
            E_NOTICE, E_USER_NOTICE         => 'Notice',
            E_PARSE                         => 'Parse Error',
            E_CORE_ERROR, E_COMPILE_ERROR   => 'Fatal Error',
            E_RECOVERABLE_ERROR             => 'Recoverable Error',
            E_DEPRECATED, E_USER_DEPRECATED => 'Deprecated',
            default                         => 'Error (' . $errno . ')',
        };
    }
}
