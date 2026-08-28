<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Middleware\AuthMiddleware;
use App\Models\AuditLog;
use App\Models\Cliente;

final class ClientesController extends Controller
{
    private Cliente $model;

    public function __construct()
    {
        AuthMiddleware::handle();
        $this->model = new Cliente();
    }

    public function index(): void
    {
        $search = trim($_GET['pesquisa'] ?? '');
        $status = $_GET['estado'] ?? '';
        $order = $_GET['ordem'] ?? 'recentes';
        $page = max(1, filter_var($_GET['pagina'] ?? 1, FILTER_VALIDATE_INT) ?: 1);
        $perPage = 10;

        $total = $this->model->countFiltered($search, $status);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $totalPages);

        $this->view('clientes/index', [
            'title' => 'Clientes',
            'clients' => $this->model->paginate(
                $search,
                $status,
                $order,
                $page,
                $perPage
            ),
            'search' => $search,
            'status' => $status,
            'order' => $order,
            'page' => $page,
            'total' => $total,
            'totalPages' => $totalPages,
        ]);
    }

    public function create(): void
    {
        $this->view('clientes/create', [
            'title' => 'Novo cliente',
            'client' => $this->emptyClient(),
            'errors' => [],
        ]);
    }

    public function store(): void
    {
        $this->validateCsrf('/clientes/novo');
        $data = $this->formData();
        $errors = $this->validate($data);

        if ($this->model->documentExists($data['documento'])) {
            $errors['documento'] = 'Já existe um cliente com este documento.';
        }

        if ($errors) {
            http_response_code(422);
            $this->view('clientes/create', [
                'title' => 'Novo cliente',
                'client' => $data,
                'errors' => $errors,
            ]);
            return;
        }

        $id = $this->model->create($data);
        (new AuditLog())->record(
            (int) Session::user()['id'],
            'CREATE',
            'clientes',
            $id,
            'Cliente cadastrado.',
            null,
            $data
        );

        Session::flash('sucesso', 'Cliente cadastrado com sucesso.');
        $this->redirect('/clientes');
    }

    public function edit(): void
    {
        $client = $this->findOrRedirect($this->requestedId());

        $this->view('clientes/edit', [
            'title' => 'Editar cliente',
            'client' => $client,
            'errors' => [],
        ]);
    }

    public function update(): void
    {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT) ?: 0;
        $this->validateCsrf('/clientes/editar?id=' . $id);
        $oldClient = $this->findOrRedirect($id);
        $data = $this->formData();
        $errors = $this->validate($data);

        if ($this->model->documentExists($data['documento'], $id)) {
            $errors['documento'] = 'Já existe outro cliente com este documento.';
        }

        if ($errors) {
            http_response_code(422);
            $data['id'] = $id;
            $this->view('clientes/edit', [
                'title' => 'Editar cliente',
                'client' => $data,
                'errors' => $errors,
            ]);
            return;
        }

        $this->model->update($id, $data);
        (new AuditLog())->record(
            (int) Session::user()['id'],
            'UPDATE',
            'clientes',
            $id,
            'Dados do cliente atualizados.',
            $oldClient,
            $data
        );

        Session::flash('sucesso', 'Cliente atualizado com sucesso.');
        $this->redirect('/clientes');
    }

    public function delete(): void
    {
        $this->validateCsrf('/clientes');
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT) ?: 0;
        $client = $this->findOrRedirect($id);

        if ($client['estado'] === 'inativo') {
            Session::flash('erro', 'Este cliente já está inativo.');
            $this->redirect('/clientes');
        }

        $this->model->deactivate($id);
        (new AuditLog())->record(
            (int) Session::user()['id'],
            'DELETE',
            'clientes',
            $id,
            'Cliente desativado.',
            $client,
            ['estado' => 'inativo']
        );

        Session::flash('sucesso', 'Cliente desativado com sucesso.');
        $this->redirect('/clientes');
    }

    private function requestedId(): int
    {
        return filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT) ?: 0;
    }

    private function findOrRedirect(int $id): array
    {
        $client = $id > 0 ? $this->model->find($id) : null;

        if (!$client) {
            Session::flash('erro', 'Cliente não encontrado.');
            $this->redirect('/clientes');
        }

        return $client;
    }

    private function formData(): array
    {
        return [
            'nome' => trim($_POST['nome'] ?? ''),
            'telefone' => trim($_POST['telefone'] ?? ''),
            'email' => mb_strtolower(trim($_POST['email'] ?? '')),
            'documento' => trim($_POST['documento'] ?? ''),
            'endereco' => trim($_POST['endereco'] ?? ''),
            'estado' => $_POST['estado'] ?? 'ativo',
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];

        if (mb_strlen($data['nome']) < 3 || mb_strlen($data['nome']) > 120) {
            $errors['nome'] = 'O nome deve ter entre 3 e 120 caracteres.';
        }

        if (!preg_match('/^[0-9+()\s-]{7,25}$/', $data['telefone'])) {
            $errors['telefone'] = 'Informe um número de telefone válido.';
        }

        if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Informe um email válido.';
        }

        if (mb_strlen($data['documento']) > 40) {
            $errors['documento'] = 'O documento não pode ultrapassar 40 caracteres.';
        }

        if (mb_strlen($data['endereco']) > 255) {
            $errors['endereco'] = 'O endereço não pode ultrapassar 255 caracteres.';
        }

        if (!in_array($data['estado'], ['ativo', 'inativo'], true)) {
            $errors['estado'] = 'Selecione um estado válido.';
        }

        return $errors;
    }

    private function validateCsrf(string $returnPath): void
    {
        if (!Session::validCsrf($_POST['csrf_token'] ?? null)) {
            Session::flash('erro', 'A sessão do formulário expirou. Tente novamente.');
            $this->redirect($returnPath);
        }
    }

    private function emptyClient(): array
    {
        return [
            'nome' => '',
            'telefone' => '',
            'email' => '',
            'documento' => '',
            'endereco' => '',
            'estado' => 'ativo',
        ];
    }
}
