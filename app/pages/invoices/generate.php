<?php
$ym = $_GET['ym'] ?? date('Y-m'); // ex: 2025-11
$dueDayInput = isset($_GET['due_day']) ? (int)$_GET['due_day'] : 5;
$dueDayInput = max(1, min(31, $dueDayInput));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::check($_POST['csrf_token'] ?? null)) {
        Flash::set('danger', 'Sessão inválida. Atualize a página e tente novamente.');
        header('Location: ?p=invoices/generate');
        exit;
    }
    $ym = $_POST['ym'] ?? date('Y-m');
    $dueDayInput = max(1, min(31, (int)($_POST['due_day'] ?? 5)));
    if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $ym)) {
        Flash::set('danger','Competencia invalida. Use AAAA-MM.');
        header('Location: ?p=invoices/generate&ym='.urlencode(date('Y-m')).'&due_day='.$dueDayInput); exit;
    }

    $monthStart = $ym . '-01';
    $monthDate = DateTimeImmutable::createFromFormat('!Y-m-d', $monthStart);
    if (!$monthDate || $monthDate->format('Y-m-d') !== $monthStart) {
        Flash::set('danger', 'Competência inválida.');
        header('Location: ?p=invoices/generate');
        exit;
    }
    $daysInMonth = (int)$monthDate->format('t');
    $sites = Site::all(0, 10000);
    $created = 0;
    $skipped = 0;
    $pdo = Database::pdo();
    $pdo->beginTransaction();
    try {
        foreach ($sites as $s) {
            $siteDueDay = Invoice::lastDueDayForSite((int)$s['id']) ?? $dueDayInput;
            $siteDueDay = max(1, min($siteDueDay, $daysInMonth));
            $dueDate = $ym . '-' . str_pad((string)$siteDueDay, 2, '0', STR_PAD_LEFT);
            if (Invoice::existsForSiteDate((int)$s['id'], $dueDate)) {
                $skipped++;
                continue;
            }
            $amount = PlanHistory::resolveAmountForDate((int)$s['id'], $dueDate);
            if ($amount <= 0) continue;
            Invoice::create([
                    'site_id' => (int)$s['id'],
                    'client_id' => (int)$s['client_id'],
                    'amount' => $amount,
                    'due_date' => $dueDate,
                    'status' => 'pending',
                    'notes' => 'Gerado automaticamente'
            ]);
            $created++;
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
    $msg = "Geradas $created mensalidades para $ym.";
    if ($skipped) {
        $msg .= " $skipped registros ja existiam e foram ignorados.";
    }
    Flash::set('success', $msg);
    header('Location: ?p=invoices/index');
    exit;
}
?>
<div class="page-header"><div class="page-heading"><h3>Gerar mensalidades</h3><p>Crie as cobranças da competência para todos os sites ativos em uma única ação.</p></div></div>
<form method="post" class="row g-3 form-panel">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
    <div class="col-md-4">
        <label class="form-label">Competencia (AAAA-MM)</label>
        <input name="ym" type="month" class="form-control" value="<?= htmlspecialchars($ym) ?>">
    </div>
    <div class="col-md-4">
        <label class="form-label">Dia do vencimento padrao</label>
        <input name="due_day" type="number" min="1" max="31" class="form-control" value="<?= htmlspecialchars((string)$dueDayInput) ?>">
        <div class="form-text">Usado somente para sites sem mensalidades anteriores.</div>
    </div>
    <div class="col-12">
        <button class="btn btn-primary">Gerar mensalidades</button>
        <a class="btn btn-soft ms-2" href="?p=invoices/index">Cancelar</a>
    </div>
</form>
