<?php

$stateLabels = [
    'recebido' => 'Recebido',
    'em_lavagem' => 'Em lavagem',
    'em_secagem' => 'Em secagem',
    'em_engomagem' => 'Em engomagem',
    'pronto' => 'Pronto',
    'entregue' => 'Entregue',
    'cancelado' => 'Cancelado',
];
?>
<section class="page-heading">
    <div>
        <p class="eyebrow">Visão geral</p>
        <h1>Dashboard</h1>
        <p>Acompanhe a atividade atual da lavandaria.</p>
    </div>
    <span class="date-chip"><?= e(date('d/m/Y')) ?></span>
</section>

<section class="stats-grid">
    <article class="stat-card">
        <span class="stat-icon blue">CL</span>
        <div>
            <p>Clientes ativos</p>
            <strong><?= number_format((float) $statistics['clientes'], 0, ',', '.') ?></strong>
        </div>
    </article>

    <article class="stat-card">
        <span class="stat-icon violet">SH</span>
        <div>
            <p>Serviços hoje</p>
            <strong><?= number_format((float) $statistics['servicos_hoje'], 0, ',', '.') ?></strong>
        </div>
    </article>

    <article class="stat-card">
        <span class="stat-icon amber">EP</span>
        <div>
            <p>Em processamento</p>
            <strong><?= number_format((float) $statistics['em_processamento'], 0, ',', '.') ?></strong>
        </div>
    </article>

    <article class="stat-card">
        <span class="stat-icon green">PR</span>
        <div>
            <p>Prontos para entrega</p>
            <strong><?= number_format((float) $statistics['prontos'], 0, ',', '.') ?></strong>
        </div>
    </article>

    <article class="stat-card revenue-card">
        <span class="stat-icon dark">Kz</span>
        <div>
            <p>Receita do mês</p>
            <strong><?= number_format((float) $statistics['receita_mes'], 2, ',', '.') ?> Kz</strong>
        </div>
    </article>
</section>

<?php
$maxStatus = max(1, ...array_map(static fn($row) => (int) $row['total'], $statusData));
$maxRevenue = max(1, ...array_map(static fn($row) => (float) $row['total'], $monthlyRevenue));
?>
<section class="charts-grid">
    <article class="panel chart-panel">
        <div class="panel-heading">
            <h2>Serviços por estado</h2>
            <p>Distribuição operacional atual.</p>
        </div>
        <div class="horizontal-chart">
            <?php if (!$statusData): ?><p class="empty-state">Sem dados.</p><?php endif; ?>
            <?php foreach ($statusData as $row): ?>
                <div class="chart-row">
                    <span><?= e($stateLabels[$row['estado']] ?? $row['estado']) ?></span>
                    <div class="chart-track"><i style="width: <?= round(((int)$row['total'] / $maxStatus) * 100, 1) ?>%"></i></div>
                    <strong><?= (int) $row['total'] ?></strong>
                </div>
            <?php endforeach; ?>
        </div>
    </article>
    <article class="panel chart-panel">
        <div class="panel-heading">
            <h2>Receita dos últimos 6 meses</h2>
            <p>Pagamentos confirmados.</p>
        </div>
        <div class="vertical-chart">
            <?php foreach ($monthlyRevenue as $row): ?>
                <div class="vertical-bar">
                    <strong><?= number_format((float)$row['total'] / 1000, 1, ',', '.') ?>k</strong>
                    <i style="height: <?= max(3, round(((float)$row['total'] / $maxRevenue) * 100, 1)) ?>%"></i>
                    <span><?= e(substr($row['mes'], 0, 2)) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </article>
</section>

<section class="panel">
    <div class="panel-heading">
        <div>
            <h2>Serviços recentes</h2>
            <p>Últimos cinco registos efetuados.</p>
        </div>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Cliente</th>
                    <th>Entrada</th>
                    <th>Previsão</th>
                    <th>Estado</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$recentServices): ?>
                    <tr>
                        <td colspan="6" class="empty-state">
                            Ainda não existem serviços registados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentServices as $service): ?>
                        <tr>
                            <td><strong><?= e($service['codigo']) ?></strong></td>
                            <td><?= e($service['cliente']) ?></td>
                            <td><?= e(date('d/m/Y H:i', strtotime($service['data_entrada']))) ?></td>
                            <td><?= e(date('d/m/Y H:i', strtotime($service['data_prevista']))) ?></td>
                            <td>
                                <span class="status status-<?= e($service['estado']) ?>">
                                    <?= e($stateLabels[$service['estado']] ?? $service['estado']) ?>
                                </span>
                            </td>
                            <td><?= number_format((float) $service['total'], 2, ',', '.') ?> Kz</td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>