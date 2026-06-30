<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::check($_POST['csrf_token'] ?? null)) {
        Flash::set('danger', 'Sessão inválida. Atualize a página e tente novamente.');
    } else {
        try {
            $ok = Auth::attempt(
                $_POST['email'] ?? '',
                $_POST['password'] ?? '',
                $_SERVER['REMOTE_ADDR'] ?? ''
            );
            if ($ok) {
                Flash::set('success', 'Bem-vindo!');
                header('Location: ?p=invoices/index');
                exit;
            }
            Flash::set('danger', 'Credenciais inválidas.');
        } catch (RuntimeException $e) {
            Flash::set('danger', $e->getMessage());
        }
    }
}
?>
<div class="login-layout">
    <section class="login-hero">
        <div class="login-brand"><span class="brand-mark">CP</span><span>ClientPay</span></div>
        <div class="login-hero-content">
            <span class="login-eyebrow">Gestão de receita recorrente</span>
            <h1>Controle que dá clareza ao seu negócio.</h1>
            <p>Clientes, sites, mensalidades e cobranças organizados em uma rotina simples, segura e previsível.</p>
            <div class="login-benefits">
                <div class="login-benefit"><span class="login-check">✓</span><span>Visão rápida de pendências e recebimentos</span></div>
                <div class="login-benefit"><span class="login-check">✓</span><span>Cobranças personalizadas pelo WhatsApp</span></div>
                <div class="login-benefit"><span class="login-check">✓</span><span>Histórico comercial preservado</span></div>
            </div>
        </div>
        <div class="login-hero-footer">Uma operação mais organizada começa por uma visão mais clara.</div>
    </section>
    <section class="login-form-side">
        <div class="login-card">
            <h2>Bem-vindo</h2>
            <p>Entre com seus dados para acessar o painel.</p>
            <form method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
            <div class="mb-3">
                <label class="form-label" for="login-email">E-mail</label>
                <input id="login-email" name="email" type="email" class="form-control" placeholder="seu@email.com" autocomplete="username" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label" for="login-password">Senha</label>
                <input id="login-password" name="password" type="password" class="form-control" placeholder="Digite sua senha" autocomplete="current-password" required>
            </div>
                <button class="btn btn-primary w-100">Entrar no ClientPay</button>
            </form>
            <p class="login-security"><span aria-hidden="true">●</span> Acesso protegido e sessão segura</p>
        </div>
    </section>
</div>
