<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

/**
 * this route file is protected by the auth admin middleware in bootstrap/app.php
 * it contains the routes for the admin controllers
 */


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

// Start::Order ===================================================== //
Route::resource('orders', OrderController::class)->names('orders');
Route::put('orders/{order}/toggleActive/{state}', [OrderController::class, 'toggleActive'])
    ->where(['id' => '[0-9]+', 'state' => 'true|false'])->name('orders.toggleActive');
// End::Order ===================================================== //
