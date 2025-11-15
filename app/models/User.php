<?php

class User
{
    public static function all(int $offset = 0, int $limit = 50, ?string $q = null): array
    {
        $where = '';
        $args = [];
        if ($q) {
            $where = 'WHERE name LIKE ? OR email LIKE ?';
            $args = ['%' . $q . '%', '%' . $q . '%'];
        }
        $sql = "SELECT * FROM users $where ORDER BY id DESC LIMIT :l OFFSET :o";
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
            $stm = Database::pdo()->prepare('SELECT COUNT(*) c FROM users WHERE name LIKE ? OR email LIKE ?');
            $like = '%' . $q . '%';
            $stm->execute([$like, $like]);
            return (int)$stm->fetch()['c'];
        }
        $stm = Database::pdo()->query('SELECT COUNT(*) c FROM users');
        return (int)$stm->fetch()['c'];
    }

    public static function find(int $id): ?array
    {
        $stm = Database::pdo()->prepare('SELECT * FROM users WHERE id=?');
        $stm->execute([$id]);
        return $stm->fetch() ?: null;
    }

    public static function findByEmail(string $email): ?array
    {
        $stm = Database::pdo()->prepare('SELECT * FROM users WHERE email=?');
        $stm->execute([$email]);
        return $stm->fetch() ?: null;
    }

    public static function create(array $data): int
    {
        $hash = password_hash($data['password'], PASSWORD_BCRYPT);
        $stm = Database::pdo()->prepare('INSERT INTO users(name,email,password_hash) VALUES(?,?,?)');
        $stm->execute([$data['name'], $data['email'], $hash]);
        return (int)Database::pdo()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $sql = 'UPDATE users SET name=:name,email=:email';
        $params = [':name' => $data['name'], ':email' => $data['email'], ':id' => $id];
        if (!empty($data['password'])) {
            $sql .= ', password_hash=:hash';
            $params[':hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        $sql .= ' WHERE id=:id';
        $stm = Database::pdo()->prepare($sql);
        $stm->execute($params);
    }

    public static function delete(int $id): void
    {
        $stm = Database::pdo()->prepare('DELETE FROM users WHERE id=?');
        $stm->execute([$id]);
    }
}
