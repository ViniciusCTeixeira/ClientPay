<?php
$q = trim($_GET['q'] ?? '');
$pageNo = max(1, (int)($_GET['page'] ?? 1));
$per = 20;
$total = Site::count($q ?: null);
$items = Site::all(($pageNo - 1) * $per, $per, $q ?: null);
?>
<div class="page-header">
    <div class="page-heading"><h3>Sites</h3><p>Gerencie projetos, domínios e valores recorrentes vinculados aos clientes.</p></div>
    <div class="page-actions"><a href="?p=sites/form" class="btn btn-primary">+ Novo site</a></div>
</div>
<form class="filter-panel" method="get">
    <input type="hidden" name="p" value="sites/index">
    <div class="input-group">
        <input name="q" class="form-control" placeholder="Buscar por cliente ou site" value="<?= htmlspecialchars($q) ?>">
        <button class="btn btn-soft">Buscar</button>
    </div>
</form>
<div class="content-panel table-responsive">
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
                <form method="post" action="?p=sites/delete" class="d-inline"
                      data-confirm="Arquivar este site? As mensalidades existentes serão preservadas.">
                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                    <button class="btn btn-sm btn-outline-danger" type="submit">Arquivar</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$items): ?>
        <tr><td colspan="7"><div class="empty-state"><span class="empty-state-mark">S</span><strong>Nenhum site encontrado</strong><span>Cadastre um site e defina sua mensalidade.</span></div></td></tr>
    <?php endif; ?>
    </tbody>
</table>
</div>
<?php include __DIR__ . '/../partials/pagination.php'; ?>
