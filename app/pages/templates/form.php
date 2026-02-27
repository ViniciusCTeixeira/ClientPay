<?php
$id = (int)($_GET['id'] ?? 0);
$data = $id ? TemplateM::find($id) : ['code' => 'before_due', 'title' => '', 'body' => '', 'active' => 1];
if ($id && !$data) {
    echo 'Template não encontrado.';
    return;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::check($_POST['csrf_token'] ?? null)) {
        Flash::set('danger', 'Sessão inválida. Atualize a página e tente novamente.');
    } else {
        $payload = [
            'code' => $_POST['code'],
            'title' => $_POST['title'],
            'body' => $_POST['body'],
            'active' => isset($_POST['active']) ? 1 : 0
        ];
        $data = array_merge($data, $payload);
        $allowedCodes = ['before_due', 'on_due', 'overdue'];
        if (!in_array($payload['code'], $allowedCodes, true) || trim($payload['title']) === '' || trim($payload['body']) === '') {
            Flash::set('danger', 'Preencha os campos obrigatórios corretamente.');
        } else {
            TemplateM::upsert($id ?: null, $payload);
            Flash::set('success', 'Template salvo');
            header('Location: ?p=templates/index');
            exit;
        }
    }
}
?>
<h3><?= $id ? 'Editar' : 'Novo' ?> template</h3>
<form method="post" class="row g-3">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
    <div class="col-md-4">
        <label class="form-label">Tipo</label>
        <select name="code" class="form-select">
            <option value="before_due" <?= $data['code'] === 'before_due' ? 'selected' : '' ?>>Pré-vencimento</option>
            <option value="on_due" <?= $data['code'] === 'on_due' ? 'selected' : '' ?>>No vencimento</option>
            <option value="overdue" <?= $data['code'] === 'overdue' ? 'selected' : '' ?>>Vencido</option>
        </select>
    </div>
    <div class="col-md-8">
        <label class="form-label">Título</label>
        <input name="title" class="form-control" value="<?= htmlspecialchars($data['title']) ?>">
    </div>
    <div class="col-12">
        <label class="form-label">Corpo</label>
        <textarea name="body" rows="6" class="form-control"><?= htmlspecialchars($data['body']) ?></textarea>
        <div class="form-text">Use variáveis: {client_name}, {site_name}, {due_date}, {amount}</div>
    </div>
    <div class="col-12 form-check">
        <input class="form-check-input" type="checkbox" name="active"
               id="tactive" <?= $data['active'] ? 'checked' : '' ?>>
        <label class="form-check-label" for="tactive">Ativo</label>
    </div>
    <div class="col-12">
        <button class="btn btn-primary">Salvar</button>
    </div>
</form>
