<?php

use App\Http\Controllers\EmpresaController;
use Illuminate\Support\Facades\Route;

Route::post('/empresas', [EmpresaController::class, 'store'])->name('api.empresas.store');
Route::get('/empresas', [EmpresaController::class, 'index'])->name('api.empresas.index');
Route::get('/empresas/{cnpj}', [EmpresaController::class, 'show'])->name('api.empresas.show');
