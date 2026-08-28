<?php

use App\Core\Session;

$token = Session::csrfToken(); ?>
<section class="page-heading">
    <div>
        <p class="eyebrow">Segurança</p>
        <h1>Backups</h1>
        <p>Crie e descarregue cópias locais da base de dados.</p>
    </div>

    <form method="post" action="<?= BASE_URL ?>/backups/criar">
        <input type="hidden" name="csrf_token" value="<?= e($token) ?>">
        <button class="button button-primary">Criar backup agora</button>
    </form>
</section>

<section class="panel">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Ficheiro</th>
                    <th>Data</th>
                    <th>Tamanho</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$files): ?>
                    <tr>
                        <td colspan="4" class="empty-state">Ainda não existem backups.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($files as $file): ?>
                    <tr>
                        <td><strong><?= e($file['nome']) ?></strong></td>
                        <td><?= e(date('d/m/Y H:i:s', $file['data'])) ?></td>
                        <td><?= number_format($file['tamanho'] / 1024, 2, ',', '.') ?> KB</td>
                        <td>
                            <div class="table-actions">
                                <a class="button button-small button-outline" href="<?= BASE_URL ?>/backups/download?ficheiro=<?= urlencode($file['nome']) ?>">Descarregar</a>
                                <form method="post" action="<?= BASE_URL ?>/backups/eliminar" data-confirm="Eliminar definitivamente este backup?">
                                    <input type="hidden" name="csrf_token" value="<?= e($token) ?>"><input type="hidden" name="ficheiro" value="<?= e($file['nome']) ?>">
                                    <button class="button button-small button-danger">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>