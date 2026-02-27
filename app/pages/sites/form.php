<?php
$id = (int)($_GET['id'] ?? 0);
$clientId = (int)($_GET['client_id'] ?? 0);
$data = $id ? Site::find($id) : ['client_id' => $clientId, 'name' => '', 'domain' => '', 'creation_cost' => '0', 'current_monthly_fee' => '0'];
if ($id && !$data) {
    echo 'Site não encontrado.';
    return;
}
$clients = Client::all(0, 1000);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::check($_POST['csrf_token'] ?? null)) {
        Flash::set('danger', 'Sessão inválida. Atualize a página e tente novamente.');
    } else {
        $action = $_POST['form_action'] ?? 'save_site';
        if ($action === 'add_history') {
            if (!$id) {
                Flash::set('danger', 'Site inválido para adicionar histórico.');
            } else {
                try {
                    PlanHistory::add(
                        $id,
                        (float)($_POST['new_amount'] ?? 0),
                        $_POST['from'] ?: date('Y-m-d'),
                        trim((string)($_POST['notes'] ?? '')) ?: null
                    );
                    Flash::set('success', 'Histórico adicionado e mensalidade atual atualizada.');
                    header('Location: ?p=sites/form&id=' . $id);
                    exit;
                } catch (InvalidArgumentException $e) {
                    Flash::set('danger', $e->getMessage());
                }
            }
        } else {
            $errors = Validation::required($_POST, ['client_id', 'name']);
            $data['client_id'] = (int)($_POST['client_id'] ?? 0);
            $data['name'] = trim((string)($_POST['name'] ?? ''));
            $data['domain'] = trim((string)($_POST['domain'] ?? '')) ?: null;
            $data['creation_cost'] = (float)($_POST['creation_cost'] ?? 0);
            if (!$id) {
                $data['current_monthly_fee'] = (float)($_POST['current_monthly_fee'] ?? 0);
            }

            if (!$errors) {
                $monthlyFee = $id ? (float)$data['current_monthly_fee'] : (float)($_POST['current_monthly_fee'] ?? 0);
                $payload = [
                    'client_id' => (int)$_POST['client_id'],
                    'name' => trim((string)$_POST['name']),
                    'domain' => trim((string)($_POST['domain'] ?? '')) ?: null,
                    'creation_cost' => (float)($_POST['creation_cost'] ?? 0),
                    'current_monthly_fee' => $monthlyFee,
                ];
                if ($id) {
                    Site::update($id, $payload);
                    Flash::set('success', 'Site atualizado');
                } else {
                    $id = Site::create($payload);
                    Flash::set('success', 'Site criado');
                }
                header('Location: ?p=sites/index');
                exit;
            } else {
                Flash::set('danger', 'Preencha os campos obrigatórios.');
            }
        }
    }
}
?>
<h3><?= $id ? 'Editar' : 'Novo' ?> site</h3>
<form method="post" class="row g-3">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
    <input type="hidden" name="form_action" value="save_site">
    <div class="col-md-6">
        <label class="form-label">Cliente *</label>
        <select name="client_id" class="form-select" required>
            <option value="">Selecione...</option>
            <?php foreach ($clients as $c): ?>
                <option value="<?= $c['id'] ?>" <?= ($data['client_id'] == $c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Nome do site *</label>
        <input name="name" class="form-control" value="<?= htmlspecialchars($data['name']) ?>" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Domínio</label>
        <input name="domain" class="form-control" value="<?= htmlspecialchars((string)$data['domain']) ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label">Valor de criação (R$)</label>
        <input name="creation_cost" type="number" step="0.01" class="form-control"
               value="<?= htmlspecialchars((string)$data['creation_cost']) ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label">Mensalidade atual (R$)</label>
        <input name="current_monthly_fee" type="number" step="0.01" class="form-control"
               value="<?= htmlspecialchars((string)$data['current_monthly_fee']) ?>" <?= $id ? 'disabled' : '' ?>>
        <div class="form-text">Alterações devem ser registradas no histórico.</div>
    </div>
    <div class="col-12">
        <button class="btn btn-primary">Salvar</button>
    </div>
</form>

<?php if ($id): $hist = PlanHistory::bySite($id); ?>
    <hr>
    <h5>Histórico de mensalidade</h5>
    <form method="post" class="row g-2" action="?p=sites/form&id=<?= $id ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
        <input type="hidden" name="form_action" value="add_history">
        <div class="col-md-3">
            <label class="form-label">Novo valor (R$)</label>
            <input name="new_amount" type="number" step="0.01" min="0.01" class="form-control" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Válido a partir de</label>
            <input name="from" type="date" class="form-control" value="<?= date('Y-m-01') ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Notas</label>
            <input name="notes" class="form-control">
        </div>
        <div class="col-md-2 d-grid align-items-end">
            <button class="btn btn-outline-primary mt-4">Adicionar</button>
        </div>
    </form>
    <table class="table table-sm mt-3">
        <thead>
        <tr>
            <th>Válido desde</th>
            <th>Valor</th>
            <th>Notas</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($hist as $h): ?>
            <tr>
                <td><?= Formatter::dateBr($h['effective_from']) ?></td>
                <td>R$ <?= Formatter::money($h['amount']) ?></td>
                <td><?= htmlspecialchars((string)$h['notes']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
