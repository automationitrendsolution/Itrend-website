<?php
// Local dev router for: php -S localhost:8000 -t public_html public_html/router.php
// (Production uses Apache + .htaccess; this file is only for `php -S`.)
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
if ($path !== '/' && is_file(__DIR__ . $path)) {
    return false; // serve the existing static asset as-is
}
require __DIR__ . '/index.php';
