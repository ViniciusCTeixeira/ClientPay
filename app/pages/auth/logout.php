<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::check($_POST['csrf_token'] ?? null)) {
    Flash::set('danger', 'Ação inválida.');
    header('Location: ?p=invoices/index');
    exit;
}

Auth::logout();
header('Location: ?p=auth/login');
exit;
