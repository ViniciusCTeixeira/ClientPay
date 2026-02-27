<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::check($_POST['csrf_token'] ?? null)) {
    Flash::set('danger', 'Ação inválida.');
    header('Location: ?p=sites/index');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id) {
    Site::delete($id);
    Flash::set('success', 'Site excluído');
}
header('Location: ?p=sites/index');
exit;
