<?php

class Site
{
    public static function allByClient(int $clientId): array
    {
        $stm = Database::pdo()->prepare('SELECT * FROM sites WHERE client_id=? AND archived_at IS NULL ORDER BY id DESC');
        $stm->execute([$clientId]);
        return $stm->fetchAll();
    }

    public static function all(int $offset = 0, int $limit = 50, ?string $q = null, bool $includeArchived = false): array
    {
        $where = $includeArchived ? 'WHERE 1=1' : 'WHERE s.archived_at IS NULL AND c.archived_at IS NULL';
        $args = [];
        if ($q) {
            $where .= ' AND (c.name LIKE ? OR s.name LIKE ?)';
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
            $stm = Database::pdo()->prepare('SELECT COUNT(*) c FROM sites s JOIN clients c ON c.id=s.client_id WHERE s.archived_at IS NULL AND c.archived_at IS NULL AND (c.name LIKE ? OR s.name LIKE ?)');
            $like = '%' . $q . '%';
            $stm->execute([$like, $like]);
            return (int)$stm->fetch()['c'];
        }
        return (int)Database::pdo()->query('SELECT COUNT(*) c FROM sites WHERE archived_at IS NULL')->fetch()['c'];
    }

    public static function find(int $id): ?array
    {
        $stm = Database::pdo()->prepare('SELECT * FROM sites WHERE id=?');
        $stm->execute([$id]);
        return $stm->fetch() ?: null;
    }

    public static function create(array $d): int
    {
        self::assertValidPayload($d);
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $stm = $pdo->prepare('INSERT INTO sites(client_id,name,domain,creation_cost,current_monthly_fee) VALUES(?,?,?,?,?)');
            $stm->execute([$d['client_id'], $d['name'], $d['domain'] ?? null, $d['creation_cost'] ?? 0, $d['current_monthly_fee'] ?? 0]);
            $id = (int)$pdo->lastInsertId();
            $ph = $pdo->prepare('INSERT INTO plan_history(site_id,amount,effective_from,notes) VALUES(?,?,?,?)');
            $ph->execute([$id, $d['current_monthly_fee'] ?? 0, date('Y-m-d'), 'Valor inicial']);
            $pdo->commit();
            return $id;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function update(int $id, array $d): void
    {
        self::assertValidPayload($d);
        $current = self::find($id);
        if (!$current) {
            throw new InvalidArgumentException('Site não encontrado.');
        }
        if ((int)$current['client_id'] !== (int)$d['client_id'] && self::hasInvoices($id)) {
            throw new InvalidArgumentException('Não é possível trocar o cliente de um site que já possui mensalidades.');
        }
        $stm = Database::pdo()->prepare('UPDATE sites SET client_id=?,name=?,domain=?,creation_cost=?,current_monthly_fee=?,updated_at=datetime("now") WHERE id=?');
        $stm->execute([$d['client_id'], $d['name'], $d['domain'] ?? null, $d['creation_cost'] ?? 0, $d['current_monthly_fee'] ?? 0, $id]);
    }

    public static function delete(int $id): void
    {
        Database::pdo()->prepare("UPDATE sites SET archived_at=datetime('now'),updated_at=datetime('now') WHERE id=? AND archived_at IS NULL")->execute([$id]);
    }

    public static function hasInvoices(int $id): bool
    {
        $stm = Database::pdo()->prepare('SELECT 1 FROM invoices WHERE site_id=? LIMIT 1');
        $stm->execute([$id]);
        return (bool)$stm->fetchColumn();
    }

    private static function assertValidPayload(array $d): void
    {
        if ((int)($d['client_id'] ?? 0) <= 0 || trim((string)($d['name'] ?? '')) === '') {
            throw new InvalidArgumentException('Cliente e nome do site são obrigatórios.');
        }
        if ((float)($d['creation_cost'] ?? 0) < 0 || (float)($d['current_monthly_fee'] ?? 0) < 0) {
            throw new InvalidArgumentException('Os valores do site não podem ser negativos.');
        }
        $client = Client::find((int)$d['client_id']);
        if (!$client || !empty($client['archived_at'])) {
            throw new InvalidArgumentException('O cliente selecionado não está ativo.');
        }
    }
}
