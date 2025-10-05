<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\LoteController;

Route::apiResource('clientes', ClienteController::class)
      ->missing(fn() => response()->json(['message' => 'Cliente não encontrado'], 404));

Route::apiResource('lotes', LoteController::class)
      ->missing(fn() => response()->json(['message' => 'Lote não encontrado'], 404));