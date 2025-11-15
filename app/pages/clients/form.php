<?php
$id = (int)($_GET['id'] ?? 0);
$data = $id ? Client::find($id) : ['name' => '', 'email' => '', 'whatsapp' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = Validation::required($_POST, ['name']);
    if (!$errors) {
        $payload = ['name' => $_POST['name'], 'email' => $_POST['email'] ?? null, 'whatsapp' => $_POST['whatsapp'] ?? null];
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
?>
<h3><?= $id ? 'Editar' : 'Novo' ?> cliente</h3>
<form method="post" class="row g-3">
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
