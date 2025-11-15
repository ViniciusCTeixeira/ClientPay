<?php
$q = trim($_GET['q'] ?? '');
$pageNo = max(1, (int)($_GET['page'] ?? 1));
$per = 20;
$total = Site::count($q ?: null);
$items = Site::all(($pageNo - 1) * $per, $per, $q ?: null);
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Sites</h3>
    <a href="?p=sites/form" class="btn btn-primary">+ Novo</a>
</div>
<form class="mb-3" method="get">
    <input type="hidden" name="p" value="sites/index">
    <div class="input-group">
        <input name="q" class="form-control" placeholder="Buscar por cliente ou site" value="<?= htmlspecialchars($q) ?>">
        <button class="btn btn-outline-secondary">Buscar</button>
    </div>
</form>
<table class="table table-striped align-middle">
    <thead>
    <tr>
        <th>#</th>
        <th>Cliente</th>
        <th>Site</th>
        <th>Domínio</th>
        <th>Criação</th>
        <th>Mensalidade</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $r): ?>
        <tr>
            <td><?= $r['id'] ?></td>
            <td><?= htmlspecialchars($r['client_name']) ?></td>
            <td><?= htmlspecialchars($r['name']) ?></td>
            <td><?= htmlspecialchars((string)$r['domain']) ?></td>
            <td>R$ <?= Formatter::money($r['creation_cost']) ?></td>
            <td>R$ <?= Formatter::money($r['current_monthly_fee']) ?></td>
            <td class="text-end">
                <a class="btn btn-sm btn-outline-secondary" href="?p=sites/form&id=<?= $r['id'] ?>">Editar</a>
                <a class="btn btn-sm btn-outline-danger" href="?p=sites/delete&id=<?= $r['id'] ?>">Excluir</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
