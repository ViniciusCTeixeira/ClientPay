<?php
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Permissions-Policy: camera=(), microphone=(), geolocation=()");
header("Content-Security-Policy: default-src 'self'; base-uri 'self'; frame-ancestors 'none'; form-action 'self'; style-src 'self' https://cdn.jsdelivr.net; script-src 'self' https://cdn.jsdelivr.net; img-src 'self' data:");

if (session_status() !== PHP_SESSION_ACTIVE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'path' => '/',
        'httponly' => true,
        'secure' => $isHttps,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    session_start();
}

$cfg = require __DIR__ . '/config.php';
date_default_timezone_set($cfg['app']['timezone']);

set_exception_handler(static function (Throwable $e): void {
    error_log(sprintf('[ClientPay] %s in %s:%d', $e->getMessage(), $e->getFile(), $e->getLine()));
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    http_response_code(500);
    $debug = filter_var(getenv('CLIENTPAY_DEBUG') ?: false, FILTER_VALIDATE_BOOL);
    echo '<!doctype html><html lang="pt-br"><meta charset="utf-8"><title>Erro</title>';
    echo '<body><h1>Não foi possível concluir a operação.</h1><p>Tente novamente. Se o problema persistir, consulte o log do servidor.</p>';
    if ($debug) {
        echo '<pre>' . htmlspecialchars((string)$e, ENT_QUOTES, 'UTF-8') . '</pre>';
    }
    echo '</body></html>';
});

require __DIR__ . '/app/lib/Database.php';
require __DIR__ . '/app/lib/Auth.php';
require __DIR__ . '/app/lib/Csrf.php';
require __DIR__ . '/app/lib/TemplateEngine.php';
require __DIR__ . '/app/lib/Validation.php';
require __DIR__ . '/app/lib/Flash.php';
require __DIR__ . '/app/lib/Formatter.php';
require __DIR__ . '/app/lib/Paginator.php';
require __DIR__ . '/app/models/User.php';
require __DIR__ . '/app/models/Client.php';
require __DIR__ . '/app/models/Site.php';
require __DIR__ . '/app/models/PlanHistory.php';
require __DIR__ . '/app/models/Template.php';
require __DIR__ . '/app/models/Invoice.php';

Database::init($cfg);
Auth::configure($cfg['app']);

$page = $_GET['p'] ?? 'invoices/index';
$publicPages = ['auth/login', 'auth/logout'];
if (!in_array($page, $publicPages, true)) {
    Auth::requireLogin();
}

if (!is_string($page) || !preg_match('#^[a-z0-9_/-]+$#i', $page)) {
    $page = '';
}
$pagesRoot = realpath(__DIR__ . '/app/pages');
$path = $page !== '' ? realpath($pagesRoot . DIRECTORY_SEPARATOR . $page . '.php') : false;
if (!$path || !str_starts_with($path, $pagesRoot . DIRECTORY_SEPARATOR)) {
    http_response_code(404);
    $pageContent = '<div class="alert alert-warning">Página não encontrada.</div>';
} else {
    ob_start();
    include $path;
    $pageContent = (string)ob_get_clean();
}

include __DIR__ . '/app/pages/partials/header.php';
include __DIR__ . '/app/pages/partials/nav.php';
echo '<main class="' . (Auth::check() ? 'app-main' : 'guest-main') . '">';
echo $pageContent;
echo '</main>';
include __DIR__ . '/app/pages/partials/footer.php';
