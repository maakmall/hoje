<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index']);
Route::get('/menus', [OrderController::class, 'index']);
Route::get('/checkout', [OrderController::class, 'checkout']);
