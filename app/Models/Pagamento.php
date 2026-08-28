<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;
use RuntimeException;

final class Pagamento
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function byService(int $serviceId): array
    {
        $statement = $this->db->prepare(
            'SELECT p.*,u.nome AS utilizador FROM pagamentos p
             INNER JOIN utilizadores u ON u.id=p.recebido_por
             WHERE p.servico_id=:id ORDER BY p.pago_em DESC'
        );
        $statement->execute(['id' => $serviceId]);
        return $statement->fetchAll();
    }

    public function create(int $serviceId, int $userId, float $value, string $method, string $reference): int
    {
        $service = (new Servico())->find($serviceId);
        if (!$service || in_array($service['estado'], ['cancelado', 'entregue'], true)) throw new RuntimeException('Este serviço não aceita novos pagamentos.');
        $balance = (float)$service['total'] - (float)$service['pago'];
        if ($value <= 0 || $value > $balance + 0.001) throw new RuntimeException('O valor deve ser superior a zero e não pode ultrapassar o saldo.');
        $methods = ['dinheiro', 'transferencia', 'tpa', 'multicaixa_express'];
        if (!in_array($method, $methods, true)) throw new RuntimeException('Método de pagamento inválido.');
        $statement = $this->db->prepare(
            "INSERT INTO pagamentos(servico_id,recebido_por,valor,metodo,referencia,estado)
             VALUES(:servico,:utilizador,:valor,:metodo,:referencia,'confirmado')"
        );
        $statement->execute(['servico' => $serviceId, 'utilizador' => $userId, 'valor' => $value, 'metodo' => $method, 'referencia' => $reference ?: null]);
        return (int)$this->db->lastInsertId();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM pagamentos WHERE id=:id LIMIT 1');
        $statement->execute(['id' => $id]);
        return $statement->fetch() ?: null;
    }

    public function annul(int $id): bool
    {
        return $this->db->prepare("UPDATE pagamentos SET estado='anulado' WHERE id=:id AND estado='confirmado'")->execute(['id' => $id]);
    }

    public function receiptData(int $id): ?array
    {
        $statement = $this->db->prepare(
            "SELECT p.id, p.servico_id, p.valor, p.metodo, p.referencia,
                    p.estado, p.pago_em,
                    s.codigo AS servico_codigo,
                    s.subtotal AS servico_subtotal,
                    s.desconto_percentual,
                    s.total AS servico_total,
                    c.nome AS cliente,
                    c.telefone AS cliente_telefone,
                    c.email AS cliente_email,
                    c.documento AS cliente_documento,
                    u.nome AS recebido_por,
                    COALESCE((
                        SELECT SUM(p2.valor)
                        FROM pagamentos p2
                        WHERE p2.servico_id = p.servico_id
                        AND p2.estado = 'confirmado'
                        AND (
                            p2.pago_em < p.pago_em
                            OR (
                                p2.pago_em = p.pago_em
                                AND p2.id <= p.id
                            )
                        )
                    ), 0) AS total_pago_ate_recibo
            FROM pagamentos p
            INNER JOIN servicos s ON s.id = p.servico_id
            INNER JOIN clientes c ON c.id = s.cliente_id
            INNER JOIN utilizadores u ON u.id = p.recebido_por
            WHERE p.id = :id
            LIMIT 1"
        );

        $statement->execute(['id' => $id]);

        return $statement->fetch() ?: null;
    }
}
