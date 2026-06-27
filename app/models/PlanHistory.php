<?php

class PlanHistory
{
    private static function isValidDate(string $date): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }
        [$y, $m, $d] = array_map('intval', explode('-', $date));
        return checkdate($m, $d, $y);
    }

    public static function bySite(int $siteId): array
    {
        $stm = Database::pdo()->prepare('SELECT * FROM plan_history WHERE site_id=? ORDER BY effective_from DESC');
        $stm->execute([$siteId]);
        return $stm->fetchAll();
    }

    public static function add(int $siteId, float $amount, string $from, ?string $notes = null): void
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('O valor do histórico deve ser maior que zero.');
        }
        if (!self::isValidDate($from)) {
            throw new InvalidArgumentException('Data de início inválida.');
        }
        $site = Site::find($siteId);
        if (!$site || !empty($site['archived_at'])) {
            throw new InvalidArgumentException('Site não encontrado ou arquivado.');
        }

        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $ins = $pdo->prepare(
                'INSERT INTO plan_history(site_id,amount,effective_from,notes) VALUES(?,?,?,?)
                 ON CONFLICT(site_id,effective_from) DO UPDATE SET amount=excluded.amount,notes=excluded.notes'
            );
            $ins->execute([$siteId, $amount, $from, $notes]);
            $current = self::resolveAmountForDate($siteId, date('Y-m-d'));
            $upd = $pdo->prepare('UPDATE sites SET current_monthly_fee=?, updated_at=datetime("now") WHERE id=?');
            $upd->execute([$current, $siteId]);

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function resolveAmountForDate(int $siteId, string $date): float
    {
        $stm = Database::pdo()->prepare('SELECT amount FROM plan_history WHERE site_id=? AND effective_from<=? ORDER BY effective_from DESC,id DESC LIMIT 1');
        $stm->execute([$siteId, $date]);
        $row = $stm->fetch();
        return (float)($row['amount'] ?? 0);
    }
}
