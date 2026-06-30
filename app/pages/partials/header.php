<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($cfg['app']['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <link href="assets/app.css" rel="stylesheet">
</head>
<?php $isAuthenticated = Auth::check(); ?>
<body class="<?= $isAuthenticated ? 'app-authenticated' : 'app-guest' ?>">
<div class="<?= $isAuthenticated ? 'app-shell container-xl' : 'guest-shell' ?>">
