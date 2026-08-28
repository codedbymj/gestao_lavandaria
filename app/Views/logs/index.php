<section class="page-heading">
    <div>
        <p class="eyebrow">Segurança</p>
        <h1>Logs de auditoria</h1>
        <p><?= (int)$total ?> operação(ões) registada(s).</p>
    </div>
</section>
<section class="panel filter-panel">
    <form method="get" action="<?= BASE_URL ?>/logs" class="filter-form audit-filters"><label class="filter-search">Pesquisar<input name="pesquisa" value="<?= e($search) ?>" placeholder="Descrição, tabela ou utilizador"></label><label>Operação<select name="operacao">
                <option value="">Todas</option><?php foreach (['CREATE', 'UPDATE', 'DELETE', 'LOGIN', 'LOGOUT', 'EXPORT', 'BACKUP'] as $op): ?><option value="<?= $op ?>" <?= $operation === $op ? 'selected' : '' ?>><?= $op ?></option><?php endforeach; ?>
            </select></label><label>De<input type="date" name="inicio" value="<?= e($from) ?>"></label><label>Até<input type="date" name="fim" value="<?= e($to) ?>"></label>
        <div class="filter-actions"><button class="button button-primary">Aplicar</button><a class="button button-outline" href="<?= BASE_URL ?>/logs">Limpar</a></div>
    </form>
</section>
<section class="panel">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Utilizador</th>
                    <th>Operação</th>
                    <th>Tabela</th>
                    <th>Registo</th>
                    <th>Descrição</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody><?php if (!$logs): ?><tr>
                        <td colspan="7" class="empty-state">Nenhum log encontrado.</td>
                    </tr><?php endif; ?><?php foreach ($logs as $log): ?><tr>
                        <td><?= e(date('d/m/Y H:i:s', strtotime($log['criado_em']))) ?></td>
                        <td><?= e($log['utilizador'] ?: 'Sistema') ?><small class="table-secondary"><?= e($log['email'] ?? '') ?></small></td>
                        <td><span class="operation-badge operation-<?= strtolower(e($log['operacao'])) ?>"><?= e($log['operacao']) ?></span></td>
                        <td><?= e($log['tabela_afetada']) ?></td>
                        <td><?= e($log['registo_id'] ?? '—') ?></td>
                        <td><?= e($log['descricao']) ?></td>
                        <td><?= e($log['endereco_ip'] ?: '—') ?></td>
                    </tr><?php endforeach; ?></tbody>
        </table>
    </div><?php if ($totalPages > 1): ?><nav class="pagination"><span>Página <?= $page ?> de <?= $totalPages ?></span>
            <div class="table-actions"><?php $q = ['pesquisa' => $search, 'operacao' => $operation, 'inicio' => $from, 'fim' => $to];
                                        if ($page > 1): ?><a class="pagination-link" href="?<?= http_build_query($q + ['pagina' => $page - 1]) ?>">Anterior</a><?php endif;
                                                                                                                                                            if ($page < $totalPages): ?><a class="pagination-link" href="?<?= http_build_query($q + ['pagina' => $page + 1]) ?>">Seguinte</a><?php endif; ?></div>
        </nav><?php endif; ?>
</section>