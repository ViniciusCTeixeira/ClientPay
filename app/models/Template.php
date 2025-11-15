<?php

class TemplateM
{
    public static function all(): array
    {
        return Database::pdo()->query('SELECT * FROM templates ORDER BY code')->fetchAll();
    }

    public static function findByCode(string $code): ?array
    {
        $stm = Database::pdo()->prepare('SELECT * FROM templates WHERE code=?');
        $stm->execute([$code]);
        return $stm->fetch() ?: null;
    }

    public static function find(int $id): ?array
    {
        $stm = Database::pdo()->prepare('SELECT * FROM templates WHERE id=?');
        $stm->execute([$id]);
        return $stm->fetch() ?: null;
    }

    public static function upsert(?int $id, array $d): int
    {
        if ($id) {
            $stm = Database::pdo()->prepare('UPDATE templates SET code=?,title=?,body=?,active=?,updated_at=datetime("now") WHERE id=?');
            $stm->execute([$d['code'], $d['title'], $d['body'], (int)($d['active'] ?? 1), $id]);
            return $id;
        }
        $stm = Database::pdo()->prepare('INSERT INTO templates(code,title,body,active) VALUES(?,?,?,?)');
        $stm->execute([$d['code'], $d['title'], $d['body'], (int)($d['active'] ?? 1)]);
        return (int)Database::pdo()->lastInsertId();
    }

    public static function delete(int $id): void
    {
        Database::pdo()->prepare('DELETE FROM templates WHERE id=?')->execute([$id]);
    }
}
