<?php
// Local dev router for: php -S localhost:8000 -t public_html public_html/router.php
// (Production uses Apache + .htaccess; this file is only for `php -S`.)
//
// The PHP built-in server does NOT read .htaccess, so the deny rules that
// protect the application directories in production do not apply here. This
// router mirrors those rules so that a local dev session cannot serve source
// code, logs, or secrets — previously `/app/Core/Secrets.php`, `/router.php`
// and `/storage/logs/*.log` were all readable over HTTP in dev.

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';

// Reject traversal outright before any filesystem check.
if (str_contains($path, '..')) {
    http_response_code(404);
    exit;
}

// Mirrors: RedirectMatch 404 ^/(app|storage|vendor|workspace)(/|$)
if (preg_match('#^/(app|storage|vendor|workspace)(/|$)#i', $path)) {
    http_response_code(404);
    exit;
}

// Mirrors the .htaccess <FilesMatch>: dotfiles, the dev router itself,
// secrets, and sensitive file extensions.
$basename = basename($path);
if (
    $basename !== '' && (
        $basename[0] === '.'
        || preg_match('#\.(env|ini|log|sql|md|sh|lock|dist|bak)$#i', $basename)
        || preg_match('#^composer\.(json|lock)$#i', $basename)
        || strcasecmp($basename, 'router.php') === 0
        || preg_match('#^itrend-secrets.*\.json$#i', $basename)
    )
) {
    http_response_code(404);
    exit;
}

if ($path !== '/' && is_file(__DIR__ . $path)) {
    return false; // serve the existing static asset as-is
}
require __DIR__ . '/index.php';
