<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Models\Dashboard;
use App\Models\Relatorio;

final class DashboardController extends Controller
{
    public function index(): void
    {
        AuthMiddleware::handle();

        $model = new Dashboard();
        $report = new Relatorio();
        $this->view('dashboard/index', [
            'title' => 'Dashboard',
            'statistics' => $model->statistics(),
            'recentServices' => $model->recentServices(),
            'statusData' => $report->statusData(),
            'monthlyRevenue' => $report->monthlyRevenue(),
        ]);
    }
}
