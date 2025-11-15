<?php

class Site
{
    public static function allByClient(int $clientId): array
    {
        $stm = Database::pdo()->prepare('SELECT * FROM sites WHERE client_id=? ORDER BY id DESC');
        $stm->execute([$clientId]);
        return $stm->fetchAll();
    }

    public static function all(int $offset = 0, int $limit = 50, ?string $q = null): array
    {
        $where = '';
        $args = [];
        if ($q) {
            $where = 'WHERE c.name LIKE ? OR s.name LIKE ?';
            $args = ['%' . $q . '%', '%' . $q . '%'];
        }
        $sql = "SELECT s.*, c.name client_name FROM sites s JOIN clients c ON c.id=s.client_id $where ORDER BY s.id DESC LIMIT :l OFFSET :o";
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
            $stm = Database::pdo()->prepare('SELECT COUNT(*) c FROM sites s JOIN clients c ON c.id=s.client_id WHERE c.name LIKE ? OR s.name LIKE ?');
            $like = '%' . $q . '%';
            $stm->execute([$like, $like]);
            return (int)$stm->fetch()['c'];
        }
        return (int)Database::pdo()->query('SELECT COUNT(*) c FROM sites')->fetch()['c'];
    }

    public static function find(int $id): ?array
    {
        $stm = Database::pdo()->prepare('SELECT * FROM sites WHERE id=?');
        $stm->execute([$id]);
        return $stm->fetch() ?: null;
    }

    public static function create(array $d): int
    {
        $stm = Database::pdo()->prepare('INSERT INTO sites(client_id,name,domain,creation_cost,current_monthly_fee) VALUES(?,?,?,?,?)');
        $stm->execute([$d['client_id'], $d['name'], $d['domain'] ?? null, $d['creation_cost'] ?? 0, $d['current_monthly_fee'] ?? 0]);
        $id = (int)Database::pdo()->lastInsertId();
        $ph = Database::pdo()->prepare('INSERT INTO plan_history(site_id,amount,effective_from,notes) VALUES(?,?,?,?)');
        $ph->execute([$id, $d['current_monthly_fee'] ?? 0, date('Y-m-d'), 'Valor inicial']);
        return $id;
    }

    public static function update(int $id, array $d): void
    {
        $stm = Database::pdo()->prepare('UPDATE sites SET client_id=?,name=?,domain=?,creation_cost=?,current_monthly_fee=?,updated_at=datetime("now") WHERE id=?');
        $stm->execute([$d['client_id'], $d['name'], $d['domain'] ?? null, $d['creation_cost'] ?? 0, $d['current_monthly_fee'] ?? 0, $id]);
    }

    public static function delete(int $id): void
    {
        Database::pdo()->prepare('DELETE FROM sites WHERE id=?')->execute([$id]);
    }
}
