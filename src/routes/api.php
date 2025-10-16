<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\LoteController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\AuthController;

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/refresh', [AuthController::class, 'refresh']);

Route::middleware('auth:sanctum')->group(function () {
      Route::post('/auth/logout', [AuthController::class, 'logout']);

      Route::apiResource('clientes', ClienteController::class)
            ->missing(fn() => response()->json(['message' => 'Cliente não encontrado'], 404));

      Route::apiResource('usuarios', UsuarioController::class)
            ->missing(fn() => response()->json(['message' => 'Usuário não encontrado'], 404));

      Route::apiResource('lotes', LoteController::class)
            ->missing(fn() => response()->json(['message' => 'Lote não encontrado'], 404));
});
