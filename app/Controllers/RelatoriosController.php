<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\PdfDocument;
use App\Core\Session;
use App\Middleware\RoleMiddleware;
use App\Models\AuditLog;
use App\Models\Relatorio;

final class RelatoriosController extends Controller
{
    private Relatorio $model;

    public function __construct()
    {
        RoleMiddleware::allow(['Administrador', 'Gestor']);
        $this->model = new Relatorio();
    }

    public function index(): void
    {
        [$from, $to, $status] = $this->filters();
        $this->view('relatorios/index', ['title' => 'Relatórios', 'rows' => $this->model->services($from, $to, $status), 'summary' => $this->model->summary($from, $to, $status), 'from' => $from, 'to' => $to, 'status' => $status]);
    }

    public function pdf(): void
    {
        [$from, $to, $status] = $this->filters();
        $rows = $this->model->services($from, $to, $status);
        $summary = $this->model->summary($from, $to, $status);
        (new AuditLog())->record((int)Session::user()['id'], 'EXPORT', 'servicos', null, 'Relatório de serviços exportado em PDF.', null, ['inicio' => $from, 'fim' => $to, 'estado' => $status]);
        $pdf = new PdfDocument();
        $pdf->addPage();
        $this->header($pdf, $from, $to);
        $pdf->text(40, 88, 'Quantidade: ' . $summary['quantidade'], 10, true);
        $pdf->text(200, 88, 'Faturado: ' . number_format((float)$summary['faturado'], 2, ',', '.') . ' Kz', 10, true);
        $pdf->text(400, 88, 'Recebido: ' . number_format((float)$summary['recebido'], 2, ',', '.') . ' Kz', 10, true);
        $y = 120;
        $this->tableHeader($pdf, $y);
        foreach ($rows as $row) {
            if ($y > 790) {
                $pdf->addPage();
                $this->header($pdf, $from, $to);
                $y = 100;
                $this->tableHeader($pdf, $y);
            }
            $y += 19;
            $pdf->text(35, $y, $row['codigo'], 8);
            $pdf->text(125, $y, mb_strimwidth($row['cliente'], 0, 24, '...'), 8);
            $pdf->text(270, $y, date('d/m/Y', strtotime($row['data_entrada'])), 8);
            $pdf->text(330, $y, str_replace('_', ' ', $row['estado']), 8);
            $pdf->text(420, $y, number_format((float)$row['total'], 2, ',', '.'), 8);
            $pdf->text(500, $y, number_format((float)$row['pago'], 2, ',', '.'), 8);
            $pdf->line(35, $y + 5, 560, $y + 5);
        }
        $pdf->output('relatorio_servicos_' . date('Ymd_His') . '.pdf');
    }

    private function filters(): array
    {
        $from = $_GET['inicio'] ?? date('Y-m-01');
        $to = $_GET['fim'] ?? date('Y-m-d');
        $status = $_GET['estado'] ?? '';
        return [$from, $to, $status];
    }

    private function header(PdfDocument $pdf, string $from, string $to): void
    {
        $pdf->text(35, 38, APP_NAME, 18, true);
        $pdf->text(35, 60, 'Relatório de serviços', 13, true);
        $pdf->text(360, 40, 'Período: ' . date('d/m/Y', strtotime($from)) . ' a ' . date('d/m/Y', strtotime($to)), 9);
        $pdf->text(360, 57, 'Emitido em: ' . date('d/m/Y H:i'), 9);
        $pdf->line(35, 72, 560, 72);
    }

    private function tableHeader(PdfDocument $pdf, float &$y): void
    {
        $pdf->text(35, $y, 'Código', 8, true);
        $pdf->text(125, $y, 'Cliente', 8, true);
        $pdf->text(270, $y, 'Entrada', 8, true);
        $pdf->text(330, $y, 'Estado', 8, true);
        $pdf->text(420, $y, 'Total', 8, true);
        $pdf->text(500, $y, 'Pago', 8, true);
        $pdf->line(35, $y + 5, 560, $y + 5);
    }
}
