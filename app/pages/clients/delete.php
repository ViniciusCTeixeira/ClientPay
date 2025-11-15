<?php
$id = (int)($_GET['id'] ?? 0);
if ($id) {
    Client::delete($id);
    Flash::set('success', 'Cliente excluído');
}
header('Location: ?p=clients/index');
