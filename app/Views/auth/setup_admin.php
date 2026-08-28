<?php

use App\Core\Session;
?>
<section class="auth-card auth-card-wide">
    <div class="auth-brand">
        <span class="brand-mark brand-mark-large">L</span>
        <div>
            <h1>Configuração inicial</h1>
            <p>Crie a primeira conta de administrador.</p>
        </div>
    </div>

    <form action="<?= BASE_URL ?>/configurar-administrador" method="post" class="form-stack">
        <input type="hidden" name="csrf_token" value="<?= e(Session::csrfToken()) ?>">

        <label>
            Nome completo
            <input type="text" name="nome" minlength="3" maxlength="120"
                autocomplete="name" required autofocus>
        </label>

        <label>
            Email
            <input type="email" name="email" maxlength="150"
                autocomplete="email" required>
        </label>

        <div class="form-grid">
            <label>
                Palavra-passe
                <input type="password" name="senha" minlength="8"
                    autocomplete="new-password" required>
            </label>

            <label>
                Confirmar palavra-passe
                <input type="password" name="confirmar_senha" minlength="8"
                    autocomplete="new-password" required>
            </label>
        </div>

        <p class="form-hint">
            Utilize pelo menos 8 caracteres, com maiúscula, minúscula e número.
        </p>

        <button class="button button-primary button-block" type="submit">
            Criar administrador
        </button>
    </form>
</section>