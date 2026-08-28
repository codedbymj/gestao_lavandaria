<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\PdfDocument;
use App\Core\Session;
use App\Middleware\RoleMiddleware;
use App\Models\AuditLog;
use App\Models\Entrega;
use App\Models\Pagamento;

final class FinanceiroController extends Controller
{
    public function __construct()
    {
        RoleMiddleware::allow(['Administrador', 'Gestor', 'Atendente']);
    }

    public function pay(): void
    {
        $serviceId = filter_var($_POST['servico_id'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
        $this->csrf('/servicos/ver?id=' . $serviceId);
        $value = filter_var(str_replace(',', '.', $_POST['valor'] ?? ''), FILTER_VALIDATE_FLOAT);
        try {
            $id = (new Pagamento())->create($serviceId, (int)Session::user()['id'], (float)$value, $_POST['metodo'] ?? '', trim($_POST['referencia'] ?? ''));
            (new AuditLog())->record((int)Session::user()['id'], 'CREATE', 'pagamentos', $id, 'Pagamento registado.', null, ['servico_id' => $serviceId, 'valor' => $value, 'metodo' => $_POST['metodo'] ?? '']);
            Session::flash('sucesso', 'Pagamento registado com sucesso.');
        } catch (\Throwable $e) {
            Session::flash('erro', $e->getMessage());
        }
        $this->redirect('/servicos/ver?id=' . $serviceId);
    }

    public function receipt(): never
    {
        $paymentId = filter_var(
            $_GET['id'] ?? 0,
            FILTER_VALIDATE_INT
        ) ?: 0;

        $payment = (new Pagamento())->receiptData($paymentId);

        if (!$payment || $payment['estado'] !== 'confirmado') {
            Session::flash(
                'erro',
                'O pagamento não existe ou não possui um recibo válido.'
            );

            $this->redirect('/servicos');
        }

        $receiptNumber = sprintf(
            'REC-%s-%06d',
            date('Y', strtotime($payment['pago_em'])),
            (int) $payment['id']
        );

        $methodLabels = [
            'dinheiro' => 'Dinheiro',
            'transferencia' => 'Transferência',
            'tpa' => 'TPA',
            'multicaixa_express' => 'Multicaixa Express',
        ];

        $discountAmount = round(
            (float) $payment['servico_subtotal']
            * (float) $payment['desconto_percentual']
            / 100,
            2
        );

        $balanceAfterPayment = max(
            0,
            (float) $payment['servico_total']
            - (float) $payment['total_pago_ate_recibo']
        );

        (new AuditLog())->record(
            (int) Session::user()['id'],
            'EXPORT',
            'pagamentos',
            (int) $payment['id'],
            'Recibo de pagamento exportado em PDF.',
            null,
            [
                'recibo' => $receiptNumber,
                'servico_id' => (int) $payment['servico_id'],
            ]
        );

        $pdf = new PdfDocument();
        $pdf->addPage();

        $pdf->text(35, 42, APP_NAME, 20, true);
        $pdf->text(35, 64, 'Sistema de Gestão de Lavandaria', 10);
        $pdf->text(405, 42, 'RECIBO', 18, true);
        $pdf->text(405, 62, $receiptNumber, 10);
        $pdf->line(35, 82, 560, 82);

        $pdf->text(35, 112, 'DADOS DO CLIENTE', 11, true);
        $pdf->text(35, 136, 'Nome: ' . $payment['cliente'], 10);
        $pdf->text(
            35,
            154,
            'Telefone: ' . ($payment['cliente_telefone'] ?: 'Não informado'),
            10
        );
        $pdf->text(
            300,
            154,
            'Documento: ' . ($payment['cliente_documento'] ?: 'Não informado'),
            10
        );
        $pdf->text(
            35,
            172,
            'Email: ' . ($payment['cliente_email'] ?: 'Não informado'),
            10
        );

        $pdf->line(35, 195, 560, 195);
        $pdf->text(35, 225, 'DADOS DO PAGAMENTO', 11, true);
        $pdf->text(
            35,
            250,
            'Serviço: ' . $payment['servico_codigo'],
            10,
            true
        );
        $pdf->text(
            300,
            250,
            'Data: ' . date('d/m/Y H:i', strtotime($payment['pago_em'])),
            10
        );
        $pdf->text(
            35,
            272,
            'Método: '
            . ($methodLabels[$payment['metodo']] ?? $payment['metodo']),
            10
        );
        $pdf->text(
            300,
            272,
            'Referência: ' . ($payment['referencia'] ?: '—'),
            10
        );
        $pdf->text(
            35,
            294,
            'Recebido por: ' . $payment['recebido_por'],
            10
        );

        $pdf->line(35, 320, 560, 320);
        $pdf->text(35, 350, 'RESUMO FINANCEIRO', 11, true);

        $pdf->text(35, 378, 'Subtotal do serviço', 10);
        $pdf->text(
            440,
            378,
            number_format(
                (float) $payment['servico_subtotal'],
                2,
                ',',
                '.'
            ) . ' Kz',
            10
        );

        $pdf->text(
            35,
            400,
            'Desconto ('
            . number_format(
                (float) $payment['desconto_percentual'],
                2,
                ',',
                '.'
            )
            . '%)',
            10
        );
        $pdf->text(
            440,
            400,
            '- ' . number_format($discountAmount, 2, ',', '.') . ' Kz',
            10
        );

        $pdf->text(35, 422, 'Total do serviço', 10, true);
        $pdf->text(
            440,
            422,
            number_format(
                (float) $payment['servico_total'],
                2,
                ',',
                '.'
            ) . ' Kz',
            10,
            true
        );

        $pdf->line(35, 444, 560, 444);
        $pdf->text(35, 478, 'VALOR RECEBIDO', 14, true);
        $pdf->text(
            405,
            478,
            number_format((float) $payment['valor'], 2, ',', '.') . ' Kz',
            14,
            true
        );

        $pdf->text(35, 510, 'Total pago até este recibo', 10);
        $pdf->text(
            440,
            510,
            number_format(
                (float) $payment['total_pago_ate_recibo'],
                2,
                ',',
                '.'
            ) . ' Kz',
            10
        );

        $pdf->text(35, 532, 'Saldo após este pagamento', 10);
        $pdf->text(
            440,
            532,
            number_format($balanceAfterPayment, 2, ',', '.') . ' Kz',
            10
        );

        $pdf->line(35, 580, 560, 580);
        $pdf->text(
            35,
            610,
            'Confirmamos o recebimento do valor indicado neste documento.',
            10
        );

        $pdf->text(35, 650, 'Assinatura/Carimbo:', 10);
        $pdf->line(140, 650, 380, 650);

        $pdf->text(
            35,
            780,
            'Documento gerado em '
            . date('d/m/Y H:i')
            . ' pelo '
            . APP_NAME
            . '.',
            8
        );

        $pdf->output('recibo_' . $receiptNumber . '.pdf');
    }

    public function annul(): void
    {
        $serviceId = filter_var($_POST['servico_id'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
        $this->csrf('/servicos/ver?id=' . $serviceId);
        $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
        $model = new Pagamento();
        $old = $model->find($id);
        if (!$old || $old['estado'] !== 'confirmado' || (int)$old['servico_id'] !== $serviceId) {
            Session::flash('erro', 'Pagamento não encontrado ou já anulado.');
            $this->redirect('/servicos/ver?id=' . $serviceId);
        }
        $service = (new \App\Models\Servico())->find($serviceId);
        if (!$service || $service['estado'] === 'entregue') {
            Session::flash('erro', 'Pagamentos de serviços entregues não podem ser anulados.');
            $this->redirect('/servicos/ver?id=' . $serviceId);
        }
        $model->annul($id);
        (new AuditLog())->record((int)Session::user()['id'], 'DELETE', 'pagamentos', $id, 'Pagamento anulado.', $old, ['estado' => 'anulado']);
        Session::flash('sucesso', 'Pagamento anulado.');
        $this->redirect('/servicos/ver?id=' . $serviceId);
    }

    public function deliver(): void
    {
        $serviceId = filter_var($_POST['servico_id'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
        $this->csrf('/servicos/ver?id=' . $serviceId);
        try {
            $data = ['nome' => trim($_POST['recebido_por_nome'] ?? ''), 'documento' => trim($_POST['documento'] ?? ''), 'observacao' => trim($_POST['observacao'] ?? '')];
            $id = (new Entrega())->create($serviceId, (int)Session::user()['id'], $data['nome'], $data['documento'], $data['observacao']);
            (new AuditLog())->record((int)Session::user()['id'], 'CREATE', 'entregas', $id, 'Entrega registada.', null, $data + ['servico_id' => $serviceId]);
            Session::flash('sucesso', 'Entrega registada. O serviço foi concluído.');
        } catch (\Throwable $e) {
            Session::flash('erro', $e->getMessage());
        }
        $this->redirect('/servicos/ver?id=' . $serviceId);
    }

    private function csrf(string $path): void
    {
        if (!Session::validCsrf($_POST['csrf_token'] ?? null)) {
            Session::flash('erro', 'A sessão do formulário expirou.');
            $this->redirect($path);
        }
    }
}
