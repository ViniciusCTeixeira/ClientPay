<?php

// Router seguro para o servidor embutido do PHP. Nenhum arquivo interno é
// servido diretamente; todas as requisições passam pelo front controller.
$uriPath = rawurldecode((string)(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'));
$allowed = ['/', '/index.php'];

if ($uriPath === '/assets/app.js') {
    return false;
}

if (!in_array($uriPath, $allowed, true)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Recurso não encontrado.';
    return true;
}

require __DIR__ . '/index.php';
return true;
