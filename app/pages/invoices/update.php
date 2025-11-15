<?php
$result = Invoice::refreshStatuses();
$today = date('d/m/Y');
$msg = "Status atualizados em $today. {$result['overdue']} marcadas como vencidas e {$result['pending']} retornaram para pendente.";
Flash::set('success', $msg);
header('Location: ?p=invoices/index');
exit;
