<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::check($_POST['csrf_token'] ?? null)) {
    Flash::set('danger', 'Ação inválida.');
    header('Location: ?p=invoices/index');
    exit;
}

$result = Invoice::refreshStatuses();
$today = date('d/m/Y');
$msg = "Status atualizados em $today. {$result['overdue']} marcadas como vencidas e {$result['pending']} retornaram para pendente.";
Flash::set('success', $msg);
header('Location: ?p=invoices/index');
exit;
