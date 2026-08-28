<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;
use RuntimeException;

final class Backup
{
    private PDO $db;
    private string $directory;

    public function __construct()
    {
        $this->db = Database::connection();
        $this->directory = dirname(__DIR__, 2) . '/storage/backups';
        if (!is_dir($this->directory) && !mkdir($this->directory, 0775, true) && !is_dir($this->directory)) throw new RuntimeException('Não foi possível criar a pasta de backups.');
    }

    public function create(): string
    {
        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $path = $this->directory . '/' . $filename;
        $handle = fopen($path, 'wb');
        if (!$handle) throw new RuntimeException('Não foi possível criar o backup.');
        fwrite($handle, "-- Backup " . APP_NAME . "\n-- Gerado em " . date('Y-m-d H:i:s') . "\nSET FOREIGN_KEY_CHECKS=0;\n\n");
        $tables = $this->db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) continue;
            $create = $this->db->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_NUM);
            fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n" . $create[1] . ";\n\n");
            $rows = $this->db->query("SELECT * FROM `{$table}`");
            while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
                $columns = array_map(fn($column) => "`{$column}`", array_keys($row));
                $values = array_map(fn($value) => $value === null ? 'NULL' : $this->db->quote((string)$value), array_values($row));
                fwrite($handle, "INSERT INTO `{$table}` (" . implode(',', $columns) . ") VALUES (" . implode(',', $values) . ");\n");
            }
            fwrite($handle, "\n");
        }
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);
        return $filename;
    }

    public function files(): array
    {
        $result = [];
        foreach (glob($this->directory . '/backup_*.sql') ?: [] as $path) {
            $result[] = ['nome' => basename($path), 'tamanho' => filesize($path), 'data' => filemtime($path)];
        }
        usort($result, fn($a, $b) => $b['data'] <=> $a['data']);
        return $result;
    }

    public function path(string $filename): string
    {
        if (!preg_match('/^backup_[0-9-]+_[0-9-]+\.sql$/', $filename)) throw new RuntimeException('Nome de backup inválido.');
        $path = $this->directory . '/' . $filename;
        if (!is_file($path)) throw new RuntimeException('Backup não encontrado.');
        return $path;
    }

    public function delete(string $filename): bool
    {
        return unlink($this->path($filename));
    }
}
