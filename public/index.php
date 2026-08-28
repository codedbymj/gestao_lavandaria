<?php

declare(strict_types=1);

require dirname(__DIR__) . '/config/config.php';

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = dirname(__DIR__) . '/app/' . str_replace('\\', '/', $relativeClass) . '.php';

    if (is_file($file)) {
        require $file;
    }
});

use App\Controllers\AuthController;
use App\Controllers\BackupsController;
use App\Controllers\CatalogoController;
use App\Controllers\ClientesController;
use App\Controllers\DashboardController;
use App\Controllers\FinanceiroController;
use App\Controllers\LogsController;
use App\Controllers\RelatoriosController;
use App\Controllers\ServicosController;
use App\Controllers\UtilizadoresController;
use App\Core\Router;
use App\Core\Session;

Session::start();

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

$router = new Router();

$router->get('/', [DashboardController::class, 'index']);
$router->get('/dashboard', [DashboardController::class, 'index']);

$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

$router->get('/configurar-administrador', [AuthController::class, 'showSetup']);
$router->post('/configurar-administrador', [AuthController::class, 'setup']);

$router->get('/clientes', [ClientesController::class, 'index']);
$router->get('/clientes/novo', [ClientesController::class, 'create']);
$router->post('/clientes/novo', [ClientesController::class, 'store']);
$router->get('/clientes/editar', [ClientesController::class, 'edit']);
$router->post('/clientes/editar', [ClientesController::class, 'update']);
$router->post('/clientes/eliminar', [ClientesController::class, 'delete']);

$router->get('/utilizadores', [UtilizadoresController::class, 'index']);
$router->get('/utilizadores/novo', [UtilizadoresController::class, 'create']);
$router->post('/utilizadores/novo', [UtilizadoresController::class, 'store']);
$router->get('/utilizadores/editar', [UtilizadoresController::class, 'edit']);
$router->post('/utilizadores/editar', [UtilizadoresController::class, 'update']);
$router->post('/utilizadores/eliminar', [UtilizadoresController::class, 'delete']);

$router->get('/catalogo', [CatalogoController::class, 'index']);
$router->post('/catalogo/peca', [CatalogoController::class, 'savePiece']);
$router->post('/catalogo/tipo-servico', [CatalogoController::class, 'saveServiceType']);
$router->post('/catalogo/preco', [CatalogoController::class, 'savePrice']);
$router->post('/catalogo/desativar', [CatalogoController::class, 'deactivate']);

$router->get('/servicos', [ServicosController::class, 'index']);
$router->get('/servicos/novo', [ServicosController::class, 'create']);
$router->post('/servicos/novo', [ServicosController::class, 'store']);
$router->get('/servicos/ver', [ServicosController::class, 'show']);
$router->post('/servicos/editar', [ServicosController::class, 'update']);
$router->post('/servicos/estado', [ServicosController::class, 'updateStatus']);

$router->post('/pagamentos', [FinanceiroController::class, 'pay']);
$router->get('/pagamentos/recibo', [FinanceiroController::class, 'receipt']);
$router->post('/pagamentos/anular', [FinanceiroController::class, 'annul']);
$router->post('/entregas', [FinanceiroController::class, 'deliver']);

$router->get('/relatorios', [RelatoriosController::class, 'index']);
$router->get('/relatorios/pdf', [RelatoriosController::class, 'pdf']);
$router->get('/logs', [LogsController::class, 'index']);

$router->get('/backups', [BackupsController::class, 'index']);
$router->post('/backups/criar', [BackupsController::class, 'create']);
$router->get('/backups/download', [BackupsController::class, 'download']);
$router->post('/backups/eliminar', [BackupsController::class, 'delete']);

try {
    $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
} catch (Throwable $exception) {
    http_response_code(500);
    $errorMessage = $exception->getMessage();
    require dirname(__DIR__) . '/app/Views/errors/500.php';
}
