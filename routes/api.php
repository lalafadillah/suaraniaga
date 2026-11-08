<?php

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SellerOrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/products', [ProductController::class, 'index'])->name('api.products.index');

    Route::middleware('role:seller')->group(function () {
        Route::get('/seller/orders/new', [SellerOrderController::class, 'new'])->name('api.seller.orders.new');
    });
});
