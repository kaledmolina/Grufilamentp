<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FotoController;

Route::get('/', function () {
    return view('welcome');
});
/*
Route::get('/private-fotos/{ordenFoto}', [FotoController::class, 'show'])
    ->middleware('auth')
    ->name('fotos.show');
    */