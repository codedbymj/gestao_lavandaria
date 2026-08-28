<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Middleware\RoleMiddleware;
use App\Models\AuditLog;
use App\Models\User;

final class UtilizadoresController extends Controller
{
    private User $model;

    public function __construct()
    {
        RoleMiddleware::allow(['Administrador']);
        $this->model = new User();
    }

    public function index(): void
    {
        $search = trim($_GET['pesquisa'] ?? '');
        $status = $_GET['estado'] ?? '';
        $profileId = filter_var($_GET['perfil_id'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
        $page = max(1, filter_var($_GET['pagina'] ?? 1, FILTER_VALIDATE_INT) ?: 1);
        $perPage = 10;
        $total = $this->model->countFiltered($search, $status, $profileId);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $totalPages);

        $this->view('utilizadores/index', [
            'title' => 'Utilizadores',
            'users' => $this->model->paginate($search, $status, $profileId, $page, $perPage),
            'profiles' => $this->model->profiles(),
            'search' => $search,
            'status' => $status,
            'profileId' => $profileId,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
        ]);
    }

    public function create(): void
    {
        $this->view('utilizadores/form', [
            'title' => 'Novo utilizador',
            'userData' => $this->emptyData(),
            'profiles' => $this->model->profiles(),
            'errors' => [],
            'formAction' => BASE_URL . '/utilizadores/novo',
            'submitLabel' => 'Cadastrar utilizador',
        ]);
    }

    public function store(): void
    {
        $this->csrf('/utilizadores/novo');
        $data = $this->data();
        $errors = $this->validate($data, true);
        if ($this->model->emailExists($data['email'])) {
            $errors['email'] = 'Este email já está registado.';
        }
        if ($errors) {
            $this->formError($data, $errors, 'Novo utilizador', '/utilizadores/novo', 'Cadastrar utilizador');
            return;
        }
        $id = $this->model->create($data);
        (new AuditLog())->record((int) Session::user()['id'], 'CREATE', 'utilizadores', $id, 'Utilizador criado.', null, $this->safeData($data));
        Session::flash('sucesso', 'Utilizador cadastrado com sucesso.');
        $this->redirect('/utilizadores');
    }

    public function edit(): void
    {
        $id = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
        $user = $this->model->find($id);
        if (!$user) {
            Session::flash('erro', 'Utilizador não encontrado.');
            $this->redirect('/utilizadores');
        }
        $user['senha'] = '';
        $this->view('utilizadores/form', [
            'title' => 'Editar utilizador',
            'userData' => $user,
            'profiles' => $this->model->profiles(),
            'errors' => [],
            'formAction' => BASE_URL . '/utilizadores/editar',
            'submitLabel' => 'Guardar alterações',
        ]);
    }

    public function update(): void
    {
        $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
        $this->csrf('/utilizadores/editar?id=' . $id);
        $old = $this->model->find($id);
        if (!$old) {
            Session::flash('erro', 'Utilizador não encontrado.');
            $this->redirect('/utilizadores');
        }
        $data = $this->data();
        $data['id'] = $id;
        $errors = $this->validate($data, false);
        if ($this->model->emailExists($data['email'], $id)) {
            $errors['email'] = 'Este email já pertence a outro utilizador.';
        }
        if ($id === (int) Session::user()['id'] && $data['estado'] !== 'ativo') {
            $errors['estado'] = 'Não pode desativar a conta que está a utilizar.';
        }
        if ($id === (int) Session::user()['id'] && (int)$data['perfil_id'] !== (int)Session::user()['perfil_id']) {
            $errors['perfil_id'] = 'Não pode alterar o perfil da conta que está a utilizar.';
        }
        if ($old['perfil'] === 'Administrador' && ((int)$data['perfil_id'] !== (int)$old['perfil_id'] || $data['estado'] !== 'ativo') && $this->model->activeAdminCount() <= 1) {
            $errors['perfil_id'] = 'O sistema deve manter pelo menos um administrador ativo.';
        }
        if ($errors) {
            $this->formError($data, $errors, 'Editar utilizador', '/utilizadores/editar', 'Guardar alterações');
            return;
        }
        $this->model->update($id, $data);
        if ($id === (int)Session::user()['id']) {
            $updated = $this->model->find($id);
            if ($updated) Session::login($updated);
        }
        (new AuditLog())->record((int) Session::user()['id'], 'UPDATE', 'utilizadores', $id, 'Utilizador atualizado.', $old, $this->safeData($data));
        Session::flash('sucesso', 'Utilizador atualizado com sucesso.');
        $this->redirect('/utilizadores');
    }

    public function delete(): void
    {
        $this->csrf('/utilizadores');
        $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
        if ($id === (int) Session::user()['id']) {
            Session::flash('erro', 'Não pode desativar a conta que está a utilizar.');
            $this->redirect('/utilizadores');
        }
        $old = $this->model->find($id);
        if (!$old) {
            Session::flash('erro', 'Utilizador não encontrado.');
            $this->redirect('/utilizadores');
        }
        if ($old['perfil'] === 'Administrador' && $this->model->activeAdminCount() <= 1) {
            Session::flash('erro', 'O sistema deve manter pelo menos um administrador ativo.');
            $this->redirect('/utilizadores');
        }
        $this->model->deactivate($id);
        (new AuditLog())->record((int) Session::user()['id'], 'DELETE', 'utilizadores', $id, 'Utilizador desativado.', $old, ['estado' => 'inativo']);
        Session::flash('sucesso', 'Utilizador desativado.');
        $this->redirect('/utilizadores');
    }

    private function data(): array
    {
        return [
            'perfil_id' => filter_var($_POST['perfil_id'] ?? 0, FILTER_VALIDATE_INT) ?: 0,
            'nome' => trim($_POST['nome'] ?? ''),
            'email' => mb_strtolower(trim($_POST['email'] ?? '')),
            'telefone' => trim($_POST['telefone'] ?? ''),
            'senha' => $_POST['senha'] ?? '',
            'estado' => $_POST['estado'] ?? 'ativo',
        ];
    }

    private function validate(array $data, bool $passwordRequired): array
    {
        $errors = [];
        $validProfiles = array_map('intval', array_column($this->model->profiles(), 'id'));
        if (!in_array((int) $data['perfil_id'], $validProfiles, true)) $errors['perfil_id'] = 'Selecione um perfil válido.';
        if (mb_strlen($data['nome']) < 3) $errors['nome'] = 'Informe o nome completo.';
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Informe um email válido.';
        if ($data['telefone'] !== '' && !preg_match('/^[0-9+()\s-]{7,25}$/', $data['telefone'])) $errors['telefone'] = 'Informe um telefone válido.';
        if ($passwordRequired || $data['senha'] !== '') {
            if (strlen($data['senha']) < 8 || !preg_match('/[A-Z]/', $data['senha']) || !preg_match('/[a-z]/', $data['senha']) || !preg_match('/\d/', $data['senha'])) {
                $errors['senha'] = 'Use 8 caracteres ou mais, com maiúscula, minúscula e número.';
            }
        }
        if (!in_array($data['estado'], ['ativo', 'inativo', 'bloqueado'], true)) $errors['estado'] = 'Estado inválido.';
        return $errors;
    }

    private function formError(array $data, array $errors, string $title, string $path, string $label): void
    {
        http_response_code(422);
        $this->view('utilizadores/form', [
            'title' => $title,
            'userData' => $data,
            'profiles' => $this->model->profiles(),
            'errors' => $errors,
            'formAction' => BASE_URL . $path,
            'submitLabel' => $label,
        ]);
    }

    private function csrf(string $path): void
    {
        if (!Session::validCsrf($_POST['csrf_token'] ?? null)) {
            Session::flash('erro', 'A sessão do formulário expirou.');
            $this->redirect($path);
        }
    }

    private function safeData(array $data): array
    {
        unset($data['senha']);
        return $data;
    }

    private function emptyData(): array
    {
        return ['perfil_id' => '', 'nome' => '', 'email' => '', 'telefone' => '', 'senha' => '', 'estado' => 'ativo'];
    }
}
