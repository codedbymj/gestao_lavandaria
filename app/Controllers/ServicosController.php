<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Middleware\RoleMiddleware;
use App\Models\AuditLog;
use App\Models\Catalogo;
use App\Models\Servico;

final class ServicosController extends Controller
{
    private Servico $model;
    private const TRANSITIONS = [
        'recebido' => ['em_lavagem', 'cancelado'],
        'em_lavagem' => ['em_secagem', 'cancelado'],
        'em_secagem' => ['em_engomagem', 'pronto', 'cancelado'],
        'em_engomagem' => ['pronto', 'cancelado'],
        'pronto' => [],
        'entregue' => [],
        'cancelado' => [],
    ];

    public function __construct()
    {
        RoleMiddleware::allow(['Administrador', 'Gestor', 'Atendente']);
        $this->model = new Servico();
    }

    public function index(): void
    {
        $search = trim($_GET['pesquisa'] ?? '');
        $status = $_GET['estado'] ?? '';
        $from = $_GET['inicio'] ?? '';
        $to = $_GET['fim'] ?? '';
        $page = max(1, filter_var($_GET['pagina'] ?? 1, FILTER_VALIDATE_INT) ?: 1);
        $perPage = 10;
        $total = $this->model->countFiltered($search, $status, $from, $to);
        $pages = max(1, (int)ceil($total / $perPage));
        $page = min($page, $pages);
        $this->view('servicos/index', [
            'title' => 'Serviços',
            'services' => $this->model->paginate($search, $status, $from, $to, $page, $perPage),
            'search' => $search,
            'status' => $status,
            'from' => $from,
            'to' => $to,
            'page' => $page,
            'totalPages' => $pages,
            'total' => $total,
        ]);
    }

    public function create(): void
    {
        $this->view('servicos/create', [
            'title' => 'Novo serviço',
            'clients' => $this->model->activeClients(),
            'prices' => (new Catalogo())->activePrices(),
        ]);
    }

    public function store(): void
    {
        $this->csrf('/servicos/novo');
        $clientId = filter_var($_POST['cliente_id'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
        $discountPercentage = filter_var(
            str_replace(
                ',',
                '.',
                $_POST['desconto_percentual'] ?? '0'
            ),
            FILTER_VALIDATE_FLOAT
        );

        $discountPercentage =
            $discountPercentage === false
                ? 0
                : (float) $discountPercentage;
        $priceIds = $_POST['preco_id'] ?? [];
        $quantities = $_POST['quantidade'] ?? [];
        $colors = $_POST['cor'] ?? [];
        $notes = $_POST['item_observacoes'] ?? [];
        $items = [];
        foreach ($priceIds as $index => $priceId) {
            $id = filter_var($priceId, FILTER_VALIDATE_INT) ?: 0;
            if ($id > 0) $items[] = ['preco_id' => $id, 'quantidade' => $quantities[$index] ?? 1, 'cor' => trim($colors[$index] ?? ''), 'observacoes' => trim($notes[$index] ?? '')];
        }
        if (!$clientId) {
            Session::flash('erro', 'Selecione um cliente.');
            $this->redirect('/servicos/novo');
        }
        try {
            $header = [
                'cliente_id' => $clientId,

                'desconto_percentual' =>
                    $discountPercentage,

                'observacoes' => trim(
                    $_POST['observacoes'] ?? ''
                ),
            ];
            $id = $this->model->create($header, $items, (int)Session::user()['id']);
            (new AuditLog())->record(
                (int) Session::user()['id'],
                'CREATE',
                'servicos',
                $id,
                'Serviço registado.',
                null,
                [
                    'cliente_id' => $clientId,
                    'itens' => count($items),

                    'desconto_percentual' =>
                        $discountPercentage,
                ]
            );
            Session::flash('sucesso', 'Serviço registado e preço calculado com sucesso.');
            $this->redirect('/servicos/ver?id=' . $id);
        } catch (\Throwable $exception) {
            Session::flash('erro', $exception->getMessage());
            $this->redirect('/servicos/novo');
        }
    }

    public function show(): void
    {
        $service = $this->serviceOrRedirect();
        $items = $this->model->items((int) $service['id']);

        $this->view('servicos/show', [
            'title' => 'Serviço ' . $service['codigo'],
            'service' => $service,
            'items' => $items,
            'history' => $this->model->history(
                (int) $service['id']
            ),
            'allowedStatuses' => $this->allowedStatuses(
                $service,
                $items
            ),
            'payments' => (new \App\Models\Pagamento())->byService(
                (int) $service['id']
            ),
            'delivery' => (new \App\Models\Entrega())->findByService(
                (int) $service['id']
            ),
        ]);
    }

    public function updateStatus(): void
    {
        $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
        $this->csrf('/servicos/ver?id=' . $id);
        $service = $this->model->find($id);
        $new = $_POST['estado'] ?? '';

        $allowed = $service
            ? $this->allowedStatuses($service)
            : [];

        if (!$service || !in_array($new, $allowed, true)) {
            Session::flash(
                'erro',
                'Alteração de estado não permitida.'
            );

            $this->redirect('/servicos/ver?id=' . $id);
        }
        if ($new === 'cancelado' && (float)$service['pago'] > 0) {
            Session::flash('erro', 'Anule os pagamentos antes de cancelar o serviço.');
            $this->redirect('/servicos/ver?id=' . $id);
        }
        $this->model->updateStatus($id, $new, (int)Session::user()['id'], trim($_POST['observacao'] ?? ''));
        (new AuditLog())->record((int)Session::user()['id'], 'UPDATE', 'servicos', $id, 'Estado do serviço alterado.', ['estado' => $service['estado']], ['estado' => $new]);
        Session::flash('sucesso', 'Estado atualizado com sucesso.');
        $this->redirect('/servicos/ver?id=' . $id);
    }

    public function update(): void
    {
        $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
        $this->csrf('/servicos/ver?id=' . $id);
        $service = $this->model->find($id);
        if (!$service || in_array($service['estado'], ['entregue', 'cancelado'], true)) {
            Session::flash('erro', 'Este serviço já não pode ser editado.');
            $this->redirect('/servicos/ver?id=' . $id);
        }
        $expected = str_replace('T', ' ', trim($_POST['data_prevista'] ?? ''));
        if (!strtotime($expected)) {
            Session::flash('erro', 'Informe uma data prevista válida.');
            $this->redirect('/servicos/ver?id=' . $id);
        }
        $discountPercentage = filter_var(
            str_replace(
                ',',
                '.',
                $_POST['desconto_percentual'] ?? '0'
            ),
            FILTER_VALIDATE_FLOAT
        );

        $discountPercentage =
            $discountPercentage === false
                ? 0
                : (float) $discountPercentage;
        try {
            $this->model->updateBasic($id, $expected, $discountPercentage, trim($_POST['observacoes'] ?? ''));
            (new AuditLog())->record((int)Session::user()['id'], 'UPDATE', 'servicos', $id, 'Dados gerais do serviço atualizados.', $service, ['data_prevista' => $expected, 'desconto_percentual' => $discountPercentage]);
            Session::flash('sucesso', 'Serviço atualizado.');
        } catch (\Throwable $e) {
            Session::flash('erro', $e->getMessage());
        }
        $this->redirect('/servicos/ver?id=' . $id);
    }

    private function allowedStatuses(
        array $service,
        ?array $items = null
    ): array {
        $items ??= $this->model->items(
            (int) $service['id']
        );

        $onlyIroning = $items !== [];

        foreach ($items as $item) {
            if (
                mb_strtolower(trim($item['tipo_servico']))
                !== 'engomagem'
            ) {
                $onlyIroning = false;
                break;
            }
        }

        if ($onlyIroning) {
            $ironingTransitions = [
                'recebido' => [
                    'em_engomagem',
                    'cancelado'
                ],
                'em_engomagem' => [
                    'pronto',
                    'cancelado'
                ],
                'pronto' => [],
                'entregue' => [],
                'cancelado' => [],
            ];

            return $ironingTransitions[
                $service['estado']
            ] ?? [];
        }

        return self::TRANSITIONS[
            $service['estado']
        ] ?? [];
    }

    private function serviceOrRedirect(): array
    {
        $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
        $service = $this->model->find($id);
        if (!$service) {
            Session::flash('erro', 'Serviço não encontrado.');
            $this->redirect('/servicos');
        }
        return $service;
    }

    private function csrf(string $path): void
    {
        if (!Session::validCsrf($_POST['csrf_token'] ?? null)) {
            Session::flash('erro', 'A sessão do formulário expirou.');
            $this->redirect($path);
        }
    }
}
