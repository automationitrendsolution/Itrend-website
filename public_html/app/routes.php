<?php

declare(strict_types=1);

use App\Core\Router;

$router = new Router();

// ---- Public marketing pages ----
$router->get('/',          'HomeController@index');
$router->get('/about',     'PageController@about');
$router->get('/careers',   'PageController@careers');
$router->get('/contact',   'PageController@contact');
$router->get('/privacy',   'PageController@privacy');
$router->get('/terms',     'PageController@terms');

// ---- Health check (uptime monitors) ----
$router->get('/health',    'PageController@health');

// ---- Form endpoints (CSRF-protected, rate-limited) — email to HR only, no database ----
$router->post('/submit/contact',    'FormController@contact');
$router->post('/submit/quote',      'FormController@quote');
$router->post('/submit/career',     'FormController@career');
$router->post('/submit/feedback',   'FormController@feedback');
$router->post('/submit/newsletter', 'FormController@newsletter');

return $router;
