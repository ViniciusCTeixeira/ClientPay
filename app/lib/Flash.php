<?php

class Flash
{
    public static function set(string $type, string $msg): void
    {
        $_SESSION['flash'] = compact('type', 'msg');
    }

    public static function get(): ?array
    {
        $f = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $f;
    }
}
