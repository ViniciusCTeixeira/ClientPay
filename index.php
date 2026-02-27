<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => $isHttps,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    session_start();
}

ob_start();
$cfg = require __DIR__ . '/config.php';
require __DIR__ . '/app/lib/Database.php';
require __DIR__ . '/app/lib/Auth.php';
require __DIR__ . '/app/lib/Csrf.php';
require __DIR__ . '/app/lib/TemplateEngine.php';
require __DIR__ . '/app/lib/Validation.php';
require __DIR__ . '/app/lib/Flash.php';
require __DIR__ . '/app/lib/Formatter.php';
require __DIR__ . '/app/models/User.php';
require __DIR__ . '/app/models/Client.php';
require __DIR__ . '/app/models/Site.php';
require __DIR__ . '/app/models/PlanHistory.php';
require __DIR__ . '/app/models/Template.php';
require __DIR__ . '/app/models/Invoice.php';

Database::init($cfg);

$page = $_GET['p'] ?? 'invoices/index';
$publicPages = ['auth/login', 'auth/logout'];
if (!in_array($page, $publicPages)) {
    Auth::requireLogin();
}

$path = __DIR__ . '/app/pages/' . str_replace('..', '', $page) . '.php';
if (!is_file($path)) {
    http_response_code(404);
    echo 'Página não encontrada';
    exit;
}

include __DIR__ . '/app/pages/partials/header.php';
include __DIR__ . '/app/pages/partials/nav.php';
include $path;
include __DIR__ . '/app/pages/partials/footer.php';

ob_end_flush();
