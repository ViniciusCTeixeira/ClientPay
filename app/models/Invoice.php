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
        $paidAt = trim((string)($d['paid_at'] ?? '')) ?: null;
        $paymentMethod = trim((string)($d['payment_method'] ?? '')) ?: null;
        $paymentReference = trim((string)($d['payment_reference'] ?? '')) ?: null;

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
        $allowedMethods = ['pix', 'boleto', 'card', 'transfer', 'cash', 'other'];
        if ($paymentMethod !== null && !in_array($paymentMethod, $allowedMethods, true)) {
            throw new InvalidArgumentException('Forma de pagamento inválida.');
        }
        if ($status === 'paid') {
            $paidAt = $paidAt ?: date('Y-m-d');
            if (!self::isValidDate($paidAt)) {
                throw new InvalidArgumentException('Data de pagamento inválida.');
            }
        } else {
            $paidAt = null;
            $paymentMethod = null;
            $paymentReference = null;
        }

        return [
            'site_id' => $siteId,
            'client_id' => $clientId,
            'amount' => $amount,
            'due_date' => $dueDate,
            'status' => $status,
            'notes' => $notes,
            'paid_at' => $paidAt,
            'payment_method' => $paymentMethod,
            'payment_reference' => $paymentReference,
        ];
    }

    private static function filters(?string $q, ?string $status, ?string $ym): array
    {
        $clauses = [];
        $args = [];
        if ($q) {
            $clauses[] = '(c.name LIKE :q OR s.name LIKE :q)';
            $args[':q'] = '%' . $q . '%';
        }
        if ($status && in_array($status, ['pending', 'paid', 'overdue', 'canceled'], true)) {
            $clauses[] = 'i.status = :status';
            $args[':status'] = $status;
        }
        if ($ym && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $ym)) {
            $clauses[] = "substr(i.due_date,1,7) = :ym";
            $args[':ym'] = $ym;
        }
        return [$clauses ? ('WHERE ' . implode(' AND ', $clauses)) : '', $args];
    }

    public static function all(int $offset = 0, int $limit = 50, ?string $q = null, ?string $status = null, ?string $ym = null): array
    {
        [$where, $args] = self::filters($q, $status, $ym);
        $sql = "SELECT i.*, c.name client_name, s.name site_name FROM invoices i JOIN clients c ON c.id=i.client_id JOIN sites s ON s.id=i.site_id $where ORDER BY i.due_date DESC, i.id DESC LIMIT :l OFFSET :o";
        $stm = Database::pdo()->prepare($sql);
        foreach ($args as $key => $value) {
            $stm->bindValue($key, $value);
        }
        $stm->bindValue(':o', $offset, PDO::PARAM_INT);
        $stm->bindValue(':l', $limit, PDO::PARAM_INT);
        $stm->execute();
        return $stm->fetchAll();
    }

    public static function count(?string $q = null, ?string $status = null, ?string $ym = null): int
    {
        [$where, $args] = self::filters($q, $status, $ym);
        $stm = Database::pdo()->prepare("SELECT COUNT(*) FROM invoices i JOIN clients c ON c.id=i.client_id JOIN sites s ON s.id=i.site_id $where");
        foreach ($args as $key => $value) {
            $stm->bindValue($key, $value);
        }
        $stm->execute();
        return (int)$stm->fetchColumn();
    }

    public static function summary(?string $ym = null): array
    {
        $where = '';
        $args = [];
        if ($ym && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $ym)) {
            $where = 'WHERE substr(due_date,1,7)=?';
            $args[] = $ym;
        }
        $stm = Database::pdo()->prepare(
            "SELECT COUNT(*) total,
                    SUM(CASE WHEN status='pending' THEN amount ELSE 0 END) pending_amount,
                    SUM(CASE WHEN status='overdue' THEN amount ELSE 0 END) overdue_amount,
                    SUM(CASE WHEN status='paid' THEN amount ELSE 0 END) paid_amount
             FROM invoices $where"
        );
        $stm->execute($args);
        return $stm->fetch() ?: ['total' => 0, 'pending_amount' => 0, 'overdue_amount' => 0, 'paid_amount' => 0];
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
        $site = Site::find($d['site_id']);
        $client = Client::find($d['client_id']);
        if (!$site || !$client || !empty($site['archived_at']) || !empty($client['archived_at'])) {
            throw new InvalidArgumentException('Cliente e site precisam estar ativos para uma nova mensalidade.');
        }
        if (self::existsForSiteDate($d['site_id'], $d['due_date'])) {
            throw new InvalidArgumentException('Já existe mensalidade para este site nesta data.');
        }
        $stm = Database::pdo()->prepare('INSERT INTO invoices(site_id,client_id,amount,due_date,status,notes,paid_at,payment_method,payment_reference) VALUES(?,?,?,?,?,?,?,?,?)');
        $stm->execute([$d['site_id'], $d['client_id'], $d['amount'], $d['due_date'], $d['status'], $d['notes'], $d['paid_at'], $d['payment_method'], $d['payment_reference']]);
        return (int)Database::pdo()->lastInsertId();
    }

    public static function update(int $id, array $d): void
    {
        $d = self::sanitizePayload($d);
        if (self::existsForSiteDate($d['site_id'], $d['due_date'], $id)) {
            throw new InvalidArgumentException('Já existe mensalidade para este site nesta data.');
        }
        $stm = Database::pdo()->prepare('UPDATE invoices SET site_id=?,client_id=?,amount=?,due_date=?,status=?,notes=?,paid_at=?,payment_method=?,payment_reference=?,updated_at=datetime("now") WHERE id=?');
        $stm->execute([$d['site_id'], $d['client_id'], $d['amount'], $d['due_date'], $d['status'], $d['notes'], $d['paid_at'], $d['payment_method'], $d['payment_reference'], $id]);
    }

    public static function delete(int $id): void
    {
        Database::pdo()->prepare("UPDATE invoices SET status='canceled',updated_at=datetime('now') WHERE id=? AND status<>'paid'")->execute([$id]);
    }

    public static function markPaid(int $id): void
    {
        Database::pdo()->prepare("UPDATE invoices SET status='paid',paid_at=?,updated_at=datetime('now') WHERE id=?")->execute([date('Y-m-d'), $id]);
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

    public static function existsForSiteDate(int $siteId, string $dueDate, ?int $excludeId = null): bool
    {
        $sql = 'SELECT 1 FROM invoices WHERE site_id=? AND due_date=?';
        $args = [$siteId, $dueDate];
        if ($excludeId !== null) {
            $sql .= ' AND id<>?';
            $args[] = $excludeId;
        }
        $sql .= ' LIMIT 1';
        $stm = Database::pdo()->prepare($sql);
        $stm->execute($args);
        return (bool)$stm->fetchColumn();
    }
}
