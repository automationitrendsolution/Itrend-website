<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Csrf;
use App\Core\View;

/** HTML-escape for safe output. Use everywhere user/dynamic data is printed. */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Build an absolute-from-root asset URL (cache-busted by file mtime). */
function asset(string $path): string
{
    $path = '/' . ltrim($path, '/');
    $file = BASE_PATH . $path;
    $version = is_file($file) ? '?v=' . filemtime($file) : '';
    return $path . $version;
}

/** Root-relative URL helper. */
function url(string $path = '/'): string
{
    return '/' . ltrim($path, '/');
}

/** Render the CSRF hidden field. */
function csrf_field(): string
{
    return Csrf::field();
}

function csrf_token(): string
{
    return Csrf::token();
}

/** Include a shared partial. */
function partial(string $name, array $data = []): void
{
    echo View::partial($name, $data);
}

/** Read (and clear) a flashed value from the previous request. */
function flash(string $key, mixed $default = null): mixed
{
    if (!isset($_SESSION['_flash'][$key])) {
        return $default;
    }
    $value = $_SESSION['_flash'][$key];
    unset($_SESSION['_flash'][$key]);
    return $value;
}

function config(string $key, ?string $default = null): ?string
{
    return Config::get($key, $default);
}

/** Active-nav helper: returns "active" when $path matches the current page. */
function nav_active(string $current, string $path): string
{
    return $current === $path ? 'active' : '';
}

/** Site base URL — from APP_URL, falling back to the current host. No trailing slash. */
function site_base(): string
{
    $base = rtrim((string) Config::get('APP_URL', ''), '/');
    if ($base === '') {
        $https  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                  || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $base   = ($https ? 'https' : 'http') . '://' . $host;
    }
    return $base;
}

/** Absolute URL for a root-relative path (used for og:image, canonical, sitemap). */
function abs_url(string $path = '/'): string
{
    return site_base() . '/' . ltrim($path, '/');
}

/** Canonical URL for the current request (path only, no query string). */
function canonical_url(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    return site_base() . $path;
}
