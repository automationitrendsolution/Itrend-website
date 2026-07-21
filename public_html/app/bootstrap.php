<?php
/**
 * Application bootstrap — paths, env, autoloader, session, security.
 */

declare(strict_types=1);

// ---- Paths ----
define('APP_PATH', __DIR__);                       // public_html/app
define('BASE_PATH', dirname(__DIR__));             // public_html  (document root)
define('STORAGE_PATH', BASE_PATH . '/storage');    // public_html/storage
define('VIEW_PATH', APP_PATH . '/Views');

// ---- Optional Composer autoloader (loaded only if `composer install` was run) ----
if (is_file(BASE_PATH . '/vendor/autoload.php')) {
    require BASE_PATH . '/vendor/autoload.php';
}

// ---- Composer-free PSR-4 autoloader for the App\ namespace ----
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = APP_PATH . '/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

// ---- Load confidential configuration (itrend-secrets.json) ----
// Kept ABOVE the document root (in ../workspace) so it is never web-accessible.
// The first readable file wins; real host environment variables override it.
//   1. ../workspace/itrend-secrets.json  — recommended (one level above public_html)
//   2. ../itrend-secrets.json            — directly in the home dir, above public_html
use App\Core\Secrets;
use App\Core\Config;
use App\Core\Security;

$secretsCandidates = [
    dirname(BASE_PATH) . '/workspace/itrend-secrets.json',
    dirname(BASE_PATH) . '/itrend-secrets.json',
];
$secretsFile = '';
foreach ($secretsCandidates as $candidate) {
    if (is_readable($candidate)) {
        $secretsFile = $candidate;
        break;
    }
}
define('SECRETS_FILE', $secretsFile);
Secrets::load($secretsFile);

// ---- Error handling driven by APP_DEBUG ----
$debug = Config::bool('APP_DEBUG', false);
error_reporting(E_ALL);
ini_set('display_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', STORAGE_PATH . '/logs/php_error.log');

// ---- Global exception / error / fatal handler (branded page + area-tagged logs) ----
\App\Core\ErrorHandler::register();

date_default_timezone_set('Asia/Kolkata');

// ---- Helper functions ----
require APP_PATH . '/helpers.php';

// ---- Secure session (CSRF tokens, flash messages) ----
Security::startSession();

// ---- Always-on security headers (defence in depth alongside .htaccess) ----
Security::sendHeaders();
