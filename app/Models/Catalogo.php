<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Catalogo
{
    private PDO $db;
    private const TABLES = ['tipos_peca', 'tipos_servico'];

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function items(string $table, bool $activeOnly = false): array
    {
        $this->assertTable($table);
        $where = $activeOnly ? "WHERE estado = 'ativo'" : '';
        $extra = $table === 'tipos_servico' ? ', prazo_horas' : '';
        return $this->db->query(
            "SELECT id, nome, descricao, estado {$extra} FROM {$table} {$where} ORDER BY nome"
        )->fetchAll();
    }

    public function find(string $table, int $id): ?array
    {
        $this->assertTable($table);
        $statement = $this->db->prepare("SELECT * FROM {$table} WHERE id = :id LIMIT 1");
        $statement->execute(['id' => $id]);
        return $statement->fetch() ?: null;
    }

    public function nameExists(string $table, string $name, ?int $ignoreId = null): bool
    {
        $this->assertTable($table);
        $sql = "SELECT COUNT(*) FROM {$table} WHERE nome = :nome";
        $parameters = ['nome' => $name];
        if ($ignoreId) {
            $sql .= ' AND id <> :id';
            $parameters['id'] = $ignoreId;
        }
        $statement = $this->db->prepare($sql);
        $statement->execute($parameters);
        return (int) $statement->fetchColumn() > 0;
    }

    public function saveItem(string $table, array $data, ?int $id = null): int
    {
        $this->assertTable($table);
        $fields = ['nome' => $data['nome'], 'descricao' => $data['descricao'] ?: null, 'estado' => $data['estado']];
        if ($table === 'tipos_servico') $fields['prazo_horas'] = $data['prazo_horas'];

        if ($id) {
            $assignments = implode(', ', array_map(fn($field) => "{$field} = :{$field}", array_keys($fields)));
            $fields['id'] = $id;
            $this->db->prepare("UPDATE {$table} SET {$assignments} WHERE id = :id")->execute($fields);
            return $id;
        }

        $columns = implode(', ', array_keys($fields));
        $placeholders = ':' . implode(', :', array_keys($fields));
        $this->db->prepare("INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})")->execute($fields);
        return (int) $this->db->lastInsertId();
    }

    public function deactivateItem(string $table, int $id): bool
    {
        $this->assertTable($table);
        return $this->db->prepare("UPDATE {$table} SET estado = 'inativo' WHERE id = :id")->execute(['id' => $id]);
    }

    public function prices(): array
    {
        return $this->db->query(
            'SELECT p.id, p.tipo_peca_id, p.tipo_servico_id, p.valor, p.estado,
                    tp.nome AS peca, ts.nome AS tipo_servico
             FROM precos p
             INNER JOIN tipos_peca tp ON tp.id = p.tipo_peca_id
             INNER JOIN tipos_servico ts ON ts.id = p.tipo_servico_id
             ORDER BY tp.nome, ts.nome'
        )->fetchAll();
    }

    public function countPiecesWithPrices(): int
    {
        return (int) $this->db->query(
            'SELECT COUNT(DISTINCT tipo_peca_id) FROM precos'
        )->fetchColumn();
    }

    public function pricesGroupedByPiece(
        int $page,
        int $piecesPerPage
    ): array {
        $offset = ($page - 1) * $piecesPerPage;

        /*
        * Primeiro seleciona apenas as peças da página atual.
        * A paginação é feita por peças, não por preços.
        */
        $pieceStatement = $this->db->prepare(
            'SELECT tp.id, tp.nome
            FROM tipos_peca tp
            WHERE EXISTS (
                SELECT 1
                FROM precos p
                WHERE p.tipo_peca_id = tp.id
            )
            ORDER BY tp.nome
            LIMIT :limite OFFSET :deslocamento'
        );

        $pieceStatement->bindValue(
            ':limite',
            $piecesPerPage,
            PDO::PARAM_INT
        );

        $pieceStatement->bindValue(
            ':deslocamento',
            $offset,
            PDO::PARAM_INT
        );

        $pieceStatement->execute();
        $pieces = $pieceStatement->fetchAll();

        if (!$pieces) {
            return [];
        }

        $pieceIds = array_map(
            'intval',
            array_column($pieces, 'id')
        );

        $placeholders = implode(
            ',',
            array_fill(0, count($pieceIds), '?')
        );

        /*
        * Depois procura todos os preços das peças selecionadas.
        */
        $priceStatement = $this->db->prepare(
            "SELECT p.id,
                    p.tipo_peca_id,
                    p.tipo_servico_id,
                    p.valor,
                    p.estado,
                    tp.nome AS peca,
                    ts.nome AS tipo_servico
            FROM precos p
            INNER JOIN tipos_peca tp
                ON tp.id = p.tipo_peca_id
            INNER JOIN tipos_servico ts
                ON ts.id = p.tipo_servico_id
            WHERE p.tipo_peca_id IN ($placeholders)
            ORDER BY tp.nome, ts.nome"
        );

        $priceStatement->execute($pieceIds);

        /*
        * Cria um grupo para cada peça.
        */
        $groups = [];

        foreach ($pieces as $piece) {
            $groups[(int) $piece['id']] = [
                'id' => (int) $piece['id'],
                'nome' => $piece['nome'],
                'precos' => [],
            ];
        }

        /*
        * Coloca cada preço dentro da respetiva peça.
        */
        foreach ($priceStatement->fetchAll() as $price) {
            $pieceId = (int) $price['tipo_peca_id'];
            $groups[$pieceId]['precos'][] = $price;
        }

        return array_values($groups);
    }

    public function activePrices(): array
    {
        return $this->db->query(
            "SELECT p.id, p.tipo_peca_id, p.tipo_servico_id, p.valor,
                    tp.nome AS peca, ts.nome AS tipo_servico, ts.prazo_horas
             FROM precos p
             INNER JOIN tipos_peca tp ON tp.id = p.tipo_peca_id AND tp.estado = 'ativo'
             INNER JOIN tipos_servico ts ON ts.id = p.tipo_servico_id AND ts.estado = 'ativo'
             WHERE p.estado = 'ativo'
             ORDER BY tp.nome, ts.nome"
        )->fetchAll();
    }

    public function findPrice(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM precos WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        return $statement->fetch() ?: null;
    }

    public function combinationExists(int $pieceId, int $serviceTypeId, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM precos WHERE tipo_peca_id = :peca AND tipo_servico_id = :tipo';
        $parameters = ['peca' => $pieceId, 'tipo' => $serviceTypeId];
        if ($ignoreId) {
            $sql .= ' AND id <> :id';
            $parameters['id'] = $ignoreId;
        }
        $statement = $this->db->prepare($sql);
        $statement->execute($parameters);
        return (int) $statement->fetchColumn() > 0;
    }

    public function savePrice(array $data, ?int $id = null): int
    {
        $parameters = [
            'tipo_peca_id' => $data['tipo_peca_id'],
            'tipo_servico_id' => $data['tipo_servico_id'],
            'valor' => $data['valor'],
            'estado' => $data['estado'],
        ];
        if ($id) {
            $parameters['id'] = $id;
            $this->db->prepare(
                'UPDATE precos SET tipo_peca_id=:tipo_peca_id, tipo_servico_id=:tipo_servico_id,
                 valor=:valor, estado=:estado WHERE id=:id'
            )->execute($parameters);
            return $id;
        }
        $this->db->prepare(
            'INSERT INTO precos (tipo_peca_id, tipo_servico_id, valor, estado)
             VALUES (:tipo_peca_id, :tipo_servico_id, :valor, :estado)'
        )->execute($parameters);
        return (int) $this->db->lastInsertId();
    }

    public function deactivatePrice(int $id): bool
    {
        return $this->db->prepare("UPDATE precos SET estado='inativo' WHERE id=:id")->execute(['id' => $id]);
    }

    private function assertTable(string $table): void
    {
        if (!in_array($table, self::TABLES, true)) {
            throw new \InvalidArgumentException('Tabela de catálogo inválida.');
        }
    }
}
