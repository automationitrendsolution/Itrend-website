<?php
/**
 * Standalone branded error renderer for Apache ErrorDocument directives.
 * Kept lightweight & self-contained so it works even if a request never
 * reached the front controller.
 */

declare(strict_types=1);

$code = (int) ($_SERVER['REDIRECT_STATUS'] ?? ($_GET['code'] ?? 500));
if ($code < 400 || $code > 599) {
    $code = 500; // only valid HTTP error statuses (4xx/5xx) are rendered
}

$map = [
    // ---- 4xx — client errors ----
    400 => ['Bad request', 'The request could not be understood by the server.'],
    401 => ['Authentication required', 'You need to be signed in to view this page.'],
    402 => ['Payment required', 'This resource requires a valid payment to access.'],
    403 => ['Access denied', "You don't have permission to access this resource."],
    404 => ['Page not found', "The page you're looking for doesn't exist or has moved."],
    405 => ['Method not allowed', 'That action is not supported on this URL.'],
    406 => ['Not acceptable', "The server can't produce a response matching your request."],
    407 => ['Proxy authentication required', 'You must authenticate with a proxy first.'],
    408 => ['Request timed out', 'The server timed out waiting for the request. Please try again.'],
    409 => ['Conflict', 'The request conflicts with the current state of the resource.'],
    410 => ['Gone', 'This page has been permanently removed and is no longer available.'],
    411 => ['Length required', 'The request must include a valid content length.'],
    412 => ['Precondition failed', "A condition set for the request wasn't met."],
    413 => ['File too large', 'The file you tried to upload exceeds the allowed size.'],
    414 => ['URL too long', 'The address you requested is too long to process.'],
    415 => ['Unsupported media type', "The server doesn't support that file or media format."],
    416 => ['Range not satisfiable', 'The requested range cannot be served.'],
    417 => ['Expectation failed', "The server can't meet the request's expectations."],
    418 => ["I'm a teapot", 'This server refuses to brew coffee — it is, after all, a teapot.'],
    421 => ['Misdirected request', "This server can't produce a response for that request."],
    422 => ['Unprocessable entity', 'The request was well-formed but contains invalid data.'],
    423 => ['Locked', 'The resource you requested is locked.'],
    424 => ['Failed dependency', 'The request failed because a previous request it relied on failed.'],
    425 => ['Too early', 'The server is unwilling to process the request just yet. Please retry.'],
    426 => ['Upgrade required', 'Please switch to a secure or newer protocol to continue.'],
    428 => ['Precondition required', 'This request must be conditional. Please try again.'],
    429 => ['Too many requests', 'You have made too many requests. Please slow down and try again shortly.'],
    431 => ['Headers too large', 'Your request headers are too large to process.'],
    451 => ['Unavailable for legal reasons', 'This resource is unavailable for legal reasons.'],
    419 => ['Session expired', 'Your session expired. Please refresh and try again.'],

    // ---- 5xx — server errors ----
    500 => ['Something went wrong', 'An unexpected error occurred on our side. Please try again shortly.'],
    501 => ['Not implemented', "The server doesn't support the functionality required."],
    502 => ['Bad gateway', 'We received an invalid response from an upstream server. Please try again.'],
    503 => ['Service unavailable', 'We are temporarily offline for maintenance. Please check back soon.'],
    504 => ['Gateway timeout', "An upstream server didn't respond in time. Please try again shortly."],
    505 => ['HTTP version not supported', "The server doesn't support the HTTP version used."],
    506 => ['Configuration error', 'A server configuration issue prevented this request.'],
    507 => ['Insufficient storage', 'The server is out of storage to complete the request.'],
    508 => ['Loop detected', 'The server detected an infinite loop while processing the request.'],
    510 => ['Not extended', 'Further extensions are required to fulfil this request.'],
    511 => ['Network authentication required', 'You need to authenticate to gain network access.'],
];
[$heading, $message] = $map[$code]
    ?? [($code >= 500 ? 'Server error' : 'Request error'), 'An unexpected error occurred. Please try again shortly.'];

http_response_code($code);

try {
    require __DIR__ . '/app/bootstrap.php';
    echo App\Core\View::render('error', [
        'page'        => 'error',
        'title'       => $code . ' — ' . $heading,
        'description' => $heading,
        'code'        => $code,
        'heading'     => $heading,
        'message'     => $message,
        'detail'      => null,
    ]);
} catch (\Throwable $e) {
    // Last-resort minimal HTML if the framework itself is unavailable.
    header('Content-Type: text/html; charset=UTF-8');
    $safe = htmlspecialchars($heading, ENT_QUOTES, 'UTF-8');
    echo "<!doctype html><html><head><meta charset='utf-8'><title>{$code}</title>"
        . "<style>body{font-family:system-ui,sans-serif;background:#0a0e1a;color:#fff;display:flex;"
        . "min-height:100vh;align-items:center;justify-content:center;text-align:center;margin:0}"
        . "h1{font-size:5rem;margin:0}a{color:#f18a21}</style></head><body><div>"
        . "<h1>{$code}</h1><p>{$safe}</p><p><a href='/'>Back to Home</a></p></div></body></html>";
}
