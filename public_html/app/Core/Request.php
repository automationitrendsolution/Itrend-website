<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Lightweight wrapper around the incoming HTTP request.
 */
final class Request
{
    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    /** Normalised path, no query string, no trailing slash (except root). */
    public function path(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = rawurldecode($path);
        $path = '/' . trim($path, '/');
        return $path === '' ? '/' : $path;
    }

    public function input(string $key, ?string $default = null): ?string
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? $default;
        return is_string($value) ? trim($value) : $default;
    }

    public function all(): array
    {
        return $_POST;
    }

    /**
     * Read a value as an array from POST or GET (e.g. checkbox groups: name="status[]").
     * Accepts a single scalar too, returning it as a one-element array.
     * @return array<int,string>
     */
    public function arr(string $key): array
    {
        $v = $_POST[$key] ?? $_GET[$key] ?? [];
        if (is_string($v)) { return $v === '' ? [] : [$v]; }
        if (!is_array($v)) { return []; }
        return array_values(array_filter($v, 'is_string'));
    }

    public function file(string $key): ?array
    {
        return $_FILES[$key] ?? null;
    }

    public function ip(): string
    {
        // We sit behind Apache only; trust REMOTE_ADDR (don't trust client headers).
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public function userAgent(): string
    {
        return substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
    }

    public function expectsJson(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $xhr = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
        return $xhr || str_contains($accept, 'application/json');
    }
}
