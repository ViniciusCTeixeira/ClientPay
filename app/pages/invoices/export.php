<?php
$q = trim($_GET['q'] ?? '');
$status = in_array($_GET['status'] ?? '', ['pending', 'paid', 'overdue', 'canceled'], true) ? $_GET['status'] : '';
$ym = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $_GET['ym'] ?? '') ? $_GET['ym'] : null;
$items = Invoice::all(0, 100000, $q ?: null, $status ?: null, $ym);

while (ob_get_level() > 0) {
    ob_end_clean();
}
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="mensalidades-' . ($ym ?: date('Y-m')) . '.csv"');
$output = fopen('php://output', 'wb');
fwrite($output, "\xEF\xBB\xBF");
fputcsv($output, ['Cliente', 'Site', 'Valor', 'Vencimento', 'Status', 'Pagamento', 'Forma', 'Referência'], ';');
$labels = ['pending' => 'Pendente', 'paid' => 'Pago', 'overdue' => 'Vencido', 'canceled' => 'Cancelado'];
foreach ($items as $item) {
    fputcsv($output, [
        $item['client_name'],
        $item['site_name'],
        Formatter::money($item['amount']),
        Formatter::dateBr($item['due_date']),
        $labels[$item['status']] ?? $item['status'],
        $item['paid_at'] ? Formatter::dateBr($item['paid_at']) : '',
        $item['payment_method'] ?? '',
        $item['payment_reference'] ?? '',
    ], ';');
}
fclose($output);
exit;
