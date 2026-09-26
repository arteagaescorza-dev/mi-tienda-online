<?php

use Illuminate\Support\Facades\Route;

Route::get('/products', [\App\Http\Controllers\Api\ProductController::class, 'index']);
Route::get('/products/{product}', [\App\Http\Controllers\Api\ProductController::class, 'show']);

Route::get('/cart', [\App\Http\Controllers\Api\CartController::class, 'show']);
Route::post('/cart/items', [\App\Http\Controllers\Api\CartController::class, 'addItem']);
Route::put('/cart/sync', [\App\Http\Controllers\Api\CartController::class, 'sync']);
Route::patch('/cart/items/{item}', [\App\Http\Controllers\Api\CartController::class, 'updateItem']);
Route::delete('/cart/items/{item}', [\App\Http\Controllers\Api\CartController::class, 'removeItem']);
