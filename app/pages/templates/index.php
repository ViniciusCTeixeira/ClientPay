<?php $items = TemplateM::all(); ?>
<div class="page-header">
    <div class="page-heading"><h3>Mensagens</h3><p>Personalize os textos usados antes, no dia e depois do vencimento.</p></div>
</div>
<div class="content-panel table-responsive">
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
            <td><span class="badge text-bg-secondary">
                    <?php if ($r['code'] == 'before_due') { ?>
                        Pré-vencimento
                    <?php } ?>
                    <?php if ($r['code'] == 'on_due') { ?>
                        No vencimento
                    <?php } ?>
                    <?php if ($r['code'] == 'overdue') { ?>
                        Vencido
                    <?php } ?>
                </span></td>
            <td><?= htmlspecialchars($r['title']) ?></td>
            <td><span class="badge text-bg-<?= $r['active'] ? 'success' : 'secondary' ?>"><?= $r['active'] ? 'Ativo' : 'Inativo' ?></span></td>
            <td class="text-end">
                <a class="btn btn-sm btn-outline-secondary" href="?p=templates/form&id=<?= $r['id'] ?>">Editar</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
