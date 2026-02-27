<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::check($_POST['csrf_token'] ?? null)) {
    Flash::set('danger', 'Ação inválida.');
    header('Location: ?p=templates/index');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id) {
    TemplateM::delete($id);
    Flash::set('success', 'Template excluído');
}
header('Location: ?p=templates/index');
exit;
