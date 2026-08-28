<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use DateTimeImmutable;
use PDO;
use RuntimeException;

final class Danos
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function paginate(string $search, string $status, int $page, int $perPage): array
    {
        $params = [];
        $where = $this->where($search, $status, $dateFrom, $dateTo, $params);
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT d.id, i.servico_id AS itens_servico, i.tipo_peca_id, d.estado, 
                        d.preco_extra, d.observacoes, d.criado_em, d.atualizado_em,
                FROM controlo_danos d
                INNER JOIN itens_servico i ON i.id=d.item_servico_id
                {$where}
                ORDER BY d.criado_em DESC
                LIMIT :limite OFFSET :deslocamento";
        $statement = $this->db->prepare($sql);
        foreach ($params as $key => $value) $statement->bindValue($key, $value);
        $statement->bindValue(':limite', $perPage, PDO::PARAM_INT);
        $statement->bindValue(':deslocamento', $offset, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }

    public function countFiltered(string $search, string $status, string $dateFrom, string $dateTo): int
    {
        $params = [];
        $where = $this->where($search, $status, $dateFrom, $dateTo, $params);
        $statement = $this->db->prepare(
            "SELECT COUNT(*) FROM controlo_danos d INNER JOIN INNER JOIN itens_servico i ON i.id=d.item_servico_id {$where}"
        );
        $statement->execute($params);
        return (int) $statement->fetchColumn();
    }

    
}