<?php
$q = trim($_GET['q'] ?? '');
$pageNo = max(1, (int)($_GET['page'] ?? 1));
$per = 20;
$total = Invoice::count();
$items = Invoice::all(($pageNo - 1) * $per, $per, $q ?: null);
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Mensalidades</h3>
    <div>
        <a href="?p=invoices/form" class="btn btn-primary">+ Nova</a>
        <a href="?p=invoices/generate" class="btn btn-outline-primary">Gerar em lote</a>
        <a href="?p=invoices/update" class="btn btn-outline-primary">Atualizar</a>
    </div>
</div>
<form class="mb-3" method="get">
    <input type="hidden" name="p" value="invoices/index">
    <div class="input-group">
        <input name="q" class="form-control" placeholder="Buscar por cliente ou site"
               value="<?= htmlspecialchars($q) ?>">
        <button class="btn btn-outline-secondary">Buscar</button>
    </div>
</form>
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
                <a class="btn btn-sm btn-outline-info" href="?p=invoices/preview_message&id=<?= $r['id'] ?>">🔗
                    WhatsApp</a>
                <a class="btn btn-sm btn-outline-danger" href="?p=invoices/delete&id=<?= $r['id'] ?>">Excluir</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
