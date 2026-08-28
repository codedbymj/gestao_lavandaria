<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro | <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>

<body>
    <main class="auth-shell">
        <section class="error-page">
            <strong>500</strong>
            <h1>Não foi possível executar a aplicação</h1>
            <p><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p>
            <a class="button button-primary" href="<?= BASE_URL ?>/">Tentar novamente</a>
        </section>
    </main>
</body>

</html>