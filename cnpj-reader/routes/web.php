<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'API de consulta de empresas',
        'endpoints' => [
            'POST /api/empresas',
            'GET /api/empresas',
            'GET /api/empresas/{cnpj}',
        ],
    ]);
})->name('home');
