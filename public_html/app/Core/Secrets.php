<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Loads all confidential configuration for the landing site from a single
 * JSON file: workspace/itrend-secrets.json (git-ignored, kept above the web root).
 *
 * Values are mapped into the environment so the rest of the app reads them
 * through Config (Config::get('MAIL_HOST'), etc.) without any changes. Real
 * environment variables (if set by the host) always take precedence.
 */
final class Secrets
{
    private static bool $loaded = false;
    /** @var array<string,mixed> */
    private static array $data = [];

    /** JSON key path → environment key. */
    private const MAP = [
        'app.env'         => 'APP_ENV',
        'app.debug'       => 'APP_DEBUG',
        'app.url'         => 'APP_URL',
        'app.key'         => 'APP_KEY',
        'mail.host'       => 'MAIL_HOST',
        'mail.port'       => 'MAIL_PORT',
        'mail.username'   => 'MAIL_USERNAME',
        'mail.password'   => 'MAIL_PASSWORD',
        'mail.encryption' => 'MAIL_ENCRYPTION',
        'mail.from'       => 'MAIL_FROM',
        'mail.from_name'  => 'MAIL_FROM_NAME',
        'mail.to'         => 'MAIL_TO',
    ];

    public static function load(string $path): void
    {
        if (self::$loaded) {
            return;
        }
        self::$loaded = true;

        if (!is_readable($path)) {
            return; // No secrets file — rely on real environment variables only.
        }
        $json = json_decode((string) file_get_contents($path), true);
        if (!is_array($json)) {
            return;
        }
        self::$data = $json;

        foreach (self::MAP as $dot => $envKey) {
            $value = self::get($dot);
            if ($value === null) {
                continue;
            }
            if (getenv($envKey) !== false) {
                continue; // a real host env var wins
            }
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }
            $str = (string) $value;
            $_ENV[$envKey] = $str;
            putenv("{$envKey}={$str}");
        }
    }

    /** Read a dot-path value (e.g. "mail.host") from the loaded secrets. */
    public static function get(string $dotPath, mixed $default = null): mixed
    {
        $node = self::$data;
        foreach (explode('.', $dotPath) as $segment) {
            if (!is_array($node) || !array_key_exists($segment, $node)) {
                return $default;
            }
            $node = $node[$segment];
        }
        return $node;
    }
}
