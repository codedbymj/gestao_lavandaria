<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function view(string $view, array $data = []): void
    {
        $viewFile = dirname(__DIR__) . '/Views/' . $view . '.php';

        if (!is_file($viewFile)) {
            http_response_code(500);
            exit('A view solicitada não foi encontrada.');
        }

        extract($data, EXTR_SKIP);

        require dirname(__DIR__) . '/Views/layouts/header.php';
        require $viewFile;
        require dirname(__DIR__) . '/Views/layouts/footer.php';
    }

    protected function redirect(string $path): never
    {
        header('Location: ' . BASE_URL . $path);
        exit;
    }
}
