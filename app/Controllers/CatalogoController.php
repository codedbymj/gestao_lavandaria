<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Middleware\RoleMiddleware;
use App\Models\AuditLog;
use App\Models\Catalogo;

final class CatalogoController extends Controller
{
    private Catalogo $model;

    public function __construct()
    {
        RoleMiddleware::allow(['Administrador', 'Gestor']);
        $this->model = new Catalogo();
    }

    public function index(): void
    {
        $pieceId = filter_var($_GET['editar_peca'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
        $typeId = filter_var($_GET['editar_tipo'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
        $priceId = filter_var($_GET['editar_preco'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
        $this->render([
            'pieceEdit' => $pieceId ? $this->model->find('tipos_peca', $pieceId) : null,
            'typeEdit' => $typeId ? $this->model->find('tipos_servico', $typeId) : null,
            'priceEdit' => $priceId ? $this->model->findPrice($priceId) : null,
        ]);
    }

    public function savePiece(): void
    {
        $this->saveCatalogItem('tipos_peca', 'peça');
    }

    public function saveServiceType(): void
    {
        $this->saveCatalogItem('tipos_servico', 'tipo de serviço');
    }

    public function savePrice(): void
    {
        $this->csrf();
        $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT) ?: null;
        $data = [
            'tipo_peca_id' => filter_var($_POST['tipo_peca_id'] ?? 0, FILTER_VALIDATE_INT) ?: 0,
            'tipo_servico_id' => filter_var($_POST['tipo_servico_id'] ?? 0, FILTER_VALIDATE_INT) ?: 0,
            'valor' => filter_var(str_replace(',', '.', $_POST['valor'] ?? ''), FILTER_VALIDATE_FLOAT),
            'estado' => $_POST['estado'] ?? 'ativo',
        ];
        if (!$data['tipo_peca_id'] || !$data['tipo_servico_id'] || $data['valor'] === false || $data['valor'] <= 0) {
            Session::flash('erro', 'Selecione a peça, o serviço e informe um preço superior a zero.');
            $this->redirect('/catalogo');
        }
        if ($this->model->combinationExists($data['tipo_peca_id'], $data['tipo_servico_id'], $id)) {
            Session::flash('erro', 'Já existe um preço para esta combinação.');
            $this->redirect('/catalogo');
        }
        $old = $id ? $this->model->findPrice($id) : null;
        $savedId = $this->model->savePrice($data, $id);
        $this->audit($id ? 'UPDATE' : 'CREATE', 'precos', $savedId, $id ? 'Preço atualizado.' : 'Preço criado.', $old, $data);
        Session::flash('sucesso', 'Preço guardado com sucesso.');
        $this->redirect('/catalogo');
    }

    public function deactivate(): void
    {
        $this->csrf();
        $entity = $_POST['entidade'] ?? '';
        $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
        if ($entity === 'peca') {
            $old = $this->model->find('tipos_peca', $id);
            $this->model->deactivateItem('tipos_peca', $id);
            $table = 'tipos_peca';
        } elseif ($entity === 'tipo_servico') {
            $old = $this->model->find('tipos_servico', $id);
            $this->model->deactivateItem('tipos_servico', $id);
            $table = 'tipos_servico';
        } elseif ($entity === 'preco') {
            $old = $this->model->findPrice($id);
            $this->model->deactivatePrice($id);
            $table = 'precos';
        } else {
            Session::flash('erro', 'Entidade inválida.');
            $this->redirect('/catalogo');
        }
        $this->audit('DELETE', $table, $id, 'Registo de catálogo desativado.', $old, ['estado' => 'inativo']);
        Session::flash('sucesso', 'Registo desativado.');
        $this->redirect('/catalogo');
    }

    private function saveCatalogItem(string $table, string $label): void
    {
        $this->csrf();
        $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT) ?: null;
        $data = [
            'nome' => trim($_POST['nome'] ?? ''),
            'descricao' => trim($_POST['descricao'] ?? ''),
            'estado' => $_POST['estado'] ?? 'ativo',
            'prazo_horas' => filter_var($_POST['prazo_horas'] ?? 48, FILTER_VALIDATE_INT) ?: 48,
        ];
        if (mb_strlen($data['nome']) < 2 || !in_array($data['estado'], ['ativo', 'inativo'], true)) {
            Session::flash('erro', 'Informe um nome e estado válidos.');
            $this->redirect('/catalogo');
        }
        if ($this->model->nameExists($table, $data['nome'], $id)) {
            Session::flash('erro', 'Já existe um registo com este nome.');
            $this->redirect('/catalogo');
        }
        $old = $id ? $this->model->find($table, $id) : null;
        $savedId = $this->model->saveItem($table, $data, $id);
        $this->audit($id ? 'UPDATE' : 'CREATE', $table, $savedId, ucfirst($label) . ' guardado.', $old, $data);
        Session::flash('sucesso', ucfirst($label) . ' guardado com sucesso.');
        $this->redirect('/catalogo');
    }

    private function render(array $edits): void
    {
        $pricePage = max(
            1,
            filter_var(
                $_GET['pagina_precos'] ?? 1,
                FILTER_VALIDATE_INT
            ) ?: 1
        );

        // Quantidade de peças apresentadas em cada página.
        $piecesPerPage = 3;

        $totalPricePieces =
            $this->model->countPiecesWithPrices();

        $totalPricePages = max(
            1,
            (int) ceil(
                $totalPricePieces / $piecesPerPage
            )
        );

        // Impede páginas superiores ao total existente.
        $pricePage = min(
            $pricePage,
            $totalPricePages
        );

        $this->view(
            'catalogo/index',
            array_merge([
                'title' => 'Catálogo e preços',

                'pieces' =>
                    $this->model->items('tipos_peca'),

                'serviceTypes' =>
                    $this->model->items('tipos_servico'),

                'activePieces' =>
                    $this->model->items(
                        'tipos_peca',
                        true
                    ),

                'activeServiceTypes' =>
                    $this->model->items(
                        'tipos_servico',
                        true
                    ),

                'priceGroups' =>
                    $this->model->pricesGroupedByPiece(
                        $pricePage,
                        $piecesPerPage
                    ),

                'pricePage' => $pricePage,

                'totalPricePages' =>
                    $totalPricePages,

                'totalPricePieces' =>
                    $totalPricePieces,
            ], $edits)
        );
    }

    private function csrf(): void
    {
        if (!Session::validCsrf($_POST['csrf_token'] ?? null)) {
            Session::flash('erro', 'A sessão do formulário expirou.');
            $this->redirect('/catalogo');
        }
    }

    private function audit(string $operation, string $table, int $id, string $description, ?array $old, array $new): void
    {
        (new AuditLog())->record((int) Session::user()['id'], $operation, $table, $id, $description, $old, $new);
    }
}
