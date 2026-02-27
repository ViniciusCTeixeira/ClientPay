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

        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            // Última data vigente no histórico para o site
            $q = $pdo->prepare('SELECT MAX(effective_from) AS max_from FROM plan_history WHERE site_id=?');
            $q->execute([$siteId]);
            $max = $q->fetch()['max_from'] ?? null; // formato YYYY-MM-DD

            // Registra o histórico (sempre)
            $ins = $pdo->prepare('INSERT INTO plan_history(site_id,amount,effective_from,notes) VALUES(?,?,?,?)');
            $ins->execute([$siteId, $amount, $from, $notes]);

            // Atualiza o valor atual do site somente se:
            // - não havia histórico anterior, OU
            // - a nova data é mais recente que a última
            $shouldUpdate = is_null($max) || strcmp($from, $max) > 0;

            if ($shouldUpdate) {
                $upd = $pdo->prepare('UPDATE sites SET current_monthly_fee=?, updated_at=datetime("now") WHERE id=?');
                $upd->execute([$amount, $siteId]);
            }

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function resolveAmountForDate(int $siteId, string $date): float
    {
        $stm = Database::pdo()->prepare('SELECT amount FROM plan_history WHERE site_id=? AND effective_from<=? ORDER BY effective_from DESC LIMIT 1');
        $stm->execute([$siteId, $date]);
        $row = $stm->fetch();
        return (float)($row['amount'] ?? 0);
    }
}
