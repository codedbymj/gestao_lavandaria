<?php

use App\Core\Session;

function clientListUrl(array $changes = []): string
{
    global $search, $status, $order, $page;

    $query = array_merge([
        'pesquisa' => $search,
        'estado' => $status,
        'ordem' => $order,
        'pagina' => $page,
    ], $changes);

    $query = array_filter($query, static fn($value) => $value !== '' && $value !== null);
    return BASE_URL . '/clientes?' . http_build_query($query);
}
?>
<section class="page-heading">
    <div>
        <p class="eyebrow">Gestão</p>
        <h1>Clientes</h1>
        <p><?= number_format($total, 0, ',', '.') ?> cliente(s) encontrado(s).</p>
    </div>
    <a class="button button-primary" href="<?= BASE_URL ?>/clientes/novo">
        + Novo cliente
    </a>
</section>

<section class="panel filter-panel">
    <form action="<?= BASE_URL ?>/clientes" method="get" class="filter-form">
        <label class="filter-search">
            Pesquisar
            <input type="search" name="pesquisa" value="<?= e($search) ?>"
                placeholder="Nome, telefone, email ou documento">
        </label>

        <label>
            Estado
            <select name="estado">
                <option value="">Todos</option>
                <option value="ativo" <?= $status === 'ativo' ? 'selected' : '' ?>>Ativos</option>
                <option value="inativo" <?= $status === 'inativo' ? 'selected' : '' ?>>Inativos</option>
            </select>
        </label>

        <label>
            Ordenação
            <select name="ordem">
                <option value="recentes" <?= $order === 'recentes' ? 'selected' : '' ?>>Mais recentes</option>
                <option value="antigos" <?= $order === 'antigos' ? 'selected' : '' ?>>Mais antigos</option>
                <option value="nome_asc" <?= $order === 'nome_asc' ? 'selected' : '' ?>>Nome A–Z</option>
                <option value="nome_desc" <?= $order === 'nome_desc' ? 'selected' : '' ?>>Nome Z–A</option>
            </select>
        </label>

        <div class="filter-actions">
            <button class="button button-primary" type="submit">Aplicar</button>
            <a class="button button-outline" href="<?= BASE_URL ?>/clientes">Limpar</a>
        </div>
    </form>
</section>

<section class="panel">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Telefone</th>
                    <th>Documento</th>
                    <th>Estado</th>
                    <th>Cadastro</th>
                    <th class="actions-column">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$clients): ?>
                    <tr>
                        <td colspan="6" class="empty-state">
                            Nenhum cliente corresponde aos critérios selecionados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($clients as $client): ?>
                        <tr>
                            <td>
                                <strong><?= e($client['nome']) ?></strong>
                                <small class="table-secondary"><?= e($client['email'] ?: 'Sem email') ?></small>
                            </td>
                            <td><?= e($client['telefone']) ?></td>
                            <td><?= e($client['documento'] ?: '—') ?></td>
                            <td>
                                <span class="status status-<?= e($client['estado']) ?>">
                                    <?= $client['estado'] === 'ativo' ? 'Ativo' : 'Inativo' ?>
                                </span>
                            </td>
                            <td><?= e(date('d/m/Y', strtotime($client['criado_em']))) ?></td>
                            <td>
                                <div class="table-actions">
                                    <a class="button button-small button-outline"
                                        href="<?= BASE_URL ?>/clientes/editar?id=<?= (int) $client['id'] ?>">
                                        Editar
                                    </a>

                                    <?php if ($client['estado'] === 'ativo'): ?>
                                        <form
                                                method="post"
                                                action="<?= BASE_URL ?>/clientes/eliminar"
                                                data-confirm="Deseja realmente desativar este cliente?"
                                            >
                                            <input type="hidden" name="csrf_token"
                                                value="<?= e(Session::csrfToken()) ?>">
                                            <input type="hidden" name="id" value="<?= (int) $client['id'] ?>">
                                            <button class="button button-small button-danger" type="submit">
                                                Desativar
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
        <nav class="pagination" aria-label="Paginação de clientes">
            <a class="pagination-link <?= $page <= 1 ? 'disabled' : '' ?>"
                href="<?= $page > 1 ? e(clientListUrl(['pagina' => $page - 1])) : '#' ?>">
                Anterior
            </a>

            <span>Página <?= $page ?> de <?= $totalPages ?></span>

            <a class="pagination-link <?= $page >= $totalPages ? 'disabled' : '' ?>"
                href="<?= $page < $totalPages ? e(clientListUrl(['pagina' => $page + 1])) : '#' ?>">
                Seguinte
            </a>
        </nav>
    <?php endif; ?>
</section>