<?php

declare(strict_types=1);

namespace App\Core;

final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_name('lavandaria_session');
        session_start();

        if (
            isset($_SESSION['ultima_atividade'])
            && time() - (int) $_SESSION['ultima_atividade'] > 1800
        ) {
            $_SESSION = [];
            session_regenerate_id(true);
            $_SESSION['flash']['erro'] = 'A sessão expirou por inatividade.';
        }

        $_SESSION['ultima_atividade'] = time();
    }

    public static function user(): ?array
    {
        return $_SESSION['utilizador'] ?? null;
    }

    public static function authenticated(): bool
    {
        return isset($_SESSION['utilizador']['id']);
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['utilizador'] = [
            'id' => (int) $user['id'],
            'nome' => $user['nome'],
            'email' => $user['email'],
            'perfil' => $user['perfil'],
            'perfil_id' => (int) $user['perfil_id'],
        ];
    }

    public static function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path']);
        }

        session_destroy();
    }

    public static function hasRole(array $roles): bool
    {
        $role = self::user()['perfil'] ?? null;
        return $role !== null && in_array($role, $roles, true);
    }

    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    public static function validCsrf(?string $token): bool
    {
        return is_string($token)
            && isset($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function flash(string $key, ?string $message = null): ?string
    {
        if ($message !== null) {
            $_SESSION['flash'][$key] = $message;
            return null;
        }

        $value = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $value;
    }
}
