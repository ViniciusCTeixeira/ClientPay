<?php

class Invoice
{
    private static function isValidDate(string $date): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }
        [$y, $m, $d] = array_map('intval', explode('-', $date));
        return checkdate($m, $d, $y);
    }

    private static function assertValidStatus(string $status): void
    {
        $allowed = ['pending', 'paid', 'overdue', 'canceled'];
        if (!in_array($status, $allowed, true)) {
            throw new InvalidArgumentException('Status de mensalidade inválido.');
        }
    }

    private static function assertSiteBelongsToClient(int $siteId, int $clientId): void
    {
        $stm = Database::pdo()->prepare('SELECT client_id FROM sites WHERE id=?');
        $stm->execute([$siteId]);
        $site = $stm->fetch();
        if (!$site) {
            throw new InvalidArgumentException('Site informado não existe.');
        }
        if ((int)$site['client_id'] !== $clientId) {
            throw new InvalidArgumentException('O site selecionado não pertence ao cliente informado.');
        }
    }

    private static function sanitizePayload(array $d): array
    {
        $siteId = (int)($d['site_id'] ?? 0);
        $clientId = (int)($d['client_id'] ?? 0);
        $amount = (float)($d['amount'] ?? 0);
        $dueDate = trim((string)($d['due_date'] ?? ''));
        $status = (string)($d['status'] ?? 'pending');
        $notes = trim((string)($d['notes'] ?? '')) ?: null;

        if ($siteId <= 0 || $clientId <= 0) {
            throw new InvalidArgumentException('Cliente e site são obrigatórios.');
        }
        if ($amount <= 0) {
            throw new InvalidArgumentException('O valor da mensalidade deve ser maior que zero.');
        }
        if (!self::isValidDate($dueDate)) {
            throw new InvalidArgumentException('Data de vencimento inválida.');
        }
        self::assertValidStatus($status);
        self::assertSiteBelongsToClient($siteId, $clientId);

        return [
            'site_id' => $siteId,
            'client_id' => $clientId,
            'amount' => $amount,
            'due_date' => $dueDate,
            'status' => $status,
            'notes' => $notes,
        ];
    }

    public static function all(int $offset = 0, int $limit = 50, ?string $q = null): array
    {
        $where = '';
        $args = [];
        if ($q) {
            $where = 'WHERE c.name LIKE ? OR s.name LIKE ?';
            $args = ['%' . $q . '%', '%' . $q . '%'];
        }
        $sql = "SELECT i.*, c.name client_name, s.name site_name FROM invoices i JOIN clients c ON c.id=i.client_id JOIN sites s ON s.id=i.site_id $where ORDER BY i.due_date DESC, i.id DESC LIMIT :l OFFSET :o";
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
            $stm = Database::pdo()->prepare('SELECT COUNT(*) total FROM invoices i JOIN clients c ON c.id=i.client_id JOIN sites s ON s.id=i.site_id WHERE c.name LIKE ? OR s.name LIKE ?');
            $like = '%' . $q . '%';
            $stm->execute([$like, $like]);
            return (int)$stm->fetch()['total'];
        }
        return (int)Database::pdo()->query('SELECT COUNT(*) c FROM invoices')->fetch()['c'];
    }

    public static function find(int $id): ?array
    {
        $stm = Database::pdo()->prepare('SELECT i.*, c.name client_name, c.whatsapp, c.email, s.name site_name FROM invoices i JOIN clients c ON c.id=i.client_id JOIN sites s ON s.id=i.site_id WHERE i.id=?');
        $stm->execute([$id]);
        return $stm->fetch() ?: null;
    }

    public static function create(array $d): int
    {
        $d = self::sanitizePayload($d);
        $stm = Database::pdo()->prepare('INSERT INTO invoices(site_id,client_id,amount,due_date,status,notes) VALUES(?,?,?,?,?,?)');
        $stm->execute([$d['site_id'], $d['client_id'], $d['amount'], $d['due_date'], $d['status'] ?? 'pending', $d['notes'] ?? null]);
        return (int)Database::pdo()->lastInsertId();
    }

    public static function update(int $id, array $d): void
    {
        $d = self::sanitizePayload($d);
        $stm = Database::pdo()->prepare('UPDATE invoices SET site_id=?,client_id=?,amount=?,due_date=?,status=?,notes=?,updated_at=datetime("now") WHERE id=?');
        $stm->execute([$d['site_id'], $d['client_id'], $d['amount'], $d['due_date'], $d['status'], $d['notes'] ?? null, $id]);
    }

    public static function delete(int $id): void
    {
        Database::pdo()->prepare('DELETE FROM invoices WHERE id=?')->execute([$id]);
    }

    public static function markPaid(int $id): void
    {
        Database::pdo()->prepare("UPDATE invoices SET status='paid',updated_at=datetime('now') WHERE id=?")->execute([$id]);
    }

    public static function refreshStatuses(?string $today = null): array
    {
        $today = $today ?? date('Y-m-d');
        $pdo = Database::pdo();
        $overdueStm = $pdo->prepare("UPDATE invoices SET status='overdue',updated_at=datetime('now') WHERE status NOT IN ('paid','canceled') AND due_date < ?");
        $overdueStm->execute([$today]);
        $madeOverdue = $overdueStm->rowCount();

        $pendingStm = $pdo->prepare("UPDATE invoices SET status='pending',updated_at=datetime('now') WHERE status='overdue' AND due_date >= ?");
        $pendingStm->execute([$today]);
        $backToPending = $pendingStm->rowCount();

        return [
            'overdue' => $madeOverdue,
            'pending' => $backToPending,
        ];
    }

    public static function lastDueDayForSite(int $siteId): ?int
    {
        $stm = Database::pdo()->prepare('SELECT due_date FROM invoices WHERE site_id=? ORDER BY due_date DESC LIMIT 1');
        $stm->execute([$siteId]);
        $row = $stm->fetch();
        if (!$row || empty($row['due_date'])) {
            return null;
        }
        $parts = explode('-', $row['due_date']);
        if (count($parts) < 3) {
            return null;
        }
        return (int)$parts[2];
    }

    public static function existsForSiteDate(int $siteId, string $dueDate): bool
    {
        $stm = Database::pdo()->prepare('SELECT 1 FROM invoices WHERE site_id=? AND due_date=? LIMIT 1');
        $stm->execute([$siteId, $dueDate]);
        return (bool)$stm->fetchColumn();
    }
}
