<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class AuditLog
{
    public function record(
        ?int $userId,
        string $operation,
        string $table,
        ?int $recordId,
        string $description,
        ?array $oldData = null,
        ?array $newData = null
    ): void {
        $sql = 'INSERT INTO logs_auditoria
                (utilizador_id, operacao, tabela_afetada, registo_id, descricao,
                 dados_anteriores, dados_novos, endereco_ip, user_agent)
                VALUES
                (:utilizador_id, :operacao, :tabela, :registo_id, :descricao,
                 :dados_anteriores, :dados_novos, :endereco_ip, :user_agent)';

        $statement = Database::connection()->prepare($sql);
        $statement->execute([
            'utilizador_id' => $userId,
            'operacao' => $operation,
            'tabela' => $table,
            'registo_id' => $recordId,
            'descricao' => $description,
            'dados_anteriores' => $oldData ? json_encode($oldData, JSON_UNESCAPED_UNICODE) : null,
            'dados_novos' => $newData ? json_encode($newData, JSON_UNESCAPED_UNICODE) : null,
            'endereco_ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        ]);
    }

    public function paginate(string $search, string $operation, string $from, string $to, int $page, int $perPage): array
    {
        $params = [];
        $where = $this->where($search, $operation, $from, $to, $params);
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT l.*,u.nome AS utilizador,u.email FROM logs_auditoria l
              LEFT JOIN utilizadores u ON u.id=l.utilizador_id {$where}
              ORDER BY l.criado_em DESC LIMIT :limite OFFSET :deslocamento";
        $statement = Database::connection()->prepare($sql);
        foreach ($params as $key => $value) $statement->bindValue($key, $value);
        $statement->bindValue(':limite', $perPage, \PDO::PARAM_INT);
        $statement->bindValue(':deslocamento', $offset, \PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }

    public function countFiltered(string $search, string $operation, string $from, string $to): int
    {
        $params = [];
        $where = $this->where($search, $operation, $from, $to, $params);
        $statement = Database::connection()->prepare("SELECT COUNT(*) FROM logs_auditoria l LEFT JOIN utilizadores u ON u.id=l.utilizador_id {$where}");
        $statement->execute($params);
        return (int)$statement->fetchColumn();
    }

    private function where(string $search, string $operation, string $from, string $to, array &$params): string
    {
        $conditions = [];
        if ($search !== '') {
            $term = '%' . $search . '%';
            $conditions[] = '(l.descricao LIKE :descricao OR l.tabela_afetada LIKE :tabela OR u.nome LIKE :utilizador)';
            $params[':descricao'] = $term;
            $params[':tabela'] = $term;
            $params[':utilizador'] = $term;
        }
        if (in_array($operation, ['CREATE', 'UPDATE', 'DELETE', 'LOGIN', 'LOGOUT', 'EXPORT', 'BACKUP'], true)) {
            $conditions[] = 'l.operacao=:operacao';
            $params[':operacao'] = $operation;
        }
        if ($from !== '') {
            $conditions[] = 'DATE(l.criado_em)>=:inicio';
            $params[':inicio'] = $from;
        }
        if ($to !== '') {
            $conditions[] = 'DATE(l.criado_em)<=:fim';
            $params[':fim'] = $to;
        }
        return $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
    }
}
