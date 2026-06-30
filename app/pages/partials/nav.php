<?php
$flash = Flash::get();
$currentSection = explode('/', (string)$page)[0] ?? '';
$userName = (string)($_SESSION['uname'] ?? 'Usuário');
$initial = mb_strtoupper(mb_substr($userName, 0, 1));
?>
<?php if (Auth::check()): ?>
    <nav class="navbar navbar-expand-lg app-navbar" aria-label="Navegação principal">
        <div class="container-fluid px-0">
            <a class="navbar-brand app-brand" href="?p=invoices/index" aria-label="ClientPay — início">
                <span class="brand-mark">CP</span>
                <span class="brand-copy">
                    <strong>ClientPay</strong>
                    <small>Gestão recorrente</small>
                </span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navcol"
                    aria-controls="navcol" aria-expanded="false" aria-label="Abrir menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navcol">
                <ul class="navbar-nav app-nav-links mx-lg-auto">
                    <li class="nav-item"><a class="nav-link <?= $currentSection === 'clients' ? 'active' : '' ?>" href="?p=clients/index">Clientes</a></li>
                    <li class="nav-item"><a class="nav-link <?= $currentSection === 'sites' ? 'active' : '' ?>" href="?p=sites/index">Sites</a></li>
                    <li class="nav-item"><a class="nav-link <?= $currentSection === 'invoices' ? 'active' : '' ?>" href="?p=invoices/index">Mensalidades</a></li>
                    <li class="nav-item"><a class="nav-link <?= $currentSection === 'templates' ? 'active' : '' ?>" href="?p=templates/index">Mensagens</a></li>
                    <?php if (Auth::isAdmin()): ?>
                        <li class="nav-item"><a class="nav-link <?= $currentSection === 'users' ? 'active' : '' ?>" href="?p=users/index">Equipe</a></li>
                    <?php endif; ?>
                </ul>
                <div class="dropdown app-user-menu">
                    <button class="user-menu-trigger dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="user-avatar"><?= htmlspecialchars($initial) ?></span>
                        <span class="user-copy">
                            <strong><?= htmlspecialchars($userName) ?></strong>
                            <small><?= Auth::isAdmin() ? 'Administrador' : 'Operador' ?></small>
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow-sm">
                        <a class="dropdown-item" href="?p=auth/change_password">Trocar senha</a>
                        <div class="dropdown-divider"></div>
                        <form method="post" action="?p=auth/logout" class="m-0">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                            <button class="dropdown-item text-danger" type="submit">Sair da conta</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </nav>
<?php endif; ?>
<?php if ($flash): ?>
    <div class="alert app-alert alert-<?= htmlspecialchars($flash['type']) ?>" role="alert">
        <span class="alert-dot" aria-hidden="true"></span>
        <?= htmlspecialchars($flash['msg']) ?>
    </div>
<?php endif; ?>
