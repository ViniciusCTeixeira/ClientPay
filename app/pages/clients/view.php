<?php
$id = (int)($_GET['id'] ?? 0);
$c = Client::find($id);
if (!$c) {
    echo 'Cliente não encontrado';
    return;
}
$sites = Site::allByClient($id);
?>
<h3><?= htmlspecialchars($c['name']) ?></h3>
<p>Email: <?= htmlspecialchars((string)$c['email']) ?> • WhatsApp: <?= htmlspecialchars((string)$c['whatsapp']) ?></p>
<hr>
<h5>Sites do cliente</h5>
<p><a class="btn btn-sm btn-primary" href="?p=sites/form&client_id=<?= $id ?>">+ Novo site</a></p>
<table class="table table-sm">
    <thead>
    <tr>
        <th>#</th>
        <th>Nome</th>
        <th>Domínio</th>
        <th>Criação</th>
        <th>Mensalidade atual</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($sites as $s): ?>
        <tr>
            <td><?= $s['id'] ?></td>
            <td><?= htmlspecialchars($s['name']) ?></td>
            <td><?= htmlspecialchars((string)$s['domain']) ?></td>
            <td>R$ <?= Formatter::money($s['creation_cost']) ?></td>
            <td>R$ <?= Formatter::money($s['current_monthly_fee']) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
