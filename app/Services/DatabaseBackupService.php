<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use PDO;

class DatabaseBackupService
{
    /**
     * Obtener estadísticas generales de la base de datos.
     */
    public function getDatabaseStats(): array
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();
        $databaseName = $connection->getDatabaseName();

        $tables = [];
        $totalSizeMb = 0;

        if ($driver === 'mysql' || $driver === 'mariadb') {
            try {
                $results = DB::select('
                    SELECT 
                        table_name AS name,
                        table_rows AS `rows`,
                        round(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
                    FROM information_schema.TABLES
                    WHERE table_schema = ?
                    ORDER BY (data_length + index_length) DESC
                ', [$databaseName]);

                foreach ($results as $row) {
                    $tables[] = [
                        'name' => $row->name,
                        'rows' => (int) ($row->rows ?? 0),
                        'size_mb' => (float) ($row->size_mb ?? 0),
                    ];
                    $totalSizeMb += (float) ($row->size_mb ?? 0);
                }
            } catch (\Throwable $e) {
                // Fallback si no hay permisos sobre information_schema
                $tables = $this->getTableNamesSimple($driver);
            }
        } elseif ($driver === 'sqlite') {
            $sqliteTables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            foreach ($sqliteTables as $row) {
                $tableName = $row->name;
                $count = DB::table($tableName)->count();
                $tables[] = [
                    'name' => $tableName,
                    'rows' => $count,
                    'size_mb' => 0,
                ];
            }
            if (file_exists($databaseName)) {
                $totalSizeMb = round(filesize($databaseName) / 1024 / 1024, 2);
            }
        } else {
            $tables = $this->getTableNamesSimple($driver);
        }

        return [
            'driver' => strtoupper($driver),
            'database' => $databaseName,
            'table_count' => count($tables),
            'total_size_mb' => round($totalSizeMb, 2),
            'tables' => $tables,
        ];
    }

    /**
     * Obtener lista simple de nombres de tablas.
     */
    public function getTableNames(): array
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            return array_map(fn($t) => $t->name, $tables);
        }

        $results = DB::select('SHOW TABLES');
        $keyName = 'Tables_in_' . $connection->getDatabaseName();

        return array_map(function ($row) use ($keyName) {
            $array = (array) $row;
            return $array[$keyName] ?? reset($array);
        }, $results);
    }

    private function getTableNamesSimple(string $driver): array
    {
        $names = $this->getTableNames();
        return array_map(fn($name) => [
            'name' => $name,
            'rows' => 0,
            'size_mb' => 0,
        ], $names);
    }

    /**
     * Generar y emitir el volcado SQL en streaming.
     */
    public function streamBackup(bool $compress = false, array $excludeDataTables = []): void
    {
        // Aumentar límites temporales de ejecución y memoria para la descarga
        @ini_set('memory_limit', '512M');
        @set_time_limit(600);

        $pdo = DB::connection()->getPdo();
        $driver = DB::connection()->getDriverName();
        $dbName = DB::connection()->getDatabaseName();

        // Si se solicita compresión, abrimos un filtro de compresión gzip en la salida
        $out = fopen('php://output', 'w');
        if ($compress && function_exists('gzencode')) {
            // Activar compresión zlib en el buffer de salida o filtro
            stream_filter_append($out, 'zlib.deflate', STREAM_FILTER_WRITE, ['window' => 15 | 16]); // gzip format
        }

        $write = function (string $text) use ($out) {
            fwrite($out, $text);
        };

        // Encabezado
        $timestamp = now()->toDateTimeString();
        $write("-- ==========================================================\n");
        $write("-- Copia de Seguridad de Base de Datos - Grufilamentp\n");
        $write("-- Fecha y Hora: {$timestamp}\n");
        $write("-- Motor: {$driver}\n");
        $write("-- Base de Datos: {$dbName}\n");
        $write("-- ==========================================================\n\n");

        if ($driver === 'mysql' || $driver === 'mariadb') {
            $write("SET FOREIGN_KEY_CHECKS = 0;\n");
            $write("SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
            $write("SET NAMES utf8mb4;\n");
            $write("SET time_zone = \"+00:00\";\n\n");

            $tables = $this->getTableNames();

            foreach ($tables as $table) {
                $write("-- --------------------------------------------------------\n");
                $write("-- Estructura de tabla para `{$table}`\n");
                $write("-- --------------------------------------------------------\n");
                $write("DROP TABLE IF EXISTS `{$table}`;\n");

                $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC);
                $createSql = $createStmt['Create Table'] ?? ($createStmt['create table'] ?? null);
                if ($createSql) {
                    $write($createSql . ";\n\n");
                }

                // Omitir datos si la tabla está excluida
                if (in_array($table, $excludeDataTables, true)) {
                    $write("-- (Datos de `{$table}` omitidos por configuración)\n\n");
                    continue;
                }

                // Volcar datos por lotes
                $write("-- Volcado de datos para la tabla `{$table}`\n");
                $this->dumpTableDataMysql($pdo, $table, $write);
                $write("\n");
            }

            $write("SET FOREIGN_KEY_CHECKS = 1;\n");
            $write("-- Fin de copia de seguridad\n");
        } elseif ($driver === 'sqlite') {
            $write("PRAGMA foreign_keys = OFF;\nBEGIN TRANSACTION;\n\n");

            $tables = DB::select("SELECT name, sql FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            foreach ($tables as $tableRow) {
                $table = $tableRow->name;
                $write("DROP TABLE IF EXISTS \"{$table}\";\n");
                $write($tableRow->sql . ";\n");

                if (!in_array($table, $excludeDataTables, true)) {
                    $this->dumpTableDataSqlite($pdo, $table, $write);
                }
                $write("\n");
            }

            $write("COMMIT;\nPRAGMA foreign_keys = ON;\n");
        }

        fclose($out);
    }

    private function dumpTableDataMysql(PDO $pdo, string $table, callable $write): void
    {
        $stmt = $pdo->query("SELECT * FROM `{$table}`");
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $batch = [];
        $batchSize = 200;

        while ($row = $stmt->fetch()) {
            $escapedValues = array_map(function ($val) use ($pdo) {
                if ($val === null) {
                    return 'NULL';
                }
                if (is_int($val) || is_float($val)) {
                    return $val;
                }
                return $pdo->quote($val);
            }, $row);

            $batch[] = '(' . implode(', ', $escapedValues) . ')';

            if (count($batch) >= $batchSize) {
                $write("INSERT INTO `{$table}` VALUES \n" . implode(",\n", $batch) . ";\n");
                $batch = [];
            }
        }

        if (!empty($batch)) {
            $write("INSERT INTO `{$table}` VALUES \n" . implode(",\n", $batch) . ";\n");
        }
    }

    private function dumpTableDataSqlite(PDO $pdo, string $table, callable $write): void
    {
        $stmt = $pdo->query("SELECT * FROM \"{$table}\"");
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $batch = [];
        $batchSize = 100;

        while ($row = $stmt->fetch()) {
            $escapedValues = array_map(function ($val) use ($pdo) {
                if ($val === null) {
                    return 'NULL';
                }
                if (is_int($val) || is_float($val)) {
                    return $val;
                }
                return $pdo->quote($val);
            }, $row);

            $batch[] = '(' . implode(', ', $escapedValues) . ')';

            if (count($batch) >= $batchSize) {
                $write("INSERT INTO \"{$table}\" VALUES \n" . implode(",\n", $batch) . ";\n");
                $batch = [];
            }
        }

        if (!empty($batch)) {
            $write("INSERT INTO \"{$table}\" VALUES \n" . implode(",\n", $batch) . ";\n");
        }
    }
}
