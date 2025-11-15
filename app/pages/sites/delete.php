<?php
$id = (int)($_GET['id'] ?? 0);
if ($id) {
    Site::delete($id);
    Flash::set('success', 'Site excluído');
}
header('Location: ?p=sites/index');
