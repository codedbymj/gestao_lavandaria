<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Middleware\AuthMiddleware;
use App\Models\AuditLog;
use App\Models\User;

final class AuthController extends Controller
{
    public function showLogin(): void
    {
        AuthMiddleware::guest();

        $userModel = new User();
        if ($userModel->count() === 0) {
            $this->redirect('/configurar-administrador');
        }

        $this->view('auth/login', ['title' => 'Iniciar sessão']);
    }

    public function login(): void
    {
        AuthMiddleware::guest();

        if (!Session::validCsrf($_POST['csrf_token'] ?? null)) {
            Session::flash('erro', 'A sessão do formulário expirou. Tente novamente.');
            $this->redirect('/login');
        }

        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $password = $_POST['senha'] ?? '';

        if (!$email || $password === '') {
            Session::flash('erro', 'Preencha corretamente o email e a palavra-passe.');
            $this->redirect('/login');
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['senha'])) {
            if ($user) {
                $userModel->registerFailedAttempt((int) $user['id']);
            }

            Session::flash('erro', 'Email ou palavra-passe incorretos.');
            $this->redirect('/login');
        }

        if ($user['estado'] !== 'ativo') {
            Session::flash('erro', 'A conta está inativa ou bloqueada.');
            $this->redirect('/login');
        }

        $userModel->registerSuccessfulLogin((int) $user['id']);
        Session::login($user);

        (new AuditLog())->record(
            (int) $user['id'],
            'LOGIN',
            'utilizadores',
            (int) $user['id'],
            'Início de sessão realizado com sucesso.'
        );

        $this->redirect('/dashboard');
    }

    public function showSetup(): void
    {
        AuthMiddleware::guest();

        if ((new User())->count() > 0) {
            $this->redirect('/login');
        }

        $this->view('auth/setup_admin', ['title' => 'Configurar administrador']);
    }

    public function setup(): void
    {
        AuthMiddleware::guest();

        if (!Session::validCsrf($_POST['csrf_token'] ?? null)) {
            Session::flash('erro', 'A sessão do formulário expirou. Tente novamente.');
            $this->redirect('/configurar-administrador');
        }

        $name = trim($_POST['nome'] ?? '');
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $password = $_POST['senha'] ?? '';
        $confirmation = $_POST['confirmar_senha'] ?? '';

        if (mb_strlen($name) < 3 || !$email) {
            Session::flash('erro', 'Informe um nome e um email válidos.');
            $this->redirect('/configurar-administrador');
        }

        if (
            strlen($password) < 8 || !preg_match('/[A-Z]/', $password)
            || !preg_match('/[a-z]/', $password) || !preg_match('/\d/', $password)
        ) {
            Session::flash(
                'erro',
                'A palavra-passe deve ter pelo menos 8 caracteres, uma maiúscula, uma minúscula e um número.'
            );
            $this->redirect('/configurar-administrador');
        }

        if ($password !== $confirmation) {
            Session::flash('erro', 'As palavras-passe não são iguais.');
            $this->redirect('/configurar-administrador');
        }

        try {
            $userModel = new User();
            $id = $userModel->createFirstAdmin($name, $email, $password);
            (new AuditLog())->record(
                $id,
                'CREATE',
                'utilizadores',
                $id,
                'Administrador inicial criado.',
                null,
                ['nome' => $name, 'email' => $email, 'perfil' => 'Administrador']
            );
            Session::flash('sucesso', 'Administrador criado. Já pode iniciar a sessão.');
            $this->redirect('/login');
        } catch (\Throwable $exception) {
            Session::flash('erro', $exception->getMessage());
            $this->redirect('/configurar-administrador');
        }
    }

    public function logout(): void
    {
        AuthMiddleware::handle();

        if (!Session::validCsrf($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            exit('Pedido inválido.');
        }

        $user = Session::user();
        (new AuditLog())->record(
            (int) $user['id'],
            'LOGOUT',
            'utilizadores',
            (int) $user['id'],
            'Sessão terminada.'
        );
        Session::logout();

        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}
