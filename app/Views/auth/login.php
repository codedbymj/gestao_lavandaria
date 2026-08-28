<?php

use App\Core\Session;
?>
<section class="auth-card">
    <div class="auth-brand">
        <span class="brand-mark brand-mark-large">L</span>
        <div>
            <h1><?= e(APP_NAME) ?></h1>
            <p>Gestão simples, organizada e segura.</p>
        </div>
    </div>

    <div class="section-heading">
        <h2>Iniciar sessão</h2>
        <p>Introduza as credenciais da sua conta.</p>
    </div>

    <form action="<?= BASE_URL ?>/login" method="post" class="form-stack">
        <input type="hidden" name="csrf_token" value="<?= e(Session::csrfToken()) ?>">

        <label>
            Email
            <input type="email" name="email" placeholder="nome@exemplo.com"
                autocomplete="email" required autofocus>
        </label>

        <label>
            Palavra-passe
            <input type="password" name="senha" placeholder="A sua palavra-passe"
                autocomplete="current-password" required>
        </label>

        <button class="button button-primary button-block" type="submit">
            Entrar
        </button>
    </form>
</section>