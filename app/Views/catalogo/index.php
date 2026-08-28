<?php

use App\Core\Session;

$token = Session::csrfToken(); ?>
<section class="page-heading">
    <div>
        <p class="eyebrow">Configuração</p>
        <h1>Catálogo e preços</h1>
        <p>Configure peças, serviços e valores utilizados no cálculo automático.</p>
    </div>
</section>

<div class="catalog-grid">
    <section class="panel form-panel">
        <h2><?= $pieceEdit ? 'Editar peça' : 'Nova peça' ?></h2>
        <form method="post" action="<?= BASE_URL ?>/catalogo/peca" class="form-stack">
            <input type="hidden" name="csrf_token" value="<?= e($token) ?>"><?php if ($pieceEdit): ?><input type="hidden" name="id" value="<?= (int)$pieceEdit['id'] ?>"><?php endif; ?>
            <label>Nome<input name="nome" maxlength="80" value="<?= e($pieceEdit['nome'] ?? '') ?>" required></label>
            <label>Descrição<input name="descricao" maxlength="180" value="<?= e($pieceEdit['descricao'] ?? '') ?>"></label>
            <label>Estado<select name="estado">
                    <option value="ativo" <?= ($pieceEdit['estado'] ?? 'ativo') === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                    <option value="inativo" <?= ($pieceEdit['estado'] ?? '') === 'inativo' ? 'selected' : '' ?>>Inativo</option>
                </select></label>
            <div class="form-actions compact"><a class="button button-outline" href="<?= BASE_URL ?>/catalogo">Limpar</a><button class="button button-primary">Guardar peça</button></div>
        </form>
    </section>

    <section class="panel form-panel">
        <h2><?= $typeEdit ? 'Editar tipo de serviço' : 'Novo tipo de serviço' ?></h2>
        <form method="post" action="<?= BASE_URL ?>/catalogo/tipo-servico" class="form-stack">
            <input type="hidden" name="csrf_token" value="<?= e($token) ?>"><?php if ($typeEdit): ?><input type="hidden" name="id" value="<?= (int)$typeEdit['id'] ?>"><?php endif; ?>
            <label>Nome<input name="nome" maxlength="80" value="<?= e($typeEdit['nome'] ?? '') ?>" required></label>
            <label>Descrição<input name="descricao" maxlength="180" value="<?= e($typeEdit['descricao'] ?? '') ?>"></label>
            <div class="form-grid"><label>Prazo em horas<input type="number" name="prazo_horas" min="1" max="720" value="<?= (int)($typeEdit['prazo_horas'] ?? 48) ?>" required></label><label>Estado<select name="estado">
                        <option value="ativo" <?= ($typeEdit['estado'] ?? 'ativo') === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                        <option value="inativo" <?= ($typeEdit['estado'] ?? '') === 'inativo' ? 'selected' : '' ?>>Inativo</option>
                    </select></label></div>
            <div class="form-actions compact"><a class="button button-outline" href="<?= BASE_URL ?>/catalogo">Limpar</a><button class="button button-primary">Guardar serviço</button></div>
        </form>
    </section>
</div>

<section class="panel form-panel catalog-price-form">
    <h2><?= $priceEdit ? 'Editar preço' : 'Adicionar preço' ?></h2>
    <form method="post" action="<?= BASE_URL ?>/catalogo/preco" class="filter-form catalog-filter">
        <input type="hidden" name="csrf_token" value="<?= e($token) ?>"><?php if ($priceEdit): ?><input type="hidden" name="id" value="<?= (int)$priceEdit['id'] ?>"><?php endif; ?>
        <label>Peça<select name="tipo_peca_id" required>
                <option value="">Selecione</option><?php foreach ($activePieces as $piece): ?><option value="<?= (int)$piece['id'] ?>" <?= (int)($priceEdit['tipo_peca_id'] ?? 0) === (int)$piece['id'] ? 'selected' : '' ?>><?= e($piece['nome']) ?></option><?php endforeach; ?>
            </select></label>
        <label>Tipo de serviço<select name="tipo_servico_id" required>
                <option value="">Selecione</option><?php foreach ($activeServiceTypes as $type): ?><option value="<?= (int)$type['id'] ?>" <?= (int)($priceEdit['tipo_servico_id'] ?? 0) === (int)$type['id'] ? 'selected' : '' ?>><?= e($type['nome']) ?></option><?php endforeach; ?>
            </select></label>
        <label>Preço (Kz)<input type="number" name="valor" min="0.01" step="0.01" value="<?= e($priceEdit['valor'] ?? '') ?>" required></label>
        <label>Estado<select name="estado">
                <option value="ativo" <?= ($priceEdit['estado'] ?? 'ativo') === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                <option value="inativo" <?= ($priceEdit['estado'] ?? '') === 'inativo' ? 'selected' : '' ?>>Inativo</option>
            </select></label>
        <div class="filter-actions"><button class="button button-primary">Guardar preço</button><a class="button button-outline" href="<?= BASE_URL ?>/catalogo">Limpar</a></div>
    </form>
</section>

<section class="panel" id="tabela-precos">

    <div class="panel-heading">
        <h2>Tabela de preços por peça</h2>

        <p>
            <?= (int) $totalPricePieces ?>
            peça(s) com preços configurados.
            São apresentadas três peças por página.
        </p>
    </div>

    <div class="table-wrapper">

        <table>

            <thead>
                <tr>
                    <th>Tipo de serviço</th>
                    <th>Valor</th>
                    <th>Estado</th>
                    <th>Ações</th>
                </tr>
            </thead>

            <tbody>

            <?php if (!$priceGroups): ?>

                <tr>
                    <td
                        colspan="4"
                        class="empty-state"
                    >
                        Ainda não existem preços configurados.
                    </td>
                </tr>

            <?php endif; ?>

            <?php foreach ($priceGroups as $group): ?>

                <!-- Cabeçalho da peça -->
                <tr class="piece-price-header">
                    <td colspan="4">

                        <strong>
                            <?= e($group['nome']) ?>
                        </strong>

                        <span>
                            <?= count($group['precos']) ?>
                            preço(s)
                        </span>

                    </td>
                </tr>

                <!-- Preços da peça -->
                <?php foreach (
                    $group['precos'] as $price
                ): ?>

                    <tr>

                        <td>
                            <?= e(
                                $price['tipo_servico']
                            ) ?>
                        </td>

                        <td>
                            <strong>
                                <?= number_format(
                                    (float) $price['valor'],
                                    2,
                                    ',',
                                    '.'
                                ) ?>
                                Kz
                            </strong>
                        </td>

                        <td>
                            <span
                                class="status status-<?=
                                    e($price['estado'])
                                ?>"
                            >
                                <?= e(
                                    ucfirst(
                                        $price['estado']
                                    )
                                ) ?>
                            </span>
                        </td>

                        <td>

                            <div class="table-actions">

                                <a
                                    class="button button-small button-outline"
                                    href="<?= BASE_URL ?>/catalogo?editar_preco=<?=
                                        (int) $price['id']
                                    ?>&pagina_precos=<?=
                                        (int) $pricePage
                                    ?>#tabela-precos"
                                >
                                    Editar
                                </a>

                                <?php if (
                                    $price['estado'] === 'ativo'
                                ): ?>

                                    <form
                                        method="post"
                                        action="<?= BASE_URL ?>/catalogo/desativar"
                                        data-confirm="Desativar este preço?"
                                    >

                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= e($token) ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="entidade"
                                            value="preco"
                                        >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?=
                                                (int) $price['id']
                                            ?>"
                                        >

                                        <button
                                            class="button button-small button-danger"
                                            type="submit"
                                        >
                                            Desativar
                                        </button>

                                    </form>

                                <?php endif; ?>

                            </div>

                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php endforeach; ?>

            </tbody>

        </table>

    </div>

    <?php if ($totalPricePages > 1): ?>

        <nav
            class="pagination"
            aria-label="Paginação da tabela de preços"
        >

            <a
                class="pagination-link <?=
                    $pricePage <= 1
                        ? 'disabled'
                        : ''
                ?>"
                href="<?=
                    $pricePage > 1
                        ? BASE_URL .
                          '/catalogo?pagina_precos=' .
                          ($pricePage - 1) .
                          '#tabela-precos'
                        : '#'
                ?>"
            >
                Anterior
            </a>

            <span>
                Página <?= (int) $pricePage ?>
                de <?= (int) $totalPricePages ?>
            </span>

            <a
                class="pagination-link <?=
                    $pricePage >= $totalPricePages
                        ? 'disabled'
                        : ''
                ?>"
                href="<?=
                    $pricePage < $totalPricePages
                        ? BASE_URL .
                          '/catalogo?pagina_precos=' .
                          ($pricePage + 1) .
                          '#tabela-precos'
                        : '#'
                ?>"
            >
                Seguinte
            </a>

        </nav>

    <?php endif; ?>

</section>

<br>

<div class="catalog-grid catalog-lists">
    <section class="panel">
        <div class="panel-heading">
            <h2>Peças</h2>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Estado</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody><?php foreach ($pieces as $piece): ?><tr>
                            <td><?= e($piece['nome']) ?></td>
                            <td><span class="status status-<?= e($piece['estado']) ?>"><?= e(ucfirst($piece['estado'])) ?></span></td>
                            <td>
                                <div class="table-actions"><a class="button button-small button-outline" href="<?= BASE_URL ?>/catalogo?editar_peca=<?= (int)$piece['id'] ?>">Editar</a><?php if ($piece['estado'] === 'ativo'): ?><form method="post" action="<?= BASE_URL ?>/catalogo/desativar" data-confirm="Desativar esta peça?"><input type="hidden" name="csrf_token" value="<?= e($token) ?>"><input type="hidden" name="entidade" value="peca"><input type="hidden" name="id" value="<?= (int)$piece['id'] ?>"><button class="button button-small button-danger">Desativar</button></form><?php endif; ?></div>
                            </td>
                        </tr><?php endforeach; ?></tbody>
            </table>
        </div>
    </section>
    <section class="panel">
        <div class="panel-heading">
            <h2>Tipos de serviço</h2>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Prazo</th>
                        <th>Estado</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody><?php foreach ($serviceTypes as $type): ?><tr>
                            <td><?= e($type['nome']) ?></td>
                            <td><?= (int)$type['prazo_horas'] ?> h</td>
                            <td><span class="status status-<?= e($type['estado']) ?>"><?= e(ucfirst($type['estado'])) ?></span></td>
                            <td>
                                <div class="table-actions"><a class="button button-small button-outline" href="<?= BASE_URL ?>/catalogo?editar_tipo=<?= (int)$type['id'] ?>">Editar</a><?php if ($type['estado'] === 'ativo'): ?><form method="post" action="<?= BASE_URL ?>/catalogo/desativar" data-confirm="Desativar este serviço?"><input type="hidden" name="csrf_token" value="<?= e($token) ?>"><input type="hidden" name="entidade" value="tipo_servico"><input type="hidden" name="id" value="<?= (int)$type['id'] ?>"><button class="button button-small button-danger">Desativar</button></form><?php endif; ?></div>
                            </td>
                        </tr><?php endforeach; ?></tbody>
            </table>
        </div>
    </section>
</div>