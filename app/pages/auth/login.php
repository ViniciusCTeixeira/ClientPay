<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ok = Auth::attempt($_POST['email'] ?? '', $_POST['password'] ?? '');
    if ($ok) {
        Flash::set('success', 'Bem-vindo!');
        header('Location: ?p=invoices/index');
        exit;
    }
    Flash::set('danger', 'Credenciais inválidas.');
}
?>
<div class="row justify-content-center">
    <div class="col-md-4">
        <h3>Login</h3>
        <form method="post" class="mt-3">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input name="email" type="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Senha</label>
                <input name="password" type="password" class="form-control" required>
            </div>
            <button class="btn btn-primary w-100">Entrar</button>
            <p class="text-muted small mt-2">admin@example.com / admin123</p>
        </form>
    </div>
</div>
