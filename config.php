<?php
return [
    'db' => [
        'path' => getenv('CLIENTPAY_DB_PATH') ?: (__DIR__ . '/app/storage/database.sqlite'),
        'sql' => __DIR__ . '/app/sql/schema.sql',
    ],
    'app' => [
        'base_url' => '/',
        'path' => __DIR__,
        'name' => 'Gerenciador de Pagamentos',
        'timezone' => getenv('CLIENTPAY_TIMEZONE') ?: 'America/Sao_Paulo',
        'session_timeout' => max(300, (int)(getenv('CLIENTPAY_SESSION_TIMEOUT') ?: 1800)),
    ]
];
