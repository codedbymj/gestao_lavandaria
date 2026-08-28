<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use DateTimeImmutable;
use PDO;
use RuntimeException;

final class Servico
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function paginate(string $search, string $status, string $dateFrom, string $dateTo, int $page, int $perPage): array
    {
        $params = [];
        $where = $this->where($search, $status, $dateFrom, $dateTo, $params);
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT s.id, s.codigo, s.estado, s.data_entrada, s.data_prevista,
                       s.total, c.nome AS cliente, c.telefone,
                       COALESCE((SELECT SUM(p.valor) FROM pagamentos p
                                 WHERE p.servico_id=s.id AND p.estado='confirmado'),0) AS pago
                FROM servicos s
                INNER JOIN clientes c ON c.id=s.cliente_id
                {$where}
                ORDER BY s.data_entrada DESC
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
            "SELECT COUNT(*) FROM servicos s INNER JOIN clientes c ON c.id=s.cliente_id {$where}"
        );
        $statement->execute($params);
        return (int) $statement->fetchColumn();
    }

    public function activeClients(): array
    {
        return $this->db->query("SELECT id,nome,telefone FROM clientes WHERE estado='ativo' ORDER BY nome")->fetchAll();
    }

    public function create(array $header, array $rawItems, int $userId): int
    {
        if (!$rawItems) throw new RuntimeException('Adicione pelo menos uma peça.');
        $clientStatement = $this->db->prepare("SELECT COUNT(*) FROM clientes WHERE id=:id AND estado='ativo'");
        $clientStatement->execute(['id' => $header['cliente_id']]);
        if (!(int)$clientStatement->fetchColumn()) throw new RuntimeException('O cliente selecionado não está ativo.');
        $items = [];
        $subtotal = 0.0;
        $maxHours = 1;

        $priceStatement = $this->db->prepare(
            "SELECT p.id,p.tipo_peca_id,p.tipo_servico_id,p.valor,ts.prazo_horas
             FROM precos p INNER JOIN tipos_servico ts ON ts.id=p.tipo_servico_id
             INNER JOIN tipos_peca tp ON tp.id=p.tipo_peca_id
             WHERE p.id=:id AND p.estado='ativo' AND ts.estado='ativo' AND tp.estado='ativo'"
        );
        foreach ($rawItems as $raw) {
            $priceStatement->execute(['id' => $raw['preco_id']]);
            $price = $priceStatement->fetch();
            if (!$price) throw new RuntimeException('Uma das opções de preço deixou de estar disponível.');
            $quantity = max(1, min(999, (int) $raw['quantidade']));
            $lineSubtotal = round((float) $price['valor'] * $quantity, 2);
            $subtotal += $lineSubtotal;
            $maxHours = max($maxHours, (int) $price['prazo_horas']);
            $items[] = [
                'tipo_peca_id' => $price['tipo_peca_id'],
                'tipo_servico_id' => $price['tipo_servico_id'],
                'quantidade' => $quantity,
                'cor' => $raw['cor'] ?: null,
                'observacoes' => $raw['observacoes'] ?: null,
                'preco_unitario' => $price['valor'],
                'subtotal' => $lineSubtotal,
            ];
        }

        $discountPercentage = max(
            0,
            round(
                (float) $header['desconto_percentual'],
                2
            )
        );

        if ($discountPercentage > 100) {
            throw new RuntimeException(
                'A percentagem de desconto deve estar entre 0% e 100%.'
            );
        }

        $discountAmount = round(
            $subtotal * $discountPercentage / 100,
            2
        );

        $total = $subtotal - $discountAmount;
        $expected = (new DateTimeImmutable())->modify("+{$maxHours} hours")->format('Y-m-d H:i:s');

        $this->db->beginTransaction();
        try {
            $statement = $this->db->prepare(
                "INSERT INTO servicos
                (
                    codigo,
                    cliente_id,
                    recebido_por,
                    estado,
                    data_prevista,
                    subtotal,
                    desconto_percentual,
                    total,
                    observacoes
                )
                VALUES
                (
                    :codigo,
                    :cliente,
                    :utilizador,
                    'recebido',
                    :prevista,
                    :subtotal,
                    :desconto_percentual,
                    :total,
                    :observacoes
                )"
            );
            $temporaryCode = 'TMP-' . bin2hex(random_bytes(8));
            $statement->execute([
                'codigo' => $temporaryCode,
                'cliente' => $header['cliente_id'],
                'utilizador' => $userId,
                'prevista' => $expected,
                'subtotal' => $subtotal,
                'desconto_percentual' => $discountPercentage,
                'total' => $total,
                'observacoes' =>
                    $header['observacoes'] ?: null,
            ]);
            $serviceId = (int) $this->db->lastInsertId();
            $code = sprintf('LAV-%s-%06d', date('Y'), $serviceId);
            $this->db->prepare('UPDATE servicos SET codigo=:codigo WHERE id=:id')->execute(['codigo' => $code, 'id' => $serviceId]);

            $itemStatement = $this->db->prepare(
                'INSERT INTO itens_servico
                 (servico_id,tipo_peca_id,tipo_servico_id,quantidade,cor,observacoes,preco_unitario,subtotal)
                 VALUES (:servico,:peca,:tipo,:quantidade,:cor,:observacoes,:preco,:subtotal)'
            );
            foreach ($items as $item) {
                $itemStatement->execute([
                    'servico' => $serviceId,
                    'peca' => $item['tipo_peca_id'],
                    'tipo' => $item['tipo_servico_id'],
                    'quantidade' => $item['quantidade'],
                    'cor' => $item['cor'],
                    'observacoes' => $item['observacoes'],
                    'preco' => $item['preco_unitario'],
                    'subtotal' => $item['subtotal'],
                ]);
            }
            $this->addHistory($serviceId, $userId, null, 'recebido', 'Serviço registado.');
            $this->db->commit();
            return $serviceId;
        } catch (\Throwable $exception) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $exception;
        }
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare(
            "SELECT s.*,c.nome AS cliente,c.telefone AS cliente_telefone,c.email AS cliente_email,
                    u.nome AS atendente,
                    COALESCE((SELECT SUM(p.valor) FROM pagamentos p WHERE p.servico_id=s.id AND p.estado='confirmado'),0) AS pago
             FROM servicos s
             INNER JOIN clientes c ON c.id=s.cliente_id
             INNER JOIN utilizadores u ON u.id=s.recebido_por
             WHERE s.id=:id LIMIT 1"
        );
        $statement->execute(['id' => $id]);
        return $statement->fetch() ?: null;
    }

    public function items(int $serviceId): array
    {
        $statement = $this->db->prepare(
            'SELECT i.*,tp.nome AS peca,ts.nome AS tipo_servico
             FROM itens_servico i INNER JOIN tipos_peca tp ON tp.id=i.tipo_peca_id
             INNER JOIN tipos_servico ts ON ts.id=i.tipo_servico_id
             WHERE i.servico_id=:id ORDER BY i.id'
        );
        $statement->execute(['id' => $serviceId]);
        return $statement->fetchAll();
    }

    public function history(int $serviceId): array
    {
        $statement = $this->db->prepare(
            'SELECT h.*,u.nome AS utilizador FROM historico_estados h
             INNER JOIN utilizadores u ON u.id=h.utilizador_id
             WHERE h.servico_id=:id ORDER BY h.alterado_em DESC'
        );
        $statement->execute(['id' => $serviceId]);
        return $statement->fetchAll();
    }

    public function updateStatus(int $id, string $newStatus, int $userId, ?string $note): void
    {
        $service = $this->find($id);
        if (!$service) throw new RuntimeException('Serviço não encontrado.');
        $sets = ['estado=:estado'];
        if ($newStatus === 'pronto') $sets[] = 'data_conclusao=NOW()';
        if ($newStatus === 'entregue') $sets[] = 'data_entrega=NOW()';
        $this->db->beginTransaction();
        try {
            $this->db->prepare('UPDATE servicos SET ' . implode(',', $sets) . ' WHERE id=:id')
                ->execute(['estado' => $newStatus, 'id' => $id]);
            $this->addHistory($id, $userId, $service['estado'], $newStatus, $note);
            $this->db->commit();
        } catch (\Throwable $exception) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $exception;
        }
    }

    public function updateBasic(
        int $id,
        string $expected,
        float $discountPercentage,
        string $notes
    ): void {
        $service = $this->find($id);

        if (!$service) {
            throw new RuntimeException(
                'Serviço não encontrado.'
            );
        }

        if (
            $discountPercentage < 0
            || $discountPercentage > 100
        ) {
            throw new RuntimeException(
                'A percentagem de desconto deve estar entre 0% e 100%.'
            );
        }

        $sql = '
            UPDATE servicos

            SET data_prevista = :prevista,

                desconto_percentual =
                    :desconto_percentual,

                total = ROUND(
                    subtotal - (
                        subtotal
                        * :percentagem_calculo
                        / 100
                    ),
                    2
                ),

                observacoes = :observacoes

            WHERE id = :id
        ';

        $statement = $this->db->prepare($sql);

        $statement->execute([
            'prevista' => $expected,

            'desconto_percentual' =>
                $discountPercentage,

            'percentagem_calculo' =>
                $discountPercentage,

            'observacoes' =>
                $notes !== '' ? $notes : null,

            'id' => $id,
        ]);
    }

    public function addHistory(int $serviceId, int $userId, ?string $old, string $new, ?string $note): void
    {
        $this->db->prepare(
            'INSERT INTO historico_estados (servico_id,utilizador_id,estado_anterior,novo_estado,observacao)
             VALUES (:servico,:utilizador,:anterior,:novo,:observacao)'
        )->execute(['servico' => $serviceId, 'utilizador' => $userId, 'anterior' => $old, 'novo' => $new, 'observacao' => $note ?: null]);
    }

    private function where(string $search, string $status, string $from, string $to, array &$params): string
    {
        $conditions = [];
        if ($search !== '') {
            $term = '%' . $search . '%';
            $conditions[] = '(s.codigo LIKE :codigo OR c.nome LIKE :cliente OR c.telefone LIKE :telefone)';
            $params[':codigo'] = $term;
            $params[':cliente'] = $term;
            $params[':telefone'] = $term;
        }
        $valid = ['recebido', 'em_lavagem', 'em_secagem', 'em_engomagem', 'pronto', 'entregue', 'cancelado'];
        if (in_array($status, $valid, true)) {
            $conditions[] = 's.estado=:estado';
            $params[':estado'] = $status;
        }
        if ($from !== '') {
            $conditions[] = 'DATE(s.data_entrada)>=:inicio';
            $params[':inicio'] = $from;
        }
        if ($to !== '') {
            $conditions[] = 'DATE(s.data_entrada)<=:fim';
            $params[':fim'] = $to;
        }
        return $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
    }
}
