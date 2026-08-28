<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Session;

final class AuthMiddleware
{
    public static function handle(): void
    {
        if (!Session::authenticated()) {
            Session::flash('erro', 'Inicie a sessão para continuar.');
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    public static function guest(): void
    {
        if (Session::authenticated()) {
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
    }
}
