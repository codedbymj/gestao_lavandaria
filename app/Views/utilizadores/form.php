<?php

use App\Core\Session; ?>
<section class="page-heading">
    <div>
        <p class="eyebrow">Administração</p>
        <h1><?= e($title) ?></h1>
        <p>Defina os dados de acesso e o perfil.</p>
    </div>
</section>
<form action="<?= e($formAction) ?>" method="post" class="panel form-panel">
    <input type="hidden" name="csrf_token" value="<?= e(Session::csrfToken()) ?>"><?php if (isset($userData['id'])): ?><input type="hidden" name="id" value="<?= (int) $userData['id'] ?>"><?php endif; ?>
    <?php if ($errors): ?><div class="alert alert-error form-alert">Corrija os campos assinalados.</div><?php endif; ?>
    <div class="form-grid">
        <label>Nome completo <span class="required">*</span><input name="nome" maxlength="120" value="<?= e($userData['nome']) ?>" required><?php if (isset($errors['nome'])): ?><small class="field-error"><?= e($errors['nome']) ?></small><?php endif; ?></label>
        <label>Email <span class="required">*</span><input type="email" name="email" maxlength="150" value="<?= e($userData['email']) ?>" required><?php if (isset($errors['email'])): ?><small class="field-error"><?= e($errors['email']) ?></small><?php endif; ?></label>
        <label>Telefone<input name="telefone" maxlength="25" value="<?= e($userData['telefone']) ?>"><?php if (isset($errors['telefone'])): ?><small class="field-error"><?= e($errors['telefone']) ?></small><?php endif; ?></label>
        <label>Perfil <span class="required">*</span><select name="perfil_id" required>
                <option value="">Selecione</option><?php foreach ($profiles as $profile): ?><option value="<?= (int)$profile['id'] ?>" <?= (int)$userData['perfil_id'] === (int)$profile['id'] ? 'selected' : '' ?>><?= e($profile['nome']) ?></option><?php endforeach; ?>
            </select><?php if (isset($errors['perfil_id'])): ?><small class="field-error"><?= e($errors['perfil_id']) ?></small><?php endif; ?></label>
        <label>Palavra-passe <?= isset($userData['id']) ? '(deixe vazia para manter)' : '<span class="required">*</span>' ?><input type="password" name="senha" minlength="8" <?= isset($userData['id']) ? '' : 'required' ?>><?php if (isset($errors['senha'])): ?><small class="field-error"><?= e($errors['senha']) ?></small><?php endif; ?></label>
        <label>Estado<select name="estado">
                <option value="ativo" <?= $userData['estado'] === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                <option value="inativo" <?= $userData['estado'] === 'inativo' ? 'selected' : '' ?>>Inativo</option>
                <option value="bloqueado" <?= $userData['estado'] === 'bloqueado' ? 'selected' : '' ?>>Bloqueado</option>
            </select><?php if (isset($errors['estado'])): ?><small class="field-error"><?= e($errors['estado']) ?></small><?php endif; ?></label>
    </div>
    <div class="form-actions"><a class="button button-outline" href="<?= BASE_URL ?>/utilizadores">Cancelar</a><button class="button button-primary"><?= e($submitLabel) ?></button></div>
</form>