<?php
$ym = $_GET['ym'] ?? date('Y-m'); // ex: 2025-11
$dueDayInput = isset($_GET['due_day']) ? (int)$_GET['due_day'] : 5;
$dueDayInput = max(1, min(31, $dueDayInput));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ym = $_POST['ym'] ?? date('Y-m');
    $dueDayInput = max(1, min(31, (int)($_POST['due_day'] ?? 5)));
    if (!preg_match('/^\d{4}-\d{2}$/', $ym)) {
        Flash::set('danger','Competencia invalida. Use AAAA-MM.');
        header('Location: ?p=invoices/generate&ym='.urlencode(date('Y-m')).'&due_day='.$dueDayInput); exit;
    }

    $monthStart = $ym . '-01';
    $daysInMonth = (int)date('t', strtotime($monthStart));
    $sites = Site::all(0, 10000);
    $created = 0;
    $skipped = 0;
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
    $msg = "Geradas $created mensalidades para $ym.";
    if ($skipped) {
        $msg .= " $skipped registros ja existiam e foram ignorados.";
    }
    Flash::set('success', $msg);
    header('Location: ?p=invoices/index');
    exit;
}
?>
<h3>Gerar mensalidades</h3>
<form method="post" class="row g-3">
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
        <button class="btn btn-primary">Gerar</button>
    </div>
</form>
