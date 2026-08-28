<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Dashboard
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function statistics(): array
    {
        return [
            'clientes' => $this->scalar('SELECT COUNT(*) FROM clientes WHERE estado = "ativo"'),
            'servicos_hoje' => $this->scalar(
                'SELECT COUNT(*) FROM servicos WHERE DATE(data_entrada) = CURDATE()'
            ),
            'em_processamento' => $this->scalar(
                "SELECT COUNT(*) FROM servicos
                 WHERE estado IN ('recebido', 'em_lavagem', 'em_secagem', 'em_engomagem')"
            ),
            'prontos' => $this->scalar(
                "SELECT COUNT(*) FROM servicos WHERE estado = 'pronto'"
            ),
            'receita_mes' => $this->scalar(
                "SELECT COALESCE(SUM(valor), 0) FROM pagamentos
                 WHERE estado = 'confirmado'
                   AND YEAR(pago_em) = YEAR(CURDATE())
                   AND MONTH(pago_em) = MONTH(CURDATE())"
            ),
        ];
    }

    public function recentServices(): array
    {
        $sql = 'SELECT s.codigo, c.nome AS cliente, s.estado, s.total,
                       s.data_entrada, s.data_prevista
                FROM servicos s
                INNER JOIN clientes c ON c.id = s.cliente_id
                ORDER BY s.data_entrada DESC
                LIMIT 5';

        return $this->db->query($sql)->fetchAll();
    }

    private function scalar(string $sql): float|int
    {
        $value = $this->db->query($sql)->fetchColumn();
        return is_numeric($value) ? (float) $value : 0;
    }
}
