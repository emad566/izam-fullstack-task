<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

/**
 * this route file is protected by the auth user middleware in bootstrap/app.php
 * it contains the routes for the user controllers
 */

// Start::Order ===================================================== //
Route::resource('orders', OrderController::class)->only(['index', 'show']);
// End::Order ===================================================== //



