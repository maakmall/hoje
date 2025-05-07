<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReservationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index']);
Route::get('/menus', [OrderController::class, 'index']);
Route::get('/checkout', [OrderController::class, 'checkout']);
Route::post('/orders', [OrderController::class, 'store']);
Route::post('/orders/upload-proof', [OrderController::class, 'uploadProof']);
Route::get('/reservations', [ReservationController::class, 'index']);
Route::post('/reservations', [ReservationController::class, 'store']);