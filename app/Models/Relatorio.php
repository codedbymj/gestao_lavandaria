<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Relatorio
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function services(string $from, string $to, string $status): array
    {
        $params = [];
        $where = $this->where($from, $to, $status, $params);
        $statement = $this->db->prepare(
            "SELECT s.codigo,c.nome AS cliente,s.estado,s.data_entrada,s.data_entrega,
                    s.total,COALESCE((SELECT SUM(p.valor) FROM pagamentos p
                    WHERE p.servico_id=s.id AND p.estado='confirmado'),0) AS pago
             FROM servicos s INNER JOIN clientes c ON c.id=s.cliente_id
             {$where} ORDER BY s.data_entrada DESC"
        );
        $statement->execute($params);
        return $statement->fetchAll();
    }

    public function summary(string $from, string $to, string $status): array
    {
        $params = [];
        $where = $this->where($from, $to, $status, $params);
        $statement = $this->db->prepare(
            "SELECT COUNT(*) AS quantidade,COALESCE(SUM(s.total),0) AS faturado,
                    COALESCE(SUM((SELECT COALESCE(SUM(p.valor),0) FROM pagamentos p
                    WHERE p.servico_id=s.id AND p.estado='confirmado')),0) AS recebido
             FROM servicos s INNER JOIN clientes c ON c.id=s.cliente_id {$where}"
        );
        $statement->execute($params);
        return $statement->fetch();
    }

    public function statusData(): array
    {
        return $this->db->query('SELECT estado,COUNT(*) AS total FROM servicos GROUP BY estado ORDER BY total DESC')->fetchAll();
    }

    public function monthlyRevenue(): array
    {
        $sql = "SELECT DATE_FORMAT(cal.mes,'%Y-%m') AS chave,DATE_FORMAT(cal.mes,'%m/%Y') AS mes,
                    COALESCE(SUM(p.valor),0) AS total
             FROM (
                SELECT DATE_SUB(DATE_FORMAT(CURDATE(),'%Y-%m-01'),INTERVAL n MONTH) AS mes
                FROM (SELECT 0 n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5) nums
             ) cal
             LEFT JOIN pagamentos p ON DATE_FORMAT(p.pago_em,'%Y-%m')=DATE_FORMAT(cal.mes,'%Y-%m') AND p.estado='confirmado'
             GROUP BY cal.mes ORDER BY cal.mes";
        return $this->db->query($sql)->fetchAll();
    }

    private function where(string $from, string $to, string $status, array &$params): string
    {
        $conditions = [];
        $valid = ['recebido', 'em_lavagem', 'em_secagem', 'em_engomagem', 'pronto', 'entregue', 'cancelado'];
        if ($from !== '') {
            $conditions[] = 'DATE(s.data_entrada)>=:inicio';
            $params['inicio'] = $from;
        }
        if ($to !== '') {
            $conditions[] = 'DATE(s.data_entrada)<=:fim';
            $params['fim'] = $to;
        }
        if (in_array($status, $valid, true)) {
            $conditions[] = 's.estado=:estado';
            $params['estado'] = $status;
        } else {
            $conditions[] = "s.estado<>'cancelado'";
        }
        return $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
    }
}
