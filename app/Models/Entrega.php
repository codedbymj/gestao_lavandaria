<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;
use RuntimeException;

final class Entrega
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function findByService(int $serviceId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT e.*,u.nome AS utilizador FROM entregas e
             INNER JOIN utilizadores u ON u.id=e.entregue_por
             WHERE e.servico_id=:id LIMIT 1'
        );
        $statement->execute(['id' => $serviceId]);
        return $statement->fetch() ?: null;
    }

    public function create(int $serviceId, int $userId, string $receiver, string $document, string $note): int
    {
        $service = (new Servico())->find($serviceId);
        if (!$service || $service['estado'] !== 'pronto') throw new RuntimeException('Apenas serviços prontos podem ser entregues.');
        if ((float)$service['pago'] + 0.001 < (float)$service['total']) throw new RuntimeException('O pagamento total deve ser confirmado antes da entrega.');
        if (mb_strlen($receiver) < 3) throw new RuntimeException('Informe quem recebeu as peças.');
        $this->db->beginTransaction();
        try {
            $statement = $this->db->prepare(
                'INSERT INTO entregas(servico_id,entregue_por,recebido_por_nome,recebido_por_documento,observacao)
                 VALUES(:servico,:utilizador,:nome,:documento,:observacao)'
            );
            $statement->execute(['servico' => $serviceId, 'utilizador' => $userId, 'nome' => $receiver, 'documento' => $document ?: null, 'observacao' => $note ?: null]);
            $id = (int)$this->db->lastInsertId();
            $this->db->prepare("UPDATE servicos SET estado='entregue',data_entrega=NOW() WHERE id=:id")->execute(['id' => $serviceId]);
            $this->db->prepare(
                "INSERT INTO historico_estados(servico_id,utilizador_id,estado_anterior,novo_estado,observacao)
                 VALUES(:servico,:utilizador,'pronto','entregue','Peças entregues ao cliente.')"
            )->execute(['servico' => $serviceId, 'utilizador' => $userId]);
            $this->db->commit();
            return $id;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }
}
