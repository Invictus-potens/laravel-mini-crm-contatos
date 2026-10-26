<?php

use App\Infraestrutura\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Route;

Route::get('/contacts', [ContactController::class, 'index']);
Route::post('/contacts', [ContactController::class, 'store']);
Route::get('/contacts/{id}', [ContactController::class, 'show'])->whereNumber('id');
Route::put('/contacts/{id}', [ContactController::class, 'update'])->whereNumber('id');
Route::delete('/contacts/{id}', [ContactController::class, 'destroy'])->whereNumber('id');
Route::post('/contacts/{id}/process-score', [ContactController::class, 'processScore'])->whereNumber('id');