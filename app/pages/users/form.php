<?php
Auth::requireAdmin();
$id = (int)($_GET['id'] ?? 0);
$data = $id ? User::find($id) : ['name' => '', 'email' => '', 'role' => 'operator'];
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
        $data['role'] = $_POST['role'] ?? 'operator';
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm'] ?? '';

        if ($data['name'] === '' || !filter_var($data['email'], FILTER_VALIDATE_EMAIL) || (!$id && $password === '')) {
            Flash::set('danger', 'Preencha os campos obrigatórios com dados válidos.');
        } elseif (!in_array($data['role'], ['admin', 'operator'], true)) {
            Flash::set('danger', 'Perfil de acesso inválido.');
        } elseif ($password !== '' && mb_strlen($password) < 6) {
            Flash::set('danger', 'A senha deve ter pelo menos 6 caracteres.');
        } elseif ($password !== $confirm) {
            Flash::set('danger', 'A confirmacao da senha nao confere.');
        } else {
            $existing = User::findByEmail($data['email']);
            if ($existing && $existing['id'] !== $id) {
                Flash::set('danger', 'Já existe um usuário com este e-mail.');
            } else {
                $canSave = true;
                if ($id && ($data['role'] ?? '') !== 'admin') {
                    $current = User::find($id);
                    if (($current['role'] ?? '') === 'admin' && User::countAdmins() <= 1) {
                        Flash::set('danger', 'O sistema precisa manter pelo menos um administrador.');
                        $data['role'] = 'admin';
                        $canSave = false;
                    }
                }
                if ($canSave) {
                    $payload = ['name' => $data['name'], 'email' => $data['email'], 'role' => $data['role']];
                    if ($password !== '') {
                        $payload['password'] = $password;
                    }

                    if ($id) {
                        User::update($id, $payload);
                        Flash::set('success', 'Usuário atualizado.');
                    } else {
                        User::create($payload);
                        Flash::set('success', 'Usuário criado.');
                    }
                    header('Location: ?p=users/index');
                    exit;
                }
            }
        }
    }
}
?>
<h3><?= $id ? 'Editar' : 'Novo' ?> usuário</h3>
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
    <div class="col-12">
        <label class="form-label">Perfil *</label>
        <select name="role" class="form-select" required>
            <option value="operator" <?= ($data['role'] ?? '') === 'operator' ? 'selected' : '' ?>>Operador</option>
            <option value="admin" <?= ($data['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrador</option>
        </select>
        <div class="form-text">Operadores gerenciam a operação; administradores também gerenciam usuários.</div>
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
