<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

/**
 * Those routes are public and not protected by any auth middleware in bootstrap/app.php
 */

// Start::Category ===================================================== //
Route::resource('categories', CategoryController::class)->only('index', 'show');
// End::Category ===================================================== //

// Start::Product ===================================================== //
Route::resource('products', ProductController::class)->only('index', 'show');
// End::Product ===================================================== //
