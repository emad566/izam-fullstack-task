<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/* ================================ Start:: Public Auth Routes ================================ */
Route::post('login', [AuthController::class, 'login'])->name('auth.login');
Route::post('register', [AuthController::class, 'register'])->name('auth.register');
/* ================================ End:: Public Auth Routes ================================ */

/* ================================ Start:: Protected Auth Routes ================================ */
Route::middleware('auth:sanctum')->group(function () {
    // Routes for all authenticated users
    Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');
});
/* ================================ End:: Protected Auth Routes ================================ */
