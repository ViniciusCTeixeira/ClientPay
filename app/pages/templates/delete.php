<?php
$id = (int)($_GET['id'] ?? 0);
if ($id) {
    TemplateM::delete($id);
    Flash::set('success', 'Template excluído');
}
header('Location: ?p=templates/index');
