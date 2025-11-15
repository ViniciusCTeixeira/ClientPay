<?php
$id = (int)($_GET['id'] ?? 0);
if ($id) {
    if (isset($_SESSION['uid']) && (int)$_SESSION['uid'] === $id) {
        Flash::set('danger', 'Voce nao pode excluir o usuario logado.');
    } else {
        User::delete($id);
        Flash::set('success', 'Usuario excluido.');
    }
}
header('Location: ?p=users/index');
