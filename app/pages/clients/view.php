<?php
$id = (int)($_GET['id'] ?? 0);
$c = Client::find($id);
if (!$c) {
    echo 'Cliente não encontrado';
    return;
}
$sites = Site::allByClient($id);
?>
<div class="page-header"><div class="page-heading"><h3><?= htmlspecialchars($c['name']) ?></h3><p><?= htmlspecialchars((string)$c['email']) ?><?= $c['email'] && $c['whatsapp'] ? ' · ' : '' ?><?= htmlspecialchars((string)$c['whatsapp']) ?></p></div><div class="page-actions"><a class="btn btn-primary" href="?p=sites/form&client_id=<?= $id ?>">+ Novo site</a><a class="btn btn-soft" href="?p=clients/form&id=<?= $id ?>">Editar cliente</a></div></div>
<div class="section-heading"><h5>Sites do cliente</h5><p>Projetos ativos e seus valores atuais.</p></div>
<div class="content-panel table-responsive"><table class="table table-sm">
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
</table></div>
