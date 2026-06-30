<?php
$q = trim($_GET['q'] ?? '');
$pageNo = max(1, (int)($_GET['page'] ?? 1));
$per = 20;
$total = Client::count($q ?: null);
$items = Client::all(($pageNo - 1) * $per, $per, $q ?: null);
?>
<div class="page-header">
    <div class="page-heading"><h3>Clientes</h3><p>Centralize contatos e acesse rapidamente os sites de cada cliente.</p></div>
    <div class="page-actions"><a href="?p=clients/form" class="btn btn-primary">+ Novo cliente</a></div>
</div>
<form class="filter-panel" method="get">
    <input type="hidden" name="p" value="clients/index">
    <div class="input-group">
        <input name="q" class="form-control" placeholder="Buscar por nome" value="<?= htmlspecialchars($q) ?>">
        <button class="btn btn-soft">Buscar</button>
    </div>
</form>
<div class="content-panel table-responsive">
<table class="table table-striped align-middle">
    <thead>
    <tr>
        <th>#</th>
        <th>Nome</th>
        <th>Email</th>
        <th>WhatsApp</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $r): ?>
        <tr>
            <td><?= $r['id'] ?></td>
            <td><a href="?p=clients/view&id=<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></a></td>
            <td><?= htmlspecialchars((string)$r['email']) ?></td>
            <td><?= htmlspecialchars((string)$r['whatsapp']) ?></td>
            <td class="text-end">
                <a class="btn btn-sm btn-outline-secondary" href="?p=clients/form&id=<?= $r['id'] ?>">Editar</a>
                <form method="post" action="?p=clients/delete" class="d-inline"
                      data-confirm="Arquivar este cliente e seus sites? O histórico financeiro será preservado.">
                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                    <button class="btn btn-sm btn-outline-danger" type="submit">Arquivar</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$items): ?>
        <tr><td colspan="5"><div class="empty-state"><span class="empty-state-mark">C</span><strong>Nenhum cliente encontrado</strong><span>Cadastre o primeiro cliente para começar.</span></div></td></tr>
    <?php endif; ?>
    </tbody>
</table>
</div>
<?php include __DIR__ . '/../partials/pagination.php'; ?>
