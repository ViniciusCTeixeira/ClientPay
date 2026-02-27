<?php
$id = (int)($_GET['id'] ?? 0);
$data = $id ? User::find($id) : ['name' => '', 'email' => ''];
if ($id && !$data) {
    echo 'Usuario nao encontrado.';
    return;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::check($_POST['csrf_token'] ?? null)) {
        Flash::set('danger', 'Sessão inválida. Atualize a página e tente novamente.');
    } else {
        $data['name'] = trim($_POST['name'] ?? '');
        $data['email'] = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm'] ?? '';

        if ($data['name'] === '' || $data['email'] === '' || (!$id && $password === '')) {
            Flash::set('danger', 'Preencha os campos obrigatorios.');
        } elseif ($password !== '' && mb_strlen($password) < 6) {
            Flash::set('danger', 'A senha deve ter pelo menos 6 caracteres.');
        } elseif ($password !== $confirm) {
            Flash::set('danger', 'A confirmacao da senha nao confere.');
        } else {
            $existing = User::findByEmail($data['email']);
            if ($existing && $existing['id'] !== $id) {
                Flash::set('danger', 'Ja existe um usuario com este email.');
            } else {
                $payload = ['name' => $data['name'], 'email' => $data['email']];
                if ($password !== '') {
                    $payload['password'] = $password;
                }

                if ($id) {
                    User::update($id, $payload);
                    Flash::set('success', 'Usuario atualizado.');
                } else {
                    User::create($payload);
                    Flash::set('success', 'Usuario criado.');
                }
                header('Location: ?p=users/index');
                exit;
            }
        }
    }
}
?>
<h3><?= $id ? 'Editar' : 'Novo' ?> usuario</h3>
<form method="post" class="row g-3">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
    <div class="col-12">
        <label class="form-label">Nome *</label>
        <input name="name" class="form-control" value="<?= htmlspecialchars($data['name']) ?>" required>
    </div>
    <div class="col-12">
        <label class="form-label">Email *</label>
        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($data['email']) ?>" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Senha <?= $id ? '(deixe em branco para manter)' : '*' ?></label>
        <input type="password" name="password" class="form-control" <?= $id ? '' : 'required' ?> minlength="6">
    </div>
    <div class="col-md-6">
        <label class="form-label">Confirmar senha <?= $id ? '' : '*' ?></label>
        <input type="password" name="confirm" class="form-control" <?= $id ? '' : 'required' ?> minlength="6">
    </div>
    <div class="col-12">
        <button class="btn btn-primary">Salvar</button>
    </div>
</form>
