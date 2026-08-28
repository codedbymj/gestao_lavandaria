<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Middleware\RoleMiddleware;
use App\Models\Danos;

final class DanosController extends Controller
{
    private Danos $model;
    public function __construct()
    {
        RoleMiddleware::allow(['Administrador', 'Gestor', 'Atendente']);
        $this->model = new Danos();
    }

    public function index(): void
    {
        $search = trim($_GET['pesquisa'] ?? '');
        $status = $_GET['estado'] ?? '';
        $from = $_GET['inicio'] ?? '';
        $to = $_GET['fim'] ?? '';
        $page = max(1, filter_var($_GET['pagina'] ?? 1, FILTER_VALIDATE_INT) ?: 1);
        $perPage = 10;
        $total = $this->model->countFiltered($search, $status, $from, $to);
        $pages = max(1, (int)ceil($total / $perPage));
        $page = min($page, $pages);
        $this->view('danos/index', [
            'title' => 'Danos',
            'services' => $this->model->paginate($search, $status, $from, $to, $page, $perPage),
            'search' => $search,
            'status' => $status,
            'from' => $from,
            'to' => $to,
            'page' => $page,
            'totalPages' => $pages,
            'total' => $total,
        ]);
    }
}