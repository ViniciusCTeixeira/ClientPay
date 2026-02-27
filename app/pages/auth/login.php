<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::check($_POST['csrf_token'] ?? null)) {
        Flash::set('danger', 'Sessão inválida. Atualize a página e tente novamente.');
    } else {
        $ok = Auth::attempt($_POST['email'] ?? '', $_POST['password'] ?? '');
        if ($ok) {
            Flash::set('success', 'Bem-vindo!');
            header('Location: ?p=invoices/index');
            exit;
        }
        Flash::set('danger', 'Credenciais inválidas.');
    }
}
?>
<div class="row justify-content-center">
    <div class="col-md-4">
        <h3>Login</h3>
        <form method="post" class="mt-3">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input name="email" type="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Senha</label>
                <input name="password" type="password" class="form-control" required>
            </div>
            <button class="btn btn-primary w-100">Entrar</button>
        </form>
    </div>
</div>
