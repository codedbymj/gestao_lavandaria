<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Middleware\RoleMiddleware;
use App\Models\AuditLog;

final class LogsController extends Controller
{
    public function __construct()
    {
        RoleMiddleware::allow(['Administrador']);
    }

    public function index(): void
    {
        $search = trim($_GET['pesquisa'] ?? '');
        $operation = $_GET['operacao'] ?? '';
        $from = $_GET['inicio'] ?? '';
        $to = $_GET['fim'] ?? '';
        $page = max(1, filter_var($_GET['pagina'] ?? 1, FILTER_VALIDATE_INT) ?: 1);
        $perPage = 20;
        $model = new AuditLog();
        $total = $model->countFiltered($search, $operation, $from, $to);
        $pages = max(1, (int)ceil($total / $perPage));
        $page = min($page, $pages);
        $this->view('logs/index', ['title' => 'Auditoria', 'logs' => $model->paginate($search, $operation, $from, $to, $page, $perPage), 'search' => $search, 'operation' => $operation, 'from' => $from, 'to' => $to, 'page' => $page, 'totalPages' => $pages, 'total' => $total]);
    }
}
