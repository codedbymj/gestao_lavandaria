<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Middleware\RoleMiddleware;
use App\Models\AuditLog;
use App\Models\Backup;

final class BackupsController extends Controller
{
    private Backup $model;
    public function __construct()
    {
        RoleMiddleware::allow(['Administrador']);
        $this->model = new Backup();
    }

    public function index(): void
    {
        $this->view('backups/index', ['title' => 'Backups', 'files' => $this->model->files()]);
    }

    public function create(): void
    {
        $this->csrf();
        try {
            $name = $this->model->create();
            (new AuditLog())->record((int)Session::user()['id'], 'BACKUP', 'base_de_dados', null, 'Backup criado: ' . $name);
            Session::flash('sucesso', 'Backup criado com sucesso.');
        } catch (\Throwable $e) {
            Session::flash('erro', $e->getMessage());
        }
        $this->redirect('/backups');
    }

    public function download(): never
    {
        try {
            $name = basename($_GET['ficheiro'] ?? '');
            $path = $this->model->path($name);
            header('Content-Type: application/sql');
            header('Content-Disposition: attachment; filename="' . $name . '"');
            header('Content-Length: ' . filesize($path));
            readfile($path);
            exit;
        } catch (\Throwable $e) {
            Session::flash('erro', $e->getMessage());
            $this->redirect('/backups');
        }
    }

    public function delete(): void
    {
        $this->csrf();
        $name = basename($_POST['ficheiro'] ?? '');
        try {
            $this->model->delete($name);
            (new AuditLog())->record((int)Session::user()['id'], 'DELETE', 'backups', null, 'Backup eliminado: ' . $name);
            Session::flash('sucesso', 'Backup eliminado.');
        } catch (\Throwable $e) {
            Session::flash('erro', $e->getMessage());
        }
        $this->redirect('/backups');
    }

    private function csrf(): void
    {
        if (!Session::validCsrf($_POST['csrf_token'] ?? null)) {
            Session::flash('erro', 'A sessão do formulário expirou.');
            $this->redirect('/backups');
        }
    }
}
