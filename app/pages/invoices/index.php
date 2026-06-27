<?php
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    Invoice::refreshStatuses();
}
$q = trim($_GET['q'] ?? '');
$status = in_array($_GET['status'] ?? '', ['pending', 'paid', 'overdue', 'canceled'], true) ? $_GET['status'] : '';
$ym = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $_GET['ym'] ?? '') ? $_GET['ym'] : date('Y-m');
$pageNo = max(1, (int)($_GET['page'] ?? 1));
$per = 20;
$total = Invoice::count($q ?: null, $status ?: null, $ym);
$items = Invoice::all(($pageNo - 1) * $per, $per, $q ?: null, $status ?: null, $ym);
$summary = Invoice::summary($ym);
$exportQuery = http_build_query(['p' => 'invoices/export', 'q' => $q, 'status' => $status, 'ym' => $ym]);
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Mensalidades</h3>
    <div>
        <a href="?p=invoices/form" class="btn btn-primary">+ Nova</a>
        <a href="?p=invoices/generate" class="btn btn-outline-primary">Gerar em lote</a>
        <a href="?<?= htmlspecialchars($exportQuery) ?>" class="btn btn-outline-success">Exportar CSV</a>
        <form method="post" action="?p=invoices/update" class="d-inline">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
            <button type="submit" class="btn btn-outline-primary">Atualizar</button>
        </form>
    </div>
</div>
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card h-100"><div class="card-body"><div class="text-muted">Mensalidades</div><div class="fs-4"><?= (int)$summary['total'] ?></div></div></div></div>
    <div class="col-md-3"><div class="card h-100"><div class="card-body"><div class="text-muted">A receber</div><div class="fs-4">R$ <?= Formatter::money($summary['pending_amount']) ?></div></div></div></div>
    <div class="col-md-3"><div class="card h-100 border-danger"><div class="card-body"><div class="text-muted">Vencido</div><div class="fs-4 text-danger">R$ <?= Formatter::money($summary['overdue_amount']) ?></div></div></div></div>
    <div class="col-md-3"><div class="card h-100 border-success"><div class="card-body"><div class="text-muted">Recebido</div><div class="fs-4 text-success">R$ <?= Formatter::money($summary['paid_amount']) ?></div></div></div></div>
</div>
<form class="mb-3" method="get">
    <input type="hidden" name="p" value="invoices/index">
    <div class="row g-2">
        <div class="col-md-5"><input name="q" class="form-control" placeholder="Buscar por cliente ou site" value="<?= htmlspecialchars($q) ?>"></div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">Todos os status</option>
                <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pendente</option>
                <option value="paid" <?= $status === 'paid' ? 'selected' : '' ?>>Pago</option>
                <option value="overdue" <?= $status === 'overdue' ? 'selected' : '' ?>>Vencido</option>
                <option value="canceled" <?= $status === 'canceled' ? 'selected' : '' ?>>Cancelado</option>
            </select>
        </div>
        <div class="col-md-2"><input name="ym" type="month" class="form-control" value="<?= htmlspecialchars($ym) ?>"></div>
        <div class="col-md-2 d-grid"><button class="btn btn-outline-secondary">Filtrar</button></div>
    </div>
</form>
<div class="table-responsive">
<table class="table table-striped align-middle">
    <thead>
    <tr>
        <th>#</th>
        <th>Cliente</th>
        <th>Site</th>
        <th>Valor</th>
        <th>Vencimento</th>
        <th>Status</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $r): ?>
        <tr>
            <td><?= $r['id'] ?></td>
            <td><?= htmlspecialchars($r['client_name']) ?></td>
            <td><?= htmlspecialchars($r['site_name']) ?></td>
            <td>R$ <?= Formatter::money($r['amount']) ?></td>
            <td><?= Formatter::dateBr($r['due_date']) ?></td>
            <td>
                <span class="badge text-bg-<?= $r['status'] === 'paid' ? 'success' : ($r['status'] === 'overdue' ? 'danger' : ($r['status'] === 'canceled' ? 'secondary' : 'warning')) ?>">
                    <?php if ($r['status'] == 'pending') { ?>
                        Pendente
                    <?php } ?>
                    <?php if ($r['status'] == 'paid') { ?>
                        Pago
                    <?php } ?>
                    <?php if ($r['status'] == 'overdue') { ?>
                        Vencido
                    <?php } ?>
                    <?php if ($r['status'] == 'canceled') { ?>
                        Cancelado
                    <?php } ?>
                </span>
            </td>
            <td class="text-end">
                <a class="btn btn-sm btn-outline-secondary" href="?p=invoices/form&id=<?= $r['id'] ?>">Editar</a>
                <?php if (in_array($r['status'], ['pending', 'overdue'], true)): ?>
                    <a class="btn btn-sm btn-outline-info" href="?p=invoices/preview_message&id=<?= $r['id'] ?>">WhatsApp</a>
                    <form method="post" action="?p=invoices/delete" class="d-inline"
                          data-confirm="Cancelar esta mensalidade?">
                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                        <button class="btn btn-sm btn-outline-danger" type="submit">Cancelar</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php include __DIR__ . '/../partials/pagination.php'; ?>
