<?php
$id = (int)($_GET['id'] ?? 0);
if ($id) {
    Invoice::delete($id);
    Flash::set('success', 'Mensalidade excluída');
}
header('Location: ?p=invoices/index');
