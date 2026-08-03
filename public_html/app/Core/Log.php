<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Resilient application logger.
 *
 * The whole point of this class is that it must NEVER be the reason a request
 * fails, and it must still capture something when the "normal" log destination
 * is unavailable — which on shared hosting / cPanel is the usual reason a 500
 * arrives with an empty log file.
 *
 * Destination is resolved once, in this order (first writable one wins):
 *   1. storage/logs/           — the intended location
 *   2. ../logs/                — one level above the doc root (some hosts lock storage/)
 *   3. sys_get_temp_dir()      — always writable; survives a bad chmod
 * and the resolved path is exposed via path() so /health can report it.
 *
 * Files are named app-YYYY-MM-DD.log so cPanel's File Manager shows one file
 * per day rather than one ever-growing blob, and old ones are pruned.
 */
final class Log
{
    /** Keep this many days of daily log files; older ones are deleted. */
    private const RETENTION_DAYS = 14;

    /** Refuse to append once a single day's file passes this size (bytes). */
    private const MAX_BYTES = 5_242_880; // 5 MB

    private static ?string $dir = null;
    private static ?string $resolvedFrom = null;

    /**
     * Write one line. $context is rendered as compact JSON when present.
     *
     * @param array<string,mixed> $context
     */
    public static function write(string $level, string $message, array $context = []): void
    {
        $dir = self::dir();
        if ($dir === null) {
            // Nowhere writable at all — fall back to PHP's own error_log so the
            // line still lands in the host's error log rather than vanishing.
            error_log('[itrend] ' . $level . ': ' . self::clean($message));
            return;
        }

        $line = sprintf(
            "[%s] %s: %s%s | %s %s | ip %s%s\n",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            self::clean($message),
            $context !== [] ? ' | ' . self::encodeContext($context) : '',
            $_SERVER['REQUEST_METHOD'] ?? 'CLI',
            self::clean((string) ($_SERVER['REQUEST_URI'] ?? '-')),
            $_SERVER['REMOTE_ADDR'] ?? '-',
            isset($_SERVER['HTTP_USER_AGENT']) ? ' | ua ' . self::clean(substr((string) $_SERVER['HTTP_USER_AGENT'], 0, 120)) : ''
        );

        $file = $dir . '/app-' . date('Y-m-d') . '.log';

        // Size guard: never let a runaway loop fill the account's disk quota.
        if (is_file($file) && filesize($file) > self::MAX_BYTES) {
            return;
        }

        // LOCK_EX keeps concurrent PHP-FPM workers from interleaving lines.
        // Errors are suppressed deliberately: logging must never raise.
        @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }

    public static function error(string $message, array $context = []): void
    {
        self::write('error', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::write('warning', $message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::write('info', $message, $context);
    }

    /** The directory logs are actually being written to, or null if none is writable. */
    public static function path(): ?string
    {
        return self::dir();
    }

    /** Which candidate won — 'storage' | 'above-root' | 'system-temp' | null. Reported by /health. */
    public static function source(): ?string
    {
        self::dir();
        return self::$resolvedFrom;
    }

    /**
     * Resolve (once per request) the first writable log directory.
     * Uses is_writable rather than a trial write so it stays cheap.
     */
    private static function dir(): ?string
    {
        if (self::$dir !== null) {
            return self::$dir !== '' ? self::$dir : null;
        }

        $candidates = [];
        if (defined('STORAGE_PATH')) {
            $candidates['storage'] = STORAGE_PATH . '/logs';
        }
        if (defined('BASE_PATH')) {
            $candidates['above-root'] = dirname(BASE_PATH) . '/logs';
        }
        $candidates['system-temp'] = sys_get_temp_dir() . '/itrend-logs';

        foreach ($candidates as $source => $path) {
            if (!is_dir($path)) {
                // Only try to create the non-storage fallbacks; if storage/logs
                // is missing that's a deployment fault worth surfacing, not hiding.
                if ($source === 'storage' || !@mkdir($path, 0755, true) && !is_dir($path)) {
                    continue;
                }
            }
            if (is_writable($path)) {
                self::$dir = $path;
                self::$resolvedFrom = $source;
                self::prune($path);
                return self::$dir;
            }
        }

        self::$dir = '';  // sentinel: resolved, but nothing writable
        return null;
    }

    /** Delete daily logs older than RETENTION_DAYS. Runs at most once per request. */
    private static function prune(string $dir): void
    {
        $cutoff = time() - (self::RETENTION_DAYS * 86400);
        foreach ((array) @glob($dir . '/app-*.log') as $file) {
            if (is_string($file) && @filemtime($file) < $cutoff) {
                @unlink($file);
            }
        }
    }

    /** @param array<string,mixed> $context */
    private static function encodeContext(array $context): string
    {
        $json = json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
        return self::clean($json === false ? '{"context":"unencodable"}' : $json);
    }

    /** Strip CR/LF/NUL so a crafted value can't forge extra log lines. */
    private static function clean(string $s): string
    {
        return str_replace(["\r", "\n", "\0"], ' ', $s);
    }
}
