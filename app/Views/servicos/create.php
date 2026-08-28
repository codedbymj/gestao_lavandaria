<?php

use App\Core\Session; ?>
<section class="page-heading">
    <div>
        <p class="eyebrow">Operações</p>
        <h1>Novo serviço</h1>
        <p>Selecione o cliente e adicione todas as peças recebidas.</p>
    </div>
</section>
<?php if (!$prices): ?><div class="alert alert-error">Configure pelo menos um preço ativo no catálogo antes de registar serviços.</div><?php endif; ?>
<form method="post" action="<?= BASE_URL ?>/servicos/novo" class="form-stack" id="service-form">
    <input type="hidden" name="csrf_token" value="<?= e(Session::csrfToken()) ?>">
    <section class="panel form-panel">
        <h2>Dados do serviço</h2>
        <div class="form-grid">
            <label>Cliente <span class="required">*</span><select name="cliente_id" required>
                    <option value="">Selecione</option><?php foreach ($clients as $client): ?><option value="<?= (int)$client['id'] ?>"><?= e($client['nome'] . ' — ' . $client['telefone']) ?></option><?php endforeach; ?>
                </select></label>
            <label>
                Desconto (%)

                <input
                    type="number"
                    name="desconto_percentual"
                    id="discount"
                    min="0"
                    max="100"
                    step="0.01"
                    value="0"
                >
            </label>
            <label class="form-span">Observações gerais<input name="observacoes" maxlength="1000" placeholder="Cuidados especiais, manchas ou instruções"></label>
        </div>
    </section>
    <section class="panel">
        <div class="panel-heading panel-heading-actions">
            <div>
                <h2>Peças recebidas</h2>
                <p>O preço é obtido automaticamente do catálogo.</p>
            </div><button class="button button-primary" type="button" id="add-item" <?= $prices ? '' : 'disabled' ?>>+ Adicionar peça</button>
        </div>
        <div class="table-wrapper">
            <table id="items-table">
                <thead>
                    <tr>
                        <th>Peça e serviço</th>
                        <th>Qtd.</th>
                        <th>Cor</th>
                        <th>Observação</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="items-body"></tbody>
            </table>
        </div>
        <div class="service-totals">

            <div>
                <span>Subtotal</span>

                <strong id="subtotal-display">
                    0,00 Kz
                </strong>
            </div>

            <div>
                <span>Desconto</span>

                <strong id="discount-display">
                    0% (- 0,00 Kz)
                </strong>
            </div>

            <div class="grand-total">
                <span>Total</span>

                <strong id="total-display">
                    0,00 Kz
                </strong>
            </div>

        </div>
    </section>
    <div class="form-actions no-border"><a class="button button-outline" href="<?= BASE_URL ?>/servicos">Cancelar</a><button class="button button-primary" <?= $prices ? '' : 'disabled' ?>>Registar serviço</button></div>
</form>
<template id="item-template">
    <tr class="service-item">
        <td><select name="preco_id[]" class="price-select" required>
                <option value="">Selecione</option><?php foreach ($prices as $price): ?><option value="<?= (int)$price['id'] ?>" data-price="<?= e($price['valor']) ?>"><?= e($price['peca'] . ' — ' . $price['tipo_servico'] . ' (' . number_format((float)$price['valor'], 2, ',', '.') . ' Kz)') ?></option><?php endforeach; ?>
            </select></td>
        <td><input type="number" name="quantidade[]" class="quantity-input compact-input" min="1" max="999" value="1" required></td>
        <td><input name="cor[]" maxlength="50" class="compact-input"></td>
        <td><input name="item_observacoes[]" maxlength="255" class="compact-input"></td>
        <td><strong class="line-total">0,00 Kz</strong></td>
        <td><button type="button" class="button button-small button-danger remove-item">Remover</button></td>
    </tr>
</template>
<script src="<?= BASE_URL ?>/assets/js/servicos.js"></script>