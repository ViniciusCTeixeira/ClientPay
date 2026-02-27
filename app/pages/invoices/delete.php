<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::check($_POST['csrf_token'] ?? null)) {
    Flash::set('danger', 'Ação inválida.');
    header('Location: ?p=invoices/index');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id) {
    Invoice::delete($id);
    Flash::set('success', 'Mensalidade excluída');
}
header('Location: ?p=invoices/index');
exit;
