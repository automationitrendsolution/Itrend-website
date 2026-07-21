<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Base controller — view rendering, redirects, JSON responses.
 */
abstract class Controller
{
    protected function view(string $template, array $data = [], string $layout = 'main'): void
    {
        echo View::render($template, $data, $layout);
    }

    protected function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    protected function redirect(string $path, int $status = 302): void
    {
        header('Location: ' . self::safeLocation($path), true, $status);
        exit;
    }

    /**
     * Open-redirect guard. Only same-origin targets are allowed; anything else
     * (external host, protocol-relative //evil.com, CR/LF injection) falls back to "/".
     * Important because the non-JS form fallback redirects to HTTP_REFERER, which the
     * client controls.
     */
    private static function safeLocation(string $target): string
    {
        $target = str_replace(["\r", "\n", "\0"], '', $target);
        if ($target === '') {
            return '/';
        }
        // Relative path like "/contact" (but NOT protocol-relative "//host").
        if ($target[0] === '/' && (!isset($target[1]) || $target[1] !== '/')) {
            return $target;
        }
        // Absolute URL: allow only when the host matches this site.
        $host = parse_url($target, PHP_URL_HOST);
        $self = $_SERVER['HTTP_HOST'] ?? '';
        if ($host !== null && $self !== '' && strcasecmp($host, $self) === 0) {
            return $target;
        }
        return '/';
    }

    /** Flash a one-shot message to the next request. */
    protected function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }
}
