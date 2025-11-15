<?php
$id = (int)($_GET['id'] ?? 0);
$inv = Invoice::find($id);
if (!$inv) {
    echo 'Mensalidade não encontrada';
    return;
}
$today = date('Y-m-d');
$code = ($inv['due_date'] > $today) ? 'before_due' : (($inv['due_date'] == $today) ? 'on_due' : 'overdue');
$tmp = TemplateM::findByCode($code);

$vars = [
        'client_name' => $inv['client_name'],
        'site_name' => $inv['site_name'],
        'due_date' => date('d/m/Y', strtotime($inv['due_date'])),
        'amount' => Formatter::money($inv['amount']),
];
$body = TemplateEngine::render($tmp['body'] ?? '', $vars);
$waNumber = preg_replace('/\D+/', '', $inv['whatsapp'] ?? '');
$waLink = $waNumber ? ('https://wa.me/' . $waNumber . '?text=' . rawurlencode($body)) : null;
?>
<h3>Pré-visualização da mensagem</h3>
<p><strong>Template:</strong> <?= htmlspecialchars($tmp['title'] ?? $code) ?> (<?= htmlspecialchars($code) ?>)</p>
<div class="card">
    <div class="card-body">
        <pre class="mb-0"><?= htmlspecialchars($body) ?></pre>
    </div>
</div>
<?php if ($waLink): ?>
    <p class="mt-3"><a class="btn btn-success" href="<?= $waLink ?>" target="_blank">Enviar via WhatsApp</a></p>
<?php else: ?>
    <div class="alert alert-warning mt-3">Cliente sem número de WhatsApp cadastrado.</div>
<?php endif; ?>
<p class="text-muted">Variáveis suportadas: {client_name}, {site_name}, {due_date}, {amount}</p>
