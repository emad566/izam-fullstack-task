<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;






// Start::Category ===================================================== //
Route::resource('categories', CategoryController::class)->names('categories');
Route::put('categories/{category}/toggleActive/{state}', [CategoryController::class, 'toggleActive'])
    ->where(['id' => '[0-9]+', 'state' => 'true|false'])->name('categories.toggleActive');
// End::Category ===================================================== //

// Start::Product ===================================================== //
Route::resource('products', ProductController::class)->names('products');
Route::put('products/{product}/toggleActive/{state}', [ProductController::class, 'toggleActive'])
    ->where(['id' => '[0-9]+', 'state' => 'true|false'])->name('products.toggleActive');
// End::Product ===================================================== //
