<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\OrderController;

// Rutas públicas (login)
Route::post('/v1/login', [AuthController::class, 'login']);

// Rutas protegidas para usuarios autenticados
Route::middleware('auth:sanctum')->group(function () {
    // Grupo de rutas para la versión 1 de la API
    Route::prefix('v1')->group(function () {
        // Rutas de autenticación
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);

        // Rutas para las órdenes del técnico
        Route::get('/orders', [OrderController::class, 'index']);
        Route::post('/orders/{orden}/accept', [OrderController::class, 'acceptOrder']);
        // Podrías añadir más rutas aquí, como para añadir comentarios, etc.

        Route::post('/v1/update-fcm-token', [AuthController::class, 'updateFcmToken']);
    });
});

Route::get('/v1/health', function () {
    return response()->json(['status' => 'ok']);
});