<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\UserAuthController;
use Illuminate\Support\Facades\Route;

/* ================================ Start:: Public Auth Routes ================================ */
Route::prefix('user')->group(function () {
    Route::post('login', [UserAuthController::class, 'login'])->name('auth.login');
    Route::post('register', [UserAuthController::class, 'register'])->name('auth.register');
 });
 Route::prefix('admin')->group(function () {
    Route::post('login', [AdminAuthController::class, 'login'])->name('admin.login');
 });
/* ================================ End:: Public Auth Routes ================================ */

/* ================================ Start:: Protected Auth Routes ================================ */
Route::middleware('auth:sanctum')->group(function () {
    // Routes for all authenticated users
    Route::post('user/logout', [UserAuthController::class, 'logout'])->name('auth.logout');
    Route::post('admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
});
/* ================================ End:: Protected Auth Routes ================================ */
