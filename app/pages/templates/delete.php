<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::check($_POST['csrf_token'] ?? null)) {
    Flash::set('danger', 'Ação inválida.');
    header('Location: ?p=templates/index');
    exit;
}

Flash::set('danger', 'Templates do sistema não podem ser excluídos; desative o template se necessário.');
header('Location: ?p=templates/index');
exit;
