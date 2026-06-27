<?php

declare(strict_types=1);

putenv('CLIENTPAY_ADMIN_PASSWORD=TestPassword123!');

require __DIR__ . '/../app/lib/Database.php';
require __DIR__ . '/../app/models/User.php';
require __DIR__ . '/../app/models/Client.php';
require __DIR__ . '/../app/models/Site.php';
require __DIR__ . '/../app/models/PlanHistory.php';
require __DIR__ . '/../app/models/Invoice.php';
require __DIR__ . '/../app/models/Template.php';

date_default_timezone_set('America/Sao_Paulo');
Database::init([
    'db' => [
        'path' => ':memory:',
        'sql' => __DIR__ . '/../app/sql/schema.sql',
    ],
]);

$tests = 0;
$assert = static function (bool $condition, string $message) use (&$tests): void {
    $tests++;
    if (!$condition) {
        throw new RuntimeException($message);
    }
};

$admin = User::findByEmail('admin@example.com');
$assert($admin !== null && $admin['role'] === 'admin', 'O usuário inicial deve ser administrador.');
$assert(count(TemplateM::all()) === 3, 'Os três templates padrão devem existir.');

$clientA = Client::create(['name' => 'Cliente A']);
$clientB = Client::create(['name' => 'Cliente B']);
$site = Site::create([
    'client_id' => $clientA,
    'name' => 'Site principal',
    'creation_cost' => 500,
    'current_monthly_fee' => 100,
]);

$today = date('Y-m-d');
PlanHistory::add($site, 150, $today, 'Correção no mesmo dia');
$assert(PlanHistory::resolveAmountForDate($site, $today) === 150.0, 'Reajuste na mesma data deve substituir o valor anterior.');

PlanHistory::add($site, 200, '2099-01-01', 'Reajuste futuro');
$assert((float)Site::find($site)['current_monthly_fee'] === 150.0, 'Reajuste futuro não pode alterar o valor atual.');

$invoice = Invoice::create([
    'site_id' => $site,
    'client_id' => $clientA,
    'amount' => 150,
    'due_date' => $today,
    'status' => 'paid',
    'payment_method' => 'pix',
    'payment_reference' => 'TESTE',
]);
$savedInvoice = Invoice::find($invoice);
$assert($savedInvoice['paid_at'] === $today, 'Mensalidade paga deve registrar a data do pagamento.');
$assert($savedInvoice['payment_method'] === 'pix', 'Forma de pagamento deve ser preservada.');

$transferBlocked = false;
try {
    Site::update($site, [
        'client_id' => $clientB,
        'name' => 'Site principal',
        'creation_cost' => 500,
        'current_monthly_fee' => 150,
    ]);
} catch (InvalidArgumentException $e) {
    $transferBlocked = true;
}
$assert($transferBlocked, 'Site com mensalidades não pode trocar de cliente.');

$negativeBlocked = false;
try {
    Site::create(['client_id' => $clientA, 'name' => 'Inválido', 'current_monthly_fee' => -1]);
} catch (InvalidArgumentException $e) {
    $negativeBlocked = true;
}
$assert($negativeBlocked, 'Valores negativos devem ser rejeitados.');

Client::delete($clientA);
$assert(Invoice::find($invoice) !== null, 'Arquivar cliente não pode apagar o histórico financeiro.');
$assert(Site::count() === 0, 'Sites do cliente arquivado não devem permanecer ativos.');

echo "OK - {$tests} verificações concluídas." . PHP_EOL;
