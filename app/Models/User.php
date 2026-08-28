<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function count(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM utilizadores')->fetchColumn();
    }

    public function paginate(
        string $search,
        string $status,
        int $profileId,
        int $page,
        int $perPage
    ): array {
        $parameters = [];
        $where = $this->userWhere($search, $status, $profileId, $parameters);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT u.id, u.nome, u.email, u.telefone, u.estado,
                       u.ultimo_login, u.criado_em, p.nome AS perfil
                FROM utilizadores u
                INNER JOIN perfis p ON p.id = u.perfil_id
                {$where}
                ORDER BY u.criado_em DESC
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

    public function countFiltered(string $search, string $status, int $profileId): int
    {
        $parameters = [];
        $where = $this->userWhere($search, $status, $profileId, $parameters);
        $statement = $this->db->prepare("SELECT COUNT(*) FROM utilizadores u {$where}");
        $statement->execute($parameters);
        return (int) $statement->fetchColumn();
    }

    public function profiles(): array
    {
        return $this->db->query('SELECT id, nome FROM perfis ORDER BY id')->fetchAll();
    }

    public function activeAdminCount(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM utilizadores u INNER JOIN perfis p ON p.id=u.perfil_id
             WHERE p.nome='Administrador' AND u.estado='ativo'"
        )->fetchColumn();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT u.id, u.perfil_id, u.nome, u.email, u.telefone, u.estado,
                    u.ultimo_login, u.criado_em, p.nome AS perfil
             FROM utilizadores u
             INNER JOIN perfis p ON p.id = u.perfil_id
             WHERE u.id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        return $statement->fetch() ?: null;
    }

    public function emailExists(string $email, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM utilizadores WHERE email = :email';
        $parameters = ['email' => $email];
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
        $statement = $this->db->prepare(
            'INSERT INTO utilizadores
             (perfil_id, nome, email, senha, telefone, estado)
             VALUES (:perfil_id, :nome, :email, :senha, :telefone, :estado)'
        );
        $statement->execute([
            'perfil_id' => $data['perfil_id'],
            'nome' => $data['nome'],
            'email' => $data['email'],
            'senha' => password_hash($data['senha'], PASSWORD_DEFAULT),
            'telefone' => $data['telefone'] ?: null,
            'estado' => $data['estado'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = 'UPDATE utilizadores
                SET perfil_id = :perfil_id, nome = :nome, email = :email,
                    telefone = :telefone, estado = :estado';
        $parameters = [
            'perfil_id' => $data['perfil_id'],
            'nome' => $data['nome'],
            'email' => $data['email'],
            'telefone' => $data['telefone'] ?: null,
            'estado' => $data['estado'],
            'id' => $id,
        ];

        if ($data['senha'] !== '') {
            $sql .= ', senha = :senha';
            $parameters['senha'] = password_hash($data['senha'], PASSWORD_DEFAULT);
        }

        $sql .= ' WHERE id = :id';
        return $this->db->prepare($sql)->execute($parameters);
    }

    public function deactivate(int $id): bool
    {
        return $this->db->prepare(
            "UPDATE utilizadores SET estado = 'inativo' WHERE id = :id"
        )->execute(['id' => $id]);
    }

    public function findByEmail(string $email): ?array
    {
        $sql = 'SELECT u.*, p.nome AS perfil
                FROM utilizadores u
                INNER JOIN perfis p ON p.id = u.perfil_id
                WHERE u.email = :email
                LIMIT 1';

        $statement = $this->db->prepare($sql);
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function createFirstAdmin(string $name, string $email, string $password): int
    {
        $this->db->beginTransaction();

        try {
            if ($this->count() > 0) {
                throw new \RuntimeException('O administrador inicial já foi criado.');
            }

            $profileStatement = $this->db->prepare(
                "SELECT id FROM perfis WHERE nome = 'Administrador' LIMIT 1"
            );
            $profileStatement->execute();
            $profileId = $profileStatement->fetchColumn();

            if (!$profileId) {
                throw new \RuntimeException('O perfil Administrador não existe.');
            }

            $statement = $this->db->prepare(
                'INSERT INTO utilizadores (perfil_id, nome, email, senha)
                 VALUES (:perfil_id, :nome, :email, :senha)'
            );
            $statement->execute([
                'perfil_id' => $profileId,
                'nome' => $name,
                'email' => $email,
                'senha' => password_hash($password, PASSWORD_DEFAULT),
            ]);

            $id = (int) $this->db->lastInsertId();
            $this->db->commit();
            return $id;
        } catch (\Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $exception;
        }
    }

    public function registerFailedAttempt(int $id): void
    {
        $statement = $this->db->prepare(
            "UPDATE utilizadores
             SET tentativas_login = tentativas_login + 1,
                 estado = CASE
                    WHEN tentativas_login + 1 >= 5 THEN 'bloqueado'
                    ELSE estado
                 END
             WHERE id = :id"
        );
        $statement->execute(['id' => $id]);
    }

    public function registerSuccessfulLogin(int $id): void
    {
        $statement = $this->db->prepare(
            'UPDATE utilizadores
             SET tentativas_login = 0, ultimo_login = NOW()
             WHERE id = :id'
        );
        $statement->execute(['id' => $id]);
    }

    private function userWhere(
        string $search,
        string $status,
        int $profileId,
        array &$parameters
    ): string {
        $conditions = [];
        if ($search !== '') {
            $term = '%' . $search . '%';
            $conditions[] = '(u.nome LIKE :nome OR u.email LIKE :email OR u.telefone LIKE :telefone)';
            $parameters[':nome'] = $term;
            $parameters[':email'] = $term;
            $parameters[':telefone'] = $term;
        }
        if (in_array($status, ['ativo', 'inativo', 'bloqueado'], true)) {
            $conditions[] = 'u.estado = :estado';
            $parameters[':estado'] = $status;
        }
        if ($profileId > 0) {
            $conditions[] = 'u.perfil_id = :perfil_id';
            $parameters[':perfil_id'] = $profileId;
        }
        return $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
    }
}
