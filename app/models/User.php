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
        $stm = Database::pdo()->prepare('SELECT * FROM users WHERE lower(email)=?');
        $stm->execute([mb_strtolower(trim($email))]);
        return $stm->fetch() ?: null;
    }

    public static function create(array $data): int
    {
        $role = in_array($data['role'] ?? '', ['admin', 'operator'], true) ? $data['role'] : 'operator';
        $hash = password_hash($data['password'], PASSWORD_BCRYPT);
        $stm = Database::pdo()->prepare('INSERT INTO users(name,email,password_hash,role) VALUES(?,?,?,?)');
        $stm->execute([$data['name'], mb_strtolower(trim($data['email'])), $hash, $role]);
        return (int)Database::pdo()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $role = in_array($data['role'] ?? '', ['admin', 'operator'], true) ? $data['role'] : 'operator';
        $sql = 'UPDATE users SET name=:name,email=:email,role=:role';
        $params = [':name' => $data['name'], ':email' => mb_strtolower(trim($data['email'])), ':role' => $role, ':id' => $id];
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

    public static function countAdmins(): int
    {
        return (int)Database::pdo()->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
    }
}
