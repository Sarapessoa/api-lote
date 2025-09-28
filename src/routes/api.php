<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;

Route::apiResource('clientes', ClienteController::class)
          ->missing(fn() => response()->json(['status' => 'erro', 'message' => 'Cliente não encontrado'], 404));