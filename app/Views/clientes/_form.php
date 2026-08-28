<?php

use App\Core\Session;
?>
<form action="<?= e($formAction) ?>" method="post" class="panel form-panel">
    <input type="hidden" name="csrf_token" value="<?= e(Session::csrfToken()) ?>">

    <?php if (isset($client['id'])): ?>
        <input type="hidden" name="id" value="<?= (int) $client['id'] ?>">
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="alert alert-error form-alert">
            Corrija os campos assinalados antes de continuar.
        </div>
    <?php endif; ?>

    <div class="form-grid">
        <label>
            Nome completo <span class="required">*</span>
            <input type="text" name="nome" maxlength="120"
                value="<?= e($client['nome']) ?>" required autofocus>
            <?php if (isset($errors['nome'])): ?>
                <small class="field-error"><?= e($errors['nome']) ?></small>
            <?php endif; ?>
        </label>

        <label>
            Telefone <span class="required">*</span>
            <input type="tel" name="telefone" maxlength="25"
                value="<?= e($client['telefone']) ?>" placeholder="+244 923 000 000" required>
            <?php if (isset($errors['telefone'])): ?>
                <small class="field-error"><?= e($errors['telefone']) ?></small>
            <?php endif; ?>
        </label>

        <label>
            Email
            <input type="email" name="email" maxlength="150"
                value="<?= e($client['email']) ?>" placeholder="cliente@exemplo.com">
            <?php if (isset($errors['email'])): ?>
                <small class="field-error"><?= e($errors['email']) ?></small>
            <?php endif; ?>
        </label>

        <label>
            BI ou outro documento
            <input type="text" name="documento" maxlength="40"
                value="<?= e($client['documento']) ?>">
            <?php if (isset($errors['documento'])): ?>
                <small class="field-error"><?= e($errors['documento']) ?></small>
            <?php endif; ?>
        </label>

        <label class="form-span">
            Endereço
            <input type="text" name="endereco" maxlength="255"
                value="<?= e($client['endereco']) ?>">
            <?php if (isset($errors['endereco'])): ?>
                <small class="field-error"><?= e($errors['endereco']) ?></small>
            <?php endif; ?>
        </label>

        <label>
            Estado
            <select name="estado">
                <option value="ativo" <?= $client['estado'] === 'ativo' ? 'selected' : '' ?>>
                    Ativo
                </option>
                <option value="inativo" <?= $client['estado'] === 'inativo' ? 'selected' : '' ?>>
                    Inativo
                </option>
            </select>
            <?php if (isset($errors['estado'])): ?>
                <small class="field-error"><?= e($errors['estado']) ?></small>
            <?php endif; ?>
        </label>
    </div>

    <div class="form-actions">
        <a class="button button-outline" href="<?= BASE_URL ?>/clientes">Cancelar</a>
        <button class="button button-primary" type="submit"><?= e($submitLabel) ?></button>
    </div>
</form>