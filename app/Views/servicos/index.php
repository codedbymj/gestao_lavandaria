<?php
$labels = ['recebido' => 'Recebido', 'em_lavagem' => 'Em lavagem', 'em_secagem' => 'Em secagem', 'em_engomagem' => 'Em engomagem', 'pronto' => 'Pronto', 'entregue' => 'Entregue', 'cancelado' => 'Cancelado'];
?>
<section class="page-heading">
    <div>
        <p class="eyebrow">Operações</p>
        <h1>Serviços</h1>
        <p><?= (int)$total ?> serviço(s) encontrado(s).</p>
    </div><a class="button button-primary" href="<?= BASE_URL ?>/servicos/novo">+ Registar serviço</a>
</section>
<section class="panel filter-panel">
    <form method="get" action="<?= BASE_URL ?>/servicos" class="filter-form service-filters">
        <label class="filter-search">Pesquisar<input type="search" name="pesquisa" value="<?= e($search) ?>" placeholder="Código, cliente ou telefone"></label>
        <label>Estado<select name="estado">
                <option value="">Todos</option><?php foreach ($labels as $key => $label): ?><option value="<?= e($key) ?>" <?= $status === $key ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
            </select></label>
        <label>De<input type="date" name="inicio" value="<?= e($from) ?>"></label><label>Até<input type="date" name="fim" value="<?= e($to) ?>"></label>
        <div class="filter-actions"><button class="button button-primary">Aplicar</button><a class="button button-outline" href="<?= BASE_URL ?>/servicos">Limpar</a></div>
    </form>
</section>
<section class="panel">
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
                    <th>Saldo</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$services): ?><tr>
                        <td colspan="8" class="empty-state">Nenhum serviço encontrado.</td>
                    </tr><?php endif; ?>
                <?php foreach ($services as $service): $balance = max(0, (float)$service['total'] - (float)$service['pago']); ?><tr>
                        <td><strong><?= e($service['codigo']) ?></strong></td>
                        <td><?= e($service['cliente']) ?><small class="table-secondary"><?= e($service['telefone']) ?></small></td>
                        <td><?= e(date('d/m/Y H:i', strtotime($service['data_entrada']))) ?></td>
                        <td><?= e(date('d/m/Y H:i', strtotime($service['data_prevista']))) ?></td>
                        <td><span class="status status-<?= e($service['estado']) ?>"><?= e($labels[$service['estado']] ?? $service['estado']) ?></span></td>
                        <td><?= number_format((float)$service['total'], 2, ',', '.') ?> Kz</td>
                        <td><?= number_format($balance, 2, ',', '.') ?> Kz</td>
                        <td><a class="button button-small button-outline" href="<?= BASE_URL ?>/servicos/ver?id=<?= (int)$service['id'] ?>">Detalhes</a></td>
                    </tr><?php endforeach; ?></tbody>
        </table>
    </div>
    <?php if ($totalPages > 1): ?><nav class="pagination"><span>Página <?= $page ?> de <?= $totalPages ?></span>
            <div class="table-actions"><?php $base = ['pesquisa' => $search, 'estado' => $status, 'inicio' => $from, 'fim' => $to];
                                        if ($page > 1): ?><a class="pagination-link" href="?<?= http_build_query($base + ['pagina' => $page - 1]) ?>">Anterior</a><?php endif;
                                                                                                                                                                if ($page < $totalPages): ?><a class="pagination-link" href="?<?= http_build_query($base + ['pagina' => $page + 1]) ?>">Seguinte</a><?php endif; ?></div>
        </nav><?php endif; ?>
</section>