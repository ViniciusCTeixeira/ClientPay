<?php

class Csrf
{
    private const SESSION_KEY = '_csrf_token';

    public static function token(): string
    {
        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }
        return (string)$_SESSION[self::SESSION_KEY];
    }

    public static function check(?string $token): bool
    {
        if (!$token || empty($_SESSION[self::SESSION_KEY])) {
            return false;
        }
        return hash_equals((string)$_SESSION[self::SESSION_KEY], $token);
    }

    public static function rotate(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
        self::token();
    }
}
