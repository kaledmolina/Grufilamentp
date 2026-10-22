<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FotoController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\BackupController;
use App\Models\PreoperationalInspection;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/private-fotos/{ordenFoto}', [FotoController::class, 'show'])
    ->middleware('auth')
    ->name('fotos.show');
Route::get('/orden/{orden}/pdf', [PdfController::class, 'downloadOrdenPdf'])
    ->middleware('auth')
    ->name('orden.pdf.download');
Route::get('/inspections/{inspection}/pdf', [PdfController::class, 'downloadInspectionPdf'])
    ->middleware('auth')
    ->name('inspections.pdf.download');

// Descarga de copia de seguridad de la base de datos (solo administrador)
Route::get('/admin/backup/download', [BackupController::class, 'download'])
    ->middleware('auth')
    ->name('admin.backup.download');

// Prevenir error 405 si un navegador intenta hacer GET a livewire/update
Route::get('/livewire/update', function () {
    return redirect()->to('/admin');
});    