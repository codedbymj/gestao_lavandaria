<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];

    public function get(string $path, array $action): void
    {
        $this->add('GET', $path, $action);
    }

    public function post(string $path, array $action): void
    {
        $this->add('POST', $path, $action);
    }

    private function add(string $method, string $path, array $action): void
    {
        $this->routes[$method][$this->normalise($path)] = $action;
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $scriptDirectory = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');

        if ($scriptDirectory !== '' && str_starts_with($path, $scriptDirectory)) {
            $path = substr($path, strlen($scriptDirectory)) ?: '/';
        }

        $path = $this->normalise($path);
        $action = $this->routes[$method][$path] ?? null;

        if ($action === null) {
            http_response_code(404);
            require dirname(__DIR__) . '/Views/layouts/header.php';
            require dirname(__DIR__) . '/Views/errors/404.php';
            require dirname(__DIR__) . '/Views/layouts/footer.php';
            return;
        }

        [$controllerClass, $controllerMethod] = $action;
        $controller = new $controllerClass();
        $controller->{$controllerMethod}();
    }

    private function normalise(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '//' ? '/' : $path;
    }
}
