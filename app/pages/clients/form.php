<?php
$id = (int)($_GET['id'] ?? 0);
$data = $id ? Client::find($id) : ['name' => '', 'email' => '', 'whatsapp' => ''];
if ($id && !$data) {
    echo 'Cliente não encontrado.';
    return;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::check($_POST['csrf_token'] ?? null)) {
        Flash::set('danger', 'Sessão inválida. Atualize a página e tente novamente.');
    } else {
        $errors = Validation::required($_POST, ['name']);
        $data['name'] = trim((string)($_POST['name'] ?? ''));
        $data['email'] = trim((string)($_POST['email'] ?? ''));
        $data['whatsapp'] = trim((string)($_POST['whatsapp'] ?? ''));
        if (!$errors) {
            $payload = [
                'name' => trim($_POST['name']),
                'email' => trim((string)($_POST['email'] ?? '')) ?: null,
                'whatsapp' => trim((string)($_POST['whatsapp'] ?? '')) ?: null,
            ];
            if ($id) {
                Client::update($id, $payload);
                Flash::set('success', 'Cliente atualizado');
            } else {
                $id = Client::create($payload);
                Flash::set('success', 'Cliente criado');
            }
            header('Location: ?p=clients/index');
            exit;
        } else {
            Flash::set('danger', 'Preencha os campos obrigatórios.');
        }
    }
}
?>
<h3><?= $id ? 'Editar' : 'Novo' ?> cliente</h3>
<form method="post" class="row g-3">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
    <div class="col-12">
        <label class="form-label">Nome *</label>
        <input name="name" class="form-control" value="<?= htmlspecialchars($data['name']) ?>" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Email</label>
        <input name="email" type="email" class="form-control" value="<?= htmlspecialchars((string)$data['email']) ?>">
    </div>
    <div class="col-md-6">
        <label class="form-label">WhatsApp (somente números)</label>
        <input name="whatsapp" class="form-control" value="<?= htmlspecialchars((string)$data['whatsapp']) ?>">
    </div>
    <div class="col-12">
        <button class="btn btn-primary">Salvar</button>
    </div>
</form>
