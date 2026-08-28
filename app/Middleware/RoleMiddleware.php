<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Session;

final class RoleMiddleware
{
    public static function allow(array $roles): void
    {
        AuthMiddleware::handle();

        if (!Session::hasRole($roles)) {
            Session::flash('erro', 'Não tem permissão para realizar esta operação.');
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
    }
}
