<?php $flash = Flash::get(); ?>
<nav class="navbar navbar-expand-lg bg-body-tertiary mb-3 rounded">
    <div class="container">
        <a class="navbar-brand" href="?p=invoices/index">ClientPay</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navcol"><span
                    class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="navcol">
            <ul class="navbar-nav me-auto">
                <?php if (Auth::check()): ?>
                    <li class="nav-item"><a class="nav-link" href="?p=clients/index">Clientes</a></li>
                    <li class="nav-item"><a class="nav-link" href="?p=sites/index">Sites</a></li>
                    <li class="nav-item"><a class="nav-link" href="?p=invoices/index">Mensalidades</a></li>
                    <li class="nav-item"><a class="nav-link" href="?p=templates/index">Templates</a></li>
                    <li class="nav-item"><a class="nav-link" href="?p=users/index">Usuarios</a></li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav ms-auto">
                <?php if (Auth::check()): ?>
                    <li class="nav-item"><a class="nav-link" href="?p=auth/change_password">Trocar senha</a></li>
                    <li class="nav-item"><p class="navbar-text me-2 mb-0">
                            Olá, <?= htmlspecialchars($_SESSION['uname'] ?? '') ?></p></li>
                    <li class="nav-item d-flex align-items-center">
                        <form method="post" action="?p=auth/logout" class="mb-0">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                            <button class="btn btn-sm btn-outline-secondary" type="submit">Sair</button>
                        </form>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<?php if ($flash): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['msg']) ?></div>
<?php endif; ?>
