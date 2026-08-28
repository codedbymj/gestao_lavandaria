<?php

use App\Core\Session; ?>
<section class="page-heading">
    <div>
        <p class="eyebrow">Administração</p>
        <h1>Utilizadores</h1>
        <p><?= (int) $total ?> registo(s) encontrado(s).</p>
    </div>
    <a class="button button-primary" href="<?= BASE_URL ?>/utilizadores/novo">+ Novo utilizador</a>
</section>
<section class="panel filter-panel">
    <form method="get" action="<?= BASE_URL ?>/utilizadores" class="filter-form">
        <label class="filter-search">Pesquisar<input type="search" name="pesquisa" value="<?= e($search) ?>" placeholder="Nome, email ou telefone"></label>
        <label>Perfil<select name="perfil_id">
                <option value="">Todos</option><?php foreach ($profiles as $profile): ?><option value="<?= (int) $profile['id'] ?>" <?= $profileId === (int) $profile['id'] ? 'selected' : '' ?>><?= e($profile['nome']) ?></option><?php endforeach; ?>
            </select></label>
        <label>Estado<select name="estado">
                <option value="">Todos</option>
                <option value="ativo" <?= $status === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                <option value="inativo" <?= $status === 'inativo' ? 'selected' : '' ?>>Inativo</option>
                <option value="bloqueado" <?= $status === 'bloqueado' ? 'selected' : '' ?>>Bloqueado</option>
            </select></label>
        <div class="filter-actions"><button class="button button-primary">Aplicar</button><a class="button button-outline" href="<?= BASE_URL ?>/utilizadores">Limpar</a></div>
    </form>
</section>
<section class="panel">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Utilizador</th>
                    <th>Perfil</th>
                    <th>Telefone</th>
                    <th>Estado</th>
                    <th>Último login</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$users): ?><tr>
                        <td colspan="6" class="empty-state">Nenhum utilizador encontrado.</td>
                    </tr><?php endif; ?>
                <?php foreach ($users as $item): ?><tr>
                        <td><strong><?= e($item['nome']) ?></strong><small class="table-secondary"><?= e($item['email']) ?></small></td>
                        <td><?= e($item['perfil']) ?></td>
                        <td><?= e($item['telefone'] ?: '—') ?></td>
                        <td><span class="status status-<?= e($item['estado']) ?>"><?= e(ucfirst($item['estado'])) ?></span></td>
                        <td><?= $item['ultimo_login'] ? e(date('d/m/Y H:i', strtotime($item['ultimo_login']))) : 'Nunca' ?></td>
                        <td>
                            <div class="table-actions"><a class="button button-small button-outline" href="<?= BASE_URL ?>/utilizadores/editar?id=<?= (int) $item['id'] ?>">Editar</a>
                                <?php if ($item['estado'] === 'ativo' && (int) $item['id'] !== (int) Session::user()['id']): ?><form method="post" action="<?= BASE_URL ?>/utilizadores/eliminar" data-confirm="Deseja realmente desativar este utilizador?"><input type="hidden" name="csrf_token" value="<?= e(Session::csrfToken()) ?>"><input type="hidden" name="id" value="<?= (int) $item['id'] ?>"><button class="button button-small button-danger">Desativar</button></form><?php endif; ?></div>
                        </td>
                    </tr><?php endforeach; ?></tbody>
        </table>
    </div>
    <?php if ($totalPages > 1): ?><nav class="pagination"><span>Página <?= $page ?> de <?= $totalPages ?></span>
            <div class="table-actions"><?php if ($page > 1): ?><a class="pagination-link" href="?<?= http_build_query(['pesquisa' => $search, 'estado' => $status, 'perfil_id' => $profileId, 'pagina' => $page - 1]) ?>">Anterior</a><?php endif; ?><?php if ($page < $totalPages): ?><a class="pagination-link" href="?<?= http_build_query(['pesquisa' => $search, 'estado' => $status, 'perfil_id' => $profileId, 'pagina' => $page + 1]) ?>">Seguinte</a><?php endif; ?></div>
        </nav><?php endif; ?>
</section>