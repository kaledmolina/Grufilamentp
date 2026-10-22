<?php

namespace App\Http\Controllers;

use App\Services\DatabaseBackupService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    protected DatabaseBackupService $backupService;

    public function __construct(DatabaseBackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Descargar copia de seguridad de la base de datos (exclusivo para administradores).
     */
    public function download(Request $request): StreamedResponse
    {
        $user = auth()->user();
        if (!$user || !$user->hasAnyRole(['administrador', 'admin'])) {
            abort(403, 'No tienes permisos para descargar copias de seguridad.');
        }

        $compress = $request->boolean('compress', true);
        $excludeTemp = $request->boolean('exclude_temp', true);

        $excludeTables = [];
        if ($excludeTemp) {
            $excludeTables = ['sessions', 'cache', 'cache_locks', 'jobs', 'failed_jobs', 'job_batches'];
        }

        $extension = $compress ? 'sql.gz' : 'sql';
        $timestamp = now()->format('Y-m-d_H-i-s');
        $fileName = "backup_db_{$timestamp}.{$extension}";

        $headers = [
            'Content-Type' => $compress ? 'application/gzip' : 'application/sql',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($compress, $excludeTables) {
            $this->backupService->streamBackup($compress, $excludeTables);
        }, 200, $headers);
    }
}
