<?php

use App\Core\Session;

$token = Session::csrfToken();
$onlyIroning = $items !== [];

foreach ($items as $serviceItem) {
    if (
        mb_strtolower(trim($serviceItem['tipo_servico']))
        !== 'engomagem'
    ) {
        $onlyIroning = false;
        break;
    }
}

$labels = $onlyIroning
    ? [
        'recebido' => 'Recebido',
        'em_engomagem' => 'Em engomagem',
        'pronto' => 'Pronto',
        'entregue' => 'Entregue',
        'cancelado' => 'Cancelado',
    ]
    : [
        'recebido' => 'Recebido',
        'em_lavagem' => 'Em lavagem',
        'em_secagem' => 'Em secagem',
        'em_engomagem' => 'Em engomagem',
        'pronto' => 'Pronto',
        'entregue' => 'Entregue',
        'cancelado' => 'Cancelado',
    ];
    
$balance = max(0, (float)$service['total'] - (float)$service['pago']);
$discountAmount = round(
    (float) $service['subtotal']
    * (float) $service['desconto_percentual']
    / 100,
    2
);
$methodLabels = ['dinheiro' => 'Dinheiro', 'transferencia' => 'Transferência', 'tpa' => 'TPA', 'multicaixa_express' => 'Multicaixa Express'];
?>
<section class="page-heading">
    <div>
        <p class="eyebrow">Serviço</p>
        <h1><?= e($service['codigo']) ?></h1>
        <p><?= e($service['cliente']) ?> · <?= e($service['cliente_telefone']) ?></p>
    </div>
    <div class="table-actions"><span class="status status-large status-<?= e($service['estado']) ?>"><?= e($labels[$service['estado']] ?? $service['estado']) ?></span><a class="button button-outline" href="<?= BASE_URL ?>/servicos">Voltar</a></div>
</section>

<section class="detail-grid summary-grid">
    <article class="summary-card"><span>Entrada</span><strong><?= e(date('d/m/Y H:i', strtotime($service['data_entrada']))) ?></strong></article>
    <article class="summary-card"><span>Previsão</span><strong><?= e(date('d/m/Y H:i', strtotime($service['data_prevista']))) ?></strong></article>
    <article class="summary-card"><span>Total</span><strong><?= number_format((float)$service['total'], 2, ',', '.') ?> Kz</strong></article>
    <article class="summary-card"><span>Pago</span><strong><?= number_format((float)$service['pago'], 2, ',', '.') ?> Kz</strong></article>
    <article class="summary-card <?= $balance > 0 ? 'balance-due' : 'balance-paid' ?>"><span>Saldo</span><strong><?= number_format($balance, 2, ',', '.') ?> Kz</strong></article>
</section>

<div class="content-columns">
    <div class="content-main">
        <section class="panel">
            <div class="panel-heading">
                <h2>Peças do serviço</h2>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Peça</th>
                            <th>Serviço</th>
                            <th>Qtd.</th>
                            <th>Cor</th>
                            <th>Preço unit.</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?><tr>
                                <td><?= e($item['peca']) ?><?php if ($item['observacoes']): ?><small class="table-secondary"><?= e($item['observacoes']) ?></small><?php endif; ?></td>
                                <td><?= e($item['tipo_servico']) ?></td>
                                <td><?= (int)$item['quantidade'] ?></td>
                                <td><?= e($item['cor'] ?: '—') ?></td>
                                <td><?= number_format((float)$item['preco_unitario'], 2, ',', '.') ?> Kz</td>
                                <td><strong><?= number_format((float)$item['subtotal'], 2, ',', '.') ?> Kz</strong></td>
                            </tr><?php endforeach; ?>
                    </tbody>
                    <tfoot>

                        <tr>
                            <td colspan="5">
                                Subtotal
                            </td>

                            <td>
                                <?= number_format(
                                    (float) $service['subtotal'],
                                    2,
                                    ',',
                                    '.'
                                ) ?>
                                Kz
                            </td>
                        </tr>

                        <tr>
                            <td colspan="5">
                                Desconto
                                (
                                <?= number_format(
                                    (float) $service[
                                        'desconto_percentual'
                                    ],
                                    2,
                                    ',',
                                    '.'
                                ) ?>
                                %)
                            </td>

                            <td>
                                -
                                <?= number_format(
                                    $discountAmount,
                                    2,
                                    ',',
                                    '.'
                                ) ?>
                                Kz
                            </td>
                        </tr>

                        <tr>
                            <td colspan="5">
                                <strong>Total</strong>
                            </td>

                            <td>
                                <strong>
                                    <?= number_format(
                                        (float) $service['total'],
                                        2,
                                        ',',
                                        '.'
                                    ) ?>
                                    Kz
                                </strong>
                            </td>
                        </tr>

                    </tfoot>
                </table>
            </div>
        </section>

        <section class="panel">
            <div class="panel-heading">
                <h2>Pagamentos</h2>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Valor</th>
                            <th>Método</th>
                            <th>Referência</th>
                            <th>Estado</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$payments): ?><tr>
                                <td colspan="6" class="empty-state">Nenhum pagamento registado.</td>
                            </tr><?php endif; ?>
                        <?php foreach ($payments as $payment): ?><tr>
                                <td><?= e(date('d/m/Y H:i', strtotime($payment['pago_em']))) ?></td>
                                <td><strong><?= number_format((float)$payment['valor'], 2, ',', '.') ?> Kz</strong></td>
                                <td><?= e($methodLabels[$payment['metodo']] ?? $payment['metodo']) ?></td>
                                <td><?= e($payment['referencia'] ?: '—') ?></td>
                                <td><span class="status status-<?= $payment['estado'] === 'confirmado' ? 'ativo' : 'inativo' ?>"><?= e(ucfirst($payment['estado'])) ?></span></td>
                                <td>
                                    <div class="table-actions">

                                        <?php if ($payment['estado'] === 'confirmado'): ?>
                                            <a
                                                class="button button-small button-outline"
                                                href="<?= BASE_URL ?>/pagamentos/recibo?id=<?= (int)$payment['id'] ?>"
                                            >
                                                Recibo
                                            </a>
                                        <?php endif; ?>

                                        <?php if (
                                            $payment['estado'] === 'confirmado'
                                            && $service['estado'] !== 'entregue'
                                        ): ?>
                                            <form
                                                method="post"
                                                action="<?= BASE_URL ?>/pagamentos/anular"
                                                data-confirm="Deseja realmente anular este pagamento?"
                                            >
                                                <input
                                                    type="hidden"
                                                    name="csrf_token"
                                                    value="<?= e($token) ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="servico_id"
                                                    value="<?= (int)$service['id'] ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?= (int)$payment['id'] ?>"
                                                >

                                                <button class="button button-small button-danger">
                                                    Anular
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                    </div>
                                </td>                            
                            </tr><?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel">
            <div class="panel-heading">
                <h2>Histórico de acompanhamento</h2>
            </div>
            <div class="timeline">
                <?php foreach ($history as $event): ?><article class="timeline-item"><span class="timeline-dot"></span>
                        <div><strong><?= e($labels[$event['novo_estado']] ?? $event['novo_estado']) ?></strong>
                            <p><?= e($event['observacao'] ?: 'Estado atualizado.') ?></p><small><?= e(date('d/m/Y H:i', strtotime($event['alterado_em']))) ?> · <?= e($event['utilizador']) ?></small>
                        </div>
                    </article><?php endforeach; ?>
            </div>
        </section>
    </div>

    <aside class="content-aside">
        <?php if ($allowedStatuses): ?><section class="panel form-panel side-panel">
                <h2>Atualizar estado</h2>
                <form method="post" action="<?= BASE_URL ?>/servicos/estado" class="form-stack"><input type="hidden" name="csrf_token" value="<?= e($token) ?>"><input type="hidden" name="id" value="<?= (int)$service['id'] ?>"><label>Novo estado<select name="estado" required>
                            <option value="">Selecione</option><?php foreach ($allowedStatuses as $state): ?><option value="<?= e($state) ?>"><?= e($labels[$state]) ?></option><?php endforeach; ?>
                        </select></label><label>Observação<input name="observacao" maxlength="255"></label><button class="button button-primary">Atualizar</button></form>
            </section><?php endif; ?>

        <?php if (!in_array($service['estado'], ['entregue', 'cancelado'], true)): ?><section class="panel form-panel side-panel">
                <h2>Editar serviço</h2>
                <form method="post" action="<?= BASE_URL ?>/servicos/editar" class="form-stack"><input type="hidden" name="csrf_token" value="<?= e($token) ?>"><input type="hidden" name="id" value="<?= (int)$service['id'] ?>"><label>Data prevista<input type="datetime-local" name="data_prevista" value="<?= e(date('Y-m-d\TH:i', strtotime($service['data_prevista']))) ?>" required></label>
                <label>
                    Desconto (%)

                    <input
                        type="number"
                        name="desconto_percentual"
                        min="0"
                        max="100"
                        step="0.01"
                        value="<?= e(
                            $service['desconto_percentual']
                        ) ?>"
                    >
                </label>
            <label>Observações<textarea name="observacoes" rows="3"><?= e($service['observacoes'] ?? '') ?></textarea></label><button class="button button-outline">Guardar alterações</button></form>
            </section><?php endif; ?>

        <?php if ($balance > 0 && !in_array($service['estado'], ['cancelado', 'entregue'], true)): ?><section class="panel form-panel side-panel">
                <h2>Registar pagamento</h2>
                <form method="post" action="<?= BASE_URL ?>/pagamentos" class="form-stack"><input type="hidden" name="csrf_token" value="<?= e($token) ?>"><input type="hidden" name="servico_id" value="<?= (int)$service['id'] ?>"><label>Valor<input type="number" name="valor" min="0.01" max="<?= e($balance) ?>" step="0.01" value="<?= e($balance) ?>" required></label><label>Método<select name="metodo" required>
                            <option value="dinheiro">Dinheiro</option>
                            <option value="transferencia">Transferência</option>
                            <option value="tpa">TPA</option>
                            <option value="multicaixa_express">Multicaixa Express</option>
                        </select></label><label>Referência<input name="referencia" maxlength="100"></label><button class="button button-primary">Confirmar pagamento</button></form>
            </section><?php endif; ?>

        <?php if ($service['estado'] === 'pronto' && $balance <= 0 && !$delivery): ?><section class="panel form-panel side-panel delivery-panel">
                <h2>Registar entrega</h2>
                <form method="post" action="<?= BASE_URL ?>/entregas" class="form-stack"><input type="hidden" name="csrf_token" value="<?= e($token) ?>"><input type="hidden" name="servico_id" value="<?= (int)$service['id'] ?>"><label>Recebido por<input name="recebido_por_nome" maxlength="120" value="<?= e($service['cliente']) ?>" required></label><label>Documento<input name="documento" maxlength="40"></label><label>Observação<input name="observacao" maxlength="255"></label><button class="button button-primary">Confirmar entrega</button></form>
            </section><?php endif; ?>

        <?php if ($delivery): ?><section class="panel form-panel side-panel delivery-summary">
                <h2>Entrega concluída</h2>
                <p><strong><?= e($delivery['recebido_por_nome']) ?></strong></p>
                <p><?= e(date('d/m/Y H:i', strtotime($delivery['entregue_em']))) ?></p><small>Registada por <?= e($delivery['utilizador']) ?></small>
            </section><?php endif; ?>
    </aside>
</div>