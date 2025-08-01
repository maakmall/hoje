<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReservationController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index']);
Route::get('/menus', [OrderController::class, 'index']);
Route::get('/checkout', [OrderController::class, 'checkout']);
Route::post('/orders', [OrderController::class, 'store']);
Route::post('/payment', [OrderController::class, 'payment'])->withoutMiddleware(VerifyCsrfToken::class);
Route::post('/orders/upload-proof', [OrderController::class, 'uploadProof']);
Route::get('/reservations', [ReservationController::class, 'index']);
Route::post('/reservations', [ReservationController::class, 'store']);

Route::get('/optimize', function () {
    Artisan::call('optimize');
    Artisan::call('filament:optimize');
    
    return 'Optimized!';
});