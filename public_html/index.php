<?php
/**
 * ============================================================
 *  iTrend Solution — Front Controller
 *  Every dynamic request enters here (see .htaccess rewrite).
 *  This file IS the document-root entry point per project spec.
 * ============================================================
 */

declare(strict_types=1);

// Bootstrap the framework (env, autoloader, sessions, security headers).
require __DIR__ . '/app/bootstrap.php';

use App\Core\Router;

// Route table.
$router = require APP_PATH . '/routes.php';

// Dispatch the current request.
$router->dispatch();
