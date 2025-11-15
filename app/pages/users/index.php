<?php
$q = trim($_GET['q'] ?? '');
$pageNo = max(1, (int)($_GET['page'] ?? 1));
$per = 20;
$total = User::count($q ?: null);
$items = User::all(($pageNo - 1) * $per, $per, $q ?: null);
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Usuarios</h3>
  <a href="?p=users/form" class="btn btn-primary">+ Novo</a>
</div>
<form class="mb-3" method="get">
  <input type="hidden" name="p" value="users/index">
  <div class="input-group">
    <input name="q" class="form-control" placeholder="Buscar por nome ou email" value="<?= htmlspecialchars($q) ?>">
    <button class="btn btn-outline-secondary">Buscar</button>
  </div>
</form>
<table class="table table-striped align-middle">
    <thead>
    <tr>
        <th>#</th>
        <th>Nome</th>
        <th>Email</th>
        <th>Criação</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $user): ?>
        <tr>
            <td><?= $user['id'] ?></td>
            <td><?= htmlspecialchars($user['name']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= Formatter::dateBr($user['created_at']) ?></td>
            <td class="text-end">
                <a class="btn btn-sm btn-outline-secondary" href="?p=users/form&id=<?= $user['id'] ?>">Editar</a>
                <a class="btn btn-sm btn-outline-danger" href="?p=users/delete&id=<?= $user['id'] ?>">Excluir</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
