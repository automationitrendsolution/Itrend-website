<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Synchronizer-token CSRF protection. One token per session, rotated on demand.
 */
final class Csrf
{
    private const KEY = '_csrf_token';

    public static function token(): string
    {
        if (empty($_SESSION[self::KEY])) {
            $_SESSION[self::KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::KEY];
    }

    public static function field(): string
    {
        $token = htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="_csrf" value="' . $token . '">';
    }

    public static function validate(?string $token): bool
    {
        if (empty($_SESSION[self::KEY]) || !is_string($token) || $token === '') {
            return false;
        }
        return hash_equals($_SESSION[self::KEY], $token);
    }

    public static function rotate(): void
    {
        $_SESSION[self::KEY] = bin2hex(random_bytes(32));
    }
}
