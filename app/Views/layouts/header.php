<?php

use App\Core\Session;

$currentUser = Session::user();
$error = Session::flash('erro');
$success = Session::flash('sucesso');
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title><?= e($title ?? APP_NAME) ?> | <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>

<body>
    <?php if ($currentUser): ?>
        <header class="topbar">
            <a class="brand" href="<?= BASE_URL ?>/dashboard">
                <span class="brand-mark">L</span>
                <span><?= e(APP_NAME) ?></span>
            </a>

            <nav class="main-nav" aria-label="Navegação principal">
                <a href="<?= BASE_URL ?>/dashboard">Dashboard</a>
                <a href="<?= BASE_URL ?>/clientes">Clientes</a>
                <a href="<?= BASE_URL ?>/servicos">Serviços</a>
                <?php if (Session::hasRole(['Administrador', 'Gestor'])): ?>
                    <a href="<?= BASE_URL ?>/catalogo">Catálogo</a>
                    <a href="<?= BASE_URL ?>/relatorios">Relatórios</a>
                <?php endif; ?>
                <?php if (Session::hasRole(['Administrador'])): ?>
                    <a href="<?= BASE_URL ?>/utilizadores">Utilizadores</a>
                    <a href="<?= BASE_URL ?>/logs">Logs</a>
                    <a href="<?= BASE_URL ?>/backups">Backups</a>
                <?php endif; ?>
            </nav>

            <nav class="topbar-user">
                <div>
                    <strong><?= e($currentUser['nome']) ?></strong>
                    <small><?= e($currentUser['perfil']) ?></small>
                </div>
                <form action="<?= BASE_URL ?>/logout" method="post">
                    <input type="hidden" name="csrf_token" value="<?= e(Session::csrfToken()) ?>">
                    <button class="button button-outline" type="submit">Sair</button>
                </form>
            </nav>
        </header>
    <?php endif; ?>

    <main class="<?= $currentUser ? 'page-shell' : 'auth-shell' ?>">
        <?php if ($error): ?>
            <div class="alert alert-error" role="alert"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success" role="alert"><?= e($success) ?></div>
        <?php endif; ?>