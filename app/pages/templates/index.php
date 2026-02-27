<?php $items = TemplateM::all(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Templates de mensagens</h3>
    <a href="?p=templates/form" class="btn btn-primary">+ Novo</a>
</div>
<table class="table table-striped">
    <thead>
    <tr>
        <th>Tipo</th>
        <th>Título</th>
        <th>Ativo</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $r): ?>
        <tr>
            <td><code>
                    <?php if ($r['code'] == 'before_due') { ?>
                        Pré-vencimento
                    <?php } ?>
                    <?php if ($r['code'] == 'on_due') { ?>
                        No vencimento
                    <?php } ?>
                    <?php if ($r['code'] == 'overdue') { ?>
                        Vencido
                    <?php } ?>
                </code></td>
            <td><?= htmlspecialchars($r['title']) ?></td>
            <td><?= $r['active'] ? 'Sim' : 'Não' ?></td>
            <td class="text-end">
                <a class="btn btn-sm btn-outline-secondary" href="?p=templates/form&id=<?= $r['id'] ?>">Editar</a>
                <form method="post" action="?p=templates/delete" class="d-inline">
                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                    <button class="btn btn-sm btn-outline-danger" type="submit">Excluir</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
