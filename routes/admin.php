<?php

use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;






// Start::Category ===================================================== //
Route::resource('categories', CategoryController::class)->names('categories');
Route::put('categories/{category}/toggleActive/{state}', [CategoryController::class, 'toggleActive'])
    ->where(['id' => '[0-9]+', 'state' => 'true|false'])->name('categories.toggleActive');
// End::Category ===================================================== //
