<?php
$id = (int)($_GET['id'] ?? 0);
$clients = Client::all(0, 1000, null, $id > 0);
$sites = Site::all(0, 1000, null, $id > 0);
$data = $id ? Invoice::find($id) : ['site_id' => '', 'client_id' => '', 'amount' => '', 'due_date' => date('Y-m-d'), 'status' => 'pending', 'notes' => '', 'paid_at' => '', 'payment_method' => '', 'payment_reference' => ''];
if ($id && !$data) {
    echo 'Mensalidade não encontrada.';
    return;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::check($_POST['csrf_token'] ?? null)) {
        Flash::set('danger', 'Sessão inválida. Atualize a página e tente novamente.');
    } else {
        $errors = Validation::required($_POST, ['site_id', 'client_id', 'due_date']);
        $data = array_merge($data, [
            'site_id' => (int)($_POST['site_id'] ?? 0),
            'client_id' => (int)($_POST['client_id'] ?? 0),
            'amount' => (string)($_POST['amount'] ?? ''),
            'due_date' => (string)($_POST['due_date'] ?? ''),
            'status' => (string)($_POST['status'] ?? 'pending'),
            'notes' => (string)($_POST['notes'] ?? ''),
            'paid_at' => (string)($_POST['paid_at'] ?? ''),
            'payment_method' => (string)($_POST['payment_method'] ?? ''),
            'payment_reference' => (string)($_POST['payment_reference'] ?? ''),
        ]);
        if (!$errors) {
            $payload = [
                'site_id' => (int)$_POST['site_id'],
                'client_id' => (int)$_POST['client_id'],
                'amount' => (float)($_POST['amount'] !== '' ? $_POST['amount'] : PlanHistory::resolveAmountForDate((int)$_POST['site_id'], $_POST['due_date'])),
                'due_date' => $_POST['due_date'],
                'status' => $_POST['status'] ?? 'pending',
                'notes' => $_POST['notes'] ?? null,
                'paid_at' => $_POST['paid_at'] ?? null,
                'payment_method' => $_POST['payment_method'] ?? null,
                'payment_reference' => $_POST['payment_reference'] ?? null,
            ];
            try {
                if ($id) {
                    Invoice::update($id, $payload);
                    Flash::set('success', 'Mensalidade atualizada');
                } else {
                    $id = Invoice::create($payload);
                    Flash::set('success', 'Mensalidade criada');
                }
                header('Location: ?p=invoices/index');
                exit;
            } catch (InvalidArgumentException $e) {
                Flash::set('danger', $e->getMessage());
            } catch (PDOException $e) {
                if (str_contains($e->getMessage(), 'UNIQUE constraint failed')) {
                    Flash::set('danger', 'Já existe mensalidade para este site nesta data.');
                } else {
                    throw $e;
                }
            }
        } else {
            Flash::set('danger', 'Preencha os campos obrigatórios.');
        }
    }
}
?>
<h3><?= $id ? 'Editar' : 'Nova' ?> mensalidade</h3>
<form method="post" class="row g-3">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
    <div class="col-md-4">
        <label class="form-label">Cliente *</label>
        <select name="client_id" id="invoice-client" class="form-select" required>
            <option value="">Selecione...</option>
            <?php foreach ($clients as $c): ?>
                <option value="<?= $c['id'] ?>" <?= ($data['client_id'] == $c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?><?= !empty($c['archived_at']) ? ' — arquivado' : '' ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Site *</label>
        <select name="site_id" id="invoice-site" class="form-select" required>
            <option value="">Selecione...</option>
            <?php foreach ($sites as $s): ?>
                <option value="<?= $s['id'] ?>" data-client="<?= (int)$s['client_id'] ?>" <?= ($data['site_id'] == $s['id']) ? 'selected' : '' ?>><?= htmlspecialchars($s['name']) ?>
                    (<?= htmlspecialchars((string)$s['domain']) ?>)<?= !empty($s['archived_at']) ? ' — arquivado' : '' ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label">Vencimento *</label>
        <input type="date" name="due_date" class="form-control" value="<?= htmlspecialchars($data['due_date']) ?>"
               required>
    </div>
    <div class="col-md-2">
        <label class="form-label">Valor (R$)</label>
        <input type="number" step="0.01" name="amount" class="form-control"
               value="<?= htmlspecialchars((string)$data['amount']) ?>">
        <div class="form-text">Em branco = pegar do histórico.</div>
    </div>
    <div class="col-md-4">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            <option value="pending" <?= $data['status'] === 'pending' ? 'selected' : '' ?>>Pendente</option>
            <option value="paid" <?= $data['status'] === 'paid' ? 'selected' : '' ?>>Pago</option>
            <option value="overdue" <?= $data['status'] === 'overdue' ? 'selected' : '' ?>>Vencido</option>
            <option value="canceled" <?= $data['status'] === 'canceled' ? 'selected' : '' ?>>Cancelado</option>
        </select>
    </div>
    <div class="col-md-8">
        <label class="form-label">Notas</label>
        <input name="notes" class="form-control" value="<?= htmlspecialchars((string)$data['notes']) ?>">
    </div>
    <div class="col-md-4">
        <label class="form-label">Data do pagamento</label>
        <input type="date" name="paid_at" class="form-control" value="<?= htmlspecialchars((string)($data['paid_at'] ?? '')) ?>">
        <div class="form-text">Preenchida automaticamente ao marcar como pago.</div>
    </div>
    <div class="col-md-4">
        <label class="form-label">Forma de pagamento</label>
        <select name="payment_method" class="form-select">
            <?php $method = (string)($data['payment_method'] ?? ''); ?>
            <option value="">Não informada</option>
            <option value="pix" <?= $method === 'pix' ? 'selected' : '' ?>>Pix</option>
            <option value="boleto" <?= $method === 'boleto' ? 'selected' : '' ?>>Boleto</option>
            <option value="card" <?= $method === 'card' ? 'selected' : '' ?>>Cartão</option>
            <option value="transfer" <?= $method === 'transfer' ? 'selected' : '' ?>>Transferência</option>
            <option value="cash" <?= $method === 'cash' ? 'selected' : '' ?>>Dinheiro</option>
            <option value="other" <?= $method === 'other' ? 'selected' : '' ?>>Outro</option>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Referência do pagamento</label>
        <input name="payment_reference" class="form-control" value="<?= htmlspecialchars((string)($data['payment_reference'] ?? '')) ?>">
    </div>
    <div class="col-12">
        <button class="btn btn-primary">Salvar</button>
    </div>
</form>
