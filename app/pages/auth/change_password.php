<?php
Auth::requireLogin();
$user = Auth::user();
if (!$user) {
    header('Location: ?p=auth/login');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::check($_POST['csrf_token'] ?? null)) {
        Flash::set('danger', 'Sessão inválida. Atualize a página e tente novamente.');
    } else {
    $current = $_POST['current'] ?? '';
    $new = $_POST['new'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    if (!$current || !$new || !$confirm) {
        Flash::set('danger', 'Preencha todos os campos.');
    } elseif (mb_strlen($new) < 6) {
        Flash::set('danger', 'A nova senha deve ter pelo menos 6 caracteres.');
    } elseif ($new !== $confirm) {
        Flash::set('danger', 'A confirmação não confere.');
    } elseif (!password_verify($current, $user['password_hash'])) {
        Flash::set('danger', 'Senha atual incorreta.');
    } else {
        Auth::updatePassword((int)$user['id'], $new);
        Flash::set('success', 'Senha alterada com sucesso! Faça login novamente.');
        header('Location: ?p=auth/logout');
        exit;
    }
    }
}
?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <h3>Trocar senha</h3>
        <form method="post" class="mt-3">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
            <div class="mb-3">
                <label class="form-label">Senha atual</label>
                <input type="password" name="current" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Nova senha</label>
                <input type="password" name="new" class="form-control" required minlength="6">
            </div>
            <div class="mb-3">
                <label class="form-label">Confirmar nova senha</label>
                <input type="password" name="confirm" class="form-control" required minlength="6">
            </div>
            <button class="btn btn-primary">Salvar</button>
        </form>
    </div>
</div>
