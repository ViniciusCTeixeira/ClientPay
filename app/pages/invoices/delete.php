<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::check($_POST['csrf_token'] ?? null)) {
    Flash::set('danger', 'Ação inválida.');
    header('Location: ?p=invoices/index');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id) {
    $invoice = Invoice::find($id);
    if ($invoice && $invoice['status'] === 'paid') {
        Flash::set('danger', 'Uma mensalidade paga não pode ser cancelada diretamente.');
    } else {
        Invoice::delete($id);
        Flash::set('success', 'Mensalidade cancelada.');
    }
}
header('Location: ?p=invoices/index');
exit;
