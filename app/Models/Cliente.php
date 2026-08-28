<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Cliente
{
    private PDO $db;

    private const ORDER_COLUMNS = [
        'nome_asc' => 'nome ASC',
        'nome_desc' => 'nome DESC',
        'recentes' => 'criado_em DESC',
        'antigos' => 'criado_em ASC',
    ];

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function paginate(
        string $search,
        string $status,
        string $order,
        int $page,
        int $perPage
    ): array {
        $parameters = [];
        $where = $this->buildWhere($search, $status, $parameters);
        $orderBy = self::ORDER_COLUMNS[$order] ?? self::ORDER_COLUMNS['recentes'];
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT id, nome, telefone, email, documento, endereco,
                       estado, criado_em, atualizado_em
                FROM clientes
                {$where}
                ORDER BY {$orderBy}
                LIMIT :limite OFFSET :deslocamento";

        $statement = $this->db->prepare($sql);

        foreach ($parameters as $key => $value) {
            $statement->bindValue($key, $value);
        }

        $statement->bindValue(':limite', $perPage, PDO::PARAM_INT);
        $statement->bindValue(':deslocamento', $offset, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function countFiltered(string $search, string $status): int
    {
        $parameters = [];
        $where = $this->buildWhere($search, $status, $parameters);
        $statement = $this->db->prepare("SELECT COUNT(*) FROM clientes {$where}");
        $statement->execute($parameters);

        return (int) $statement->fetchColumn();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT id, nome, telefone, email, documento, endereco, estado,
                    criado_em, atualizado_em
             FROM clientes
             WHERE id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $client = $statement->fetch();

        return $client ?: null;
    }

    public function documentExists(string $document, ?int $ignoreId = null): bool
    {
        if ($document === '') {
            return false;
        }

        $sql = 'SELECT COUNT(*) FROM clientes WHERE documento = :documento';
        $parameters = ['documento' => $document];

        if ($ignoreId !== null) {
            $sql .= ' AND id <> :id';
            $parameters['id'] = $ignoreId;
        }

        $statement = $this->db->prepare($sql);
        $statement->execute($parameters);

        return (int) $statement->fetchColumn() > 0;
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO clientes
                (nome, telefone, email, documento, endereco, estado)
                VALUES
                (:nome, :telefone, :email, :documento, :endereco, :estado)';

        $statement = $this->db->prepare($sql);
        $statement->execute($this->databaseData($data));

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE clientes
                SET nome = :nome,
                    telefone = :telefone,
                    email = :email,
                    documento = :documento,
                    endereco = :endereco,
                    estado = :estado
                WHERE id = :id';

        $parameters = $this->databaseData($data);
        $parameters['id'] = $id;

        return $this->db->prepare($sql)->execute($parameters);
    }

    public function deactivate(int $id): bool
    {
        $statement = $this->db->prepare(
            "UPDATE clientes SET estado = 'inativo' WHERE id = :id"
        );

        return $statement->execute(['id' => $id]);
    }

    private function buildWhere(string $search, string $status, array &$parameters): string
    {
        $conditions = [];

        if ($search !== '') {
            $conditions[] = '(nome LIKE :pesquisa_nome
                              OR telefone LIKE :pesquisa_telefone
                              OR email LIKE :pesquisa_email
                              OR documento LIKE :pesquisa_documento)';
            $term = '%' . $search . '%';
            $parameters[':pesquisa_nome'] = $term;
            $parameters[':pesquisa_telefone'] = $term;
            $parameters[':pesquisa_email'] = $term;
            $parameters[':pesquisa_documento'] = $term;
        }

        if (in_array($status, ['ativo', 'inativo'], true)) {
            $conditions[] = 'estado = :estado';
            $parameters[':estado'] = $status;
        }

        return $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
    }

    private function databaseData(array $data): array
    {
        return [
            'nome' => $data['nome'],
            'telefone' => $data['telefone'],
            'email' => $data['email'] !== '' ? $data['email'] : null,
            'documento' => $data['documento'] !== '' ? $data['documento'] : null,
            'endereco' => $data['endereco'] !== '' ? $data['endereco'] : null,
            'estado' => $data['estado'],
        ];
    }
}
