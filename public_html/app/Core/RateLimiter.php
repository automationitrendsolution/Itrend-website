<?php

declare(strict_types=1);

namespace App\Core;

/**
 * IP + action rate limiter using a file ledger in storage/cache.
 * (No database — this is the public landing site.)
 */
final class RateLimiter
{
    public static function tooManyAttempts(string $ip, string $action, int $max, int $windowSeconds): bool
    {
        return self::fileCount($ip, $action, $windowSeconds) >= $max;
    }

    public static function hit(string $ip, string $action): void
    {
        self::fileHit($ip, $action);
    }

    private static function ledgerFile(): string
    {
        return STORAGE_PATH . '/cache/ratelimit.json';
    }

    private static function fileHit(string $ip, string $action): void
    {
        $data = self::readLedger();
        $data[] = ['k' => $ip . '|' . $action, 't' => time()];
        @file_put_contents(self::ledgerFile(), json_encode($data), LOCK_EX);
    }

    private static function fileCount(string $ip, string $action, int $windowSeconds): int
    {
        $key = $ip . '|' . $action;
        $cutoff = time() - $windowSeconds;
        $count = 0;
        foreach (self::readLedger() as $row) {
            if (($row['k'] ?? '') === $key && (int) ($row['t'] ?? 0) >= $cutoff) {
                $count++;
            }
        }
        return $count;
    }

    private static function readLedger(): array
    {
        $file = self::ledgerFile();
        if (!is_file($file)) {
            return [];
        }
        $raw = @file_get_contents($file);
        $data = $raw ? json_decode($raw, true) : [];
        if (!is_array($data)) {
            return [];
        }
        $cutoff = time() - 3600;
        return array_values(array_filter($data, static fn ($r) => (int) ($r['t'] ?? 0) >= $cutoff));
    }
}
