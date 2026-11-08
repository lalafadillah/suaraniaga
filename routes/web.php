<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SellerController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/products/{product}', [CatalogController::class, 'show'])->name('products.show');

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::post('/products/{product}/order', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}/konfirmasi', [OrderController::class, 'confirmation'])->name('orders.confirmation');

    Route::middleware('role:seller')->prefix('seller')->name('seller.')->group(function () {
        Route::get('/dashboard', [SellerController::class, 'dashboard'])->name('dashboard');
        Route::get('/products/create', [SellerController::class, 'createProduct'])->name('products.create');
        Route::post('/products', [SellerController::class, 'storeProduct'])->name('products.store');
        Route::patch('/orders/{order}/status', [SellerController::class, 'updateOrderStatus'])->name('orders.status');
    });
});
