<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

// Start::Category ===================================================== //
Route::resource('categories', CategoryController::class)->only('index', 'show');
// End::Category ===================================================== //

// Start::Product ===================================================== //
Route::resource('products', ProductController::class)->only('index', 'show');
// End::Product ===================================================== //
