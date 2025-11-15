<?php
$q = trim($_GET['q'] ?? '');
$pageNo = max(1, (int)($_GET['page'] ?? 1));
$per = 20;
$total = Client::count($q ?: null);
$items = Client::all(($pageNo - 1) * $per, $per, $q ?: null);
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Clientes</h3>
    <a href="?p=clients/form" class="btn btn-primary">+ Novo</a>
</div>
<form class="mb-3" method="get">
    <input type="hidden" name="p" value="clients/index">
    <div class="input-group">
        <input name="q" class="form-control" placeholder="Buscar por nome" value="<?= htmlspecialchars($q) ?>">
        <button class="btn btn-outline-secondary">Buscar</button>
    </div>
</form>
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
            <td><?= htmlspecialchars($r['email']) ?></td>
            <td><?= htmlspecialchars($r['whatsapp']) ?></td>
            <td class="text-end">
                <a class="btn btn-sm btn-outline-secondary" href="?p=clients/form&id=<?= $r['id'] ?>">Editar</a>
                <a class="btn btn-sm btn-outline-danger" href="?p=clients/delete&id=<?= $r['id'] ?>">Excluir</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
