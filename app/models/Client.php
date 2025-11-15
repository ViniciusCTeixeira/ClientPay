<?php

class Client
{
    public static function all(int $offset = 0, int $limit = 50, ?string $q = null): array
    {
        $where = '';
        $args = [];
        if ($q) {
            $where = 'WHERE name LIKE ?';
            $args = ['%' . $q . '%'];
        }
        $sql = "SELECT * FROM clients $where ORDER BY id DESC LIMIT :l OFFSET :o";
        $stm = Database::pdo()->prepare($sql);
        foreach ($args as $k => $v) {
            $stm->bindValue($k + 1, $v);
        }
        $stm->bindValue(':o', $offset, PDO::PARAM_INT);
        $stm->bindValue(':l', $limit, PDO::PARAM_INT);
        $stm->execute();
        return $stm->fetchAll();
    }

    public static function count(?string $q = null): int
    {
        if ($q !== null && $q !== '') {
            $stm = Database::pdo()->prepare('SELECT COUNT(*) c FROM clients WHERE name LIKE ?');
            $stm->execute(['%' . $q . '%']);
            return (int)$stm->fetch()['c'];
        }
        return (int)Database::pdo()->query('SELECT COUNT(*) c FROM clients')->fetch()['c'];
    }

    public static function find(int $id): ?array
    {
        $stm = Database::pdo()->prepare('SELECT * FROM clients WHERE id=?');
        $stm->execute([$id]);
        return $stm->fetch() ?: null;
    }

    public static function create(array $d): int
    {
        $stm = Database::pdo()->prepare('INSERT INTO clients(name,email,whatsapp) VALUES(?,?,?)');
        $stm->execute([$d['name'], $d['email'] ?? null, $d['whatsapp'] ?? null]);
        return (int)Database::pdo()->lastInsertId();
    }

    public static function update(int $id, array $d): void
    {
        $stm = Database::pdo()->prepare('UPDATE clients SET name=?,email=?,whatsapp=?,updated_at=datetime("now") WHERE id=?');
        $stm->execute([$d['name'], $d['email'] ?? null, $d['whatsapp'] ?? null, $id]);
    }

    public static function delete(int $id): void
    {
        Database::pdo()->prepare('DELETE FROM clients WHERE id=?')->execute([$id]);
    }
}
