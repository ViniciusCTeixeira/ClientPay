<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::check($_POST['csrf_token'] ?? null)) {
    Flash::set('danger', 'Ação inválida.');
    header('Location: ?p=clients/index');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id) {
    Client::delete($id);
    Flash::set('success', 'Cliente e sites ativos arquivados. O histórico financeiro foi preservado.');
}
header('Location: ?p=clients/index');
exit;
