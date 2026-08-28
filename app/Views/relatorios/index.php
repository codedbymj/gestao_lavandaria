<?php $labels = ['recebido' => 'Recebido', 'em_lavagem' => 'Em lavagem', 'em_secagem' => 'Em secagem', 'em_engomagem' => 'Em engomagem', 'pronto' => 'Pronto', 'entregue' => 'Entregue', 'cancelado' => 'Cancelado'];
$query = http_build_query(['inicio' => $from, 'fim' => $to, 'estado' => $status]); ?>
<section class="page-heading">
    <div>
        <p class="eyebrow">Análise</p>
        <h1>Relatórios</h1>
        <p>Resultados financeiros e operacionais por período.</p>
    </div><a class="button button-primary" href="<?= BASE_URL ?>/relatorios/pdf?<?= e($query) ?>">Exportar PDF</a>
</section>
<section class="panel filter-panel">
    <form method="get" action="<?= BASE_URL ?>/relatorios" class="filter-form report-filters"><label>Data inicial<input type="date" name="inicio" value="<?= e($from) ?>"></label><label>Data final<input type="date" name="fim" value="<?= e($to) ?>"></label><label>Estado<select name="estado">
                <option value="">Todos</option><?php foreach ($labels as $key => $label): ?><option value="<?= e($key) ?>" <?= $status === $key ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
            </select></label>
        <div class="filter-actions"><button class="button button-primary">Aplicar</button><a class="button button-outline" href="<?= BASE_URL ?>/relatorios">Limpar</a></div>
    </form>
</section>
<section class="detail-grid report-summary">
    <article class="summary-card"><span>Serviços</span><strong><?= (int)$summary['quantidade'] ?></strong></article>
    <article class="summary-card"><span>Total faturado</span><strong><?= number_format((float)$summary['faturado'], 2, ',', '.') ?> Kz</strong></article>
    <article class="summary-card"><span>Total recebido</span><strong><?= number_format((float)$summary['recebido'], 2, ',', '.') ?> Kz</strong></article>
    <article class="summary-card"><span>Por receber</span><strong><?= number_format(max(0, (float)$summary['faturado'] - (float)$summary['recebido']), 2, ',', '.') ?> Kz</strong></article>
</section>
<section class="panel">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Cliente</th>
                    <th>Entrada</th>
                    <th>Estado</th>
                    <th>Faturado</th>
                    <th>Recebido</th>
                </tr>
            </thead>
            <tbody><?php if (!$rows): ?><tr>
                        <td colspan="6" class="empty-state">Não existem dados no período selecionado.</td>
                    </tr><?php endif; ?><?php foreach ($rows as $row): ?><tr>
                        <td><strong><?= e($row['codigo']) ?></strong></td>
                        <td><?= e($row['cliente']) ?></td>
                        <td><?= e(date('d/m/Y H:i', strtotime($row['data_entrada']))) ?></td>
                        <td><span class="status status-<?= e($row['estado']) ?>"><?= e($labels[$row['estado']] ?? $row['estado']) ?></span></td>
                        <td><?= number_format((float)$row['total'], 2, ',', '.') ?> Kz</td>
                        <td><?= number_format((float)$row['pago'], 2, ',', '.') ?> Kz</td>
                    </tr><?php endforeach; ?></tbody>
        </table>
    </div>
</section>