<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'API de consulta de empresas',
        'endpoints' => [
            'GET /docs',
            'POST /api/empresas',
            'GET /api/empresas',
            'GET /api/empresas/{cnpj}',
        ],
    ]);
})->name('home');

Route::get('/docs', function () {
    return response()->file(public_path('swagger.html'));
})->name('docs');
