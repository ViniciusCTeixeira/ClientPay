<?php

class Database
{
    private static ?PDO $pdo = null;

    public static function init(array $cfg): void
    {
        if (self::$pdo) return;
        $dbPath = $cfg['db']['path'];
        $dir = dirname($dbPath);
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        $isNew = !file_exists($dbPath);

        self::$pdo = new PDO('sqlite:' . $dbPath, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_PERSISTENT => false,
        ]);
        self::$pdo->exec('PRAGMA foreign_keys = ON');

        if ($isNew) {
            $schema = file_get_contents($cfg['db']['sql']);
            self::$pdo->exec($schema);
        }
    }

    public static function pdo(): PDO
    {
        return self::$pdo;
    }
}
