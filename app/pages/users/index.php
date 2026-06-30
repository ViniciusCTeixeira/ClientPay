<?php
Auth::requireAdmin();
$q = trim($_GET['q'] ?? '');
$pageNo = max(1, (int)($_GET['page'] ?? 1));
$per = 20;
$total = User::count($q ?: null);
$items = User::all(($pageNo - 1) * $per, $per, $q ?: null);
?>
<div class="page-header">
  <div class="page-heading"><h3>Equipe</h3><p>Controle quem pode acessar e administrar a operação.</p></div>
  <div class="page-actions"><a href="?p=users/form" class="btn btn-primary">+ Novo usuário</a></div>
</div>
<form class="filter-panel" method="get">
  <input type="hidden" name="p" value="users/index">
  <div class="input-group">
    <input name="q" class="form-control" placeholder="Buscar por nome ou email" value="<?= htmlspecialchars($q) ?>">
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
        <th>Perfil</th>
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
            <td><span class="badge text-bg-<?= ($user['role'] ?? 'operator') === 'admin' ? 'success' : 'secondary' ?>"><?= ($user['role'] ?? 'operator') === 'admin' ? 'Administrador' : 'Operador' ?></span></td>
            <td><?= Formatter::dateBr($user['created_at']) ?></td>
            <td class="text-end">
                <a class="btn btn-sm btn-outline-secondary" href="?p=users/form&id=<?= $user['id'] ?>">Editar</a>
                <form method="post" action="?p=users/delete" class="d-inline"
                      data-confirm="Excluir este usuário?">
                    <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                    <button class="btn btn-sm btn-outline-danger" type="submit">Excluir</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$items): ?>
        <tr><td colspan="6"><div class="empty-state"><span class="empty-state-mark">U</span><strong>Nenhum usuário encontrado</strong><span>Altere a busca ou adicione alguém à equipe.</span></div></td></tr>
    <?php endif; ?>
    </tbody>
</table>
</div>
<?php include __DIR__ . '/../partials/pagination.php'; ?>
