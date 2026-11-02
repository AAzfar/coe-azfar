<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::apiResource('products', ProductController::class);
Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
Route::apiResource('orders', OrderController::class)->only(['index', 'store', 'show']);
