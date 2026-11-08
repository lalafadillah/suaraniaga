<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AkunController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\HalamanController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SellerController;
use Illuminate\Support\Facades\Route;

/* Publik: katalog dan pencarian */

Route::get('/', [CatalogController::class, 'index'])->name('beranda');
Route::get('/cari', [CatalogController::class, 'search'])->name('cari');
Route::get('/produk/{produk}', [CatalogController::class, 'show'])->name('produk.detail');
Route::get('/products/{produk}', [CatalogController::class, 'show'])->name('products.show');

/* Auth session */

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');

    // Alias path yang sama dengan halaman frontend (masuk, daftar).
    Route::get('/masuk', [AuthController::class, 'showLogin'])->name('login.masuk');
    Route::post('/masuk', [AuthController::class, 'login'])->name('login.masuk.store');
    Route::get('/daftar', [AuthController::class, 'showRegister'])->name('register.daftar');
    Route::post('/daftar', [AuthController::class, 'register'])->name('register.daftar.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/keluar', [AuthController::class, 'logout'])->name('logout.keluar');

    Route::get('/profil', [AkunController::class, 'index'])->name('profil');
    Route::get('/akun', [AkunController::class, 'index'])->name('akun');
    Route::patch('/akun', [AkunController::class, 'update'])->name('akun.update');
    Route::patch('/akun/sandi', [AkunController::class, 'updatePassword'])->name('akun.sandi');
    Route::delete('/akun', [AkunController::class, 'destroy'])->name('akun.hapus');
    Route::get('/pengaturan', [HalamanController::class, 'pengaturan'])->name('pengaturan');

    Route::middleware('role:buyer')->group(function () {
        Route::get('/keranjang', [HalamanController::class, 'keranjang'])->name('keranjang');
        Route::get('/checkout', [HalamanController::class, 'checkout'])->name('checkout');
        Route::get('/pesanan', [HalamanController::class, 'pesanan'])->name('pesanan');
        Route::get('/pesanan/berhasil', [HalamanController::class, 'pesananBerhasil'])->name('pesanan.sukses');
        Route::get('/favorit', [HalamanController::class, 'favorit'])->name('favorit');

        Route::post('/products/{produk}/order', [OrderController::class, 'store'])->name('orders.store');
        Route::post('/produk/{produk}/order', [OrderController::class, 'store'])->name('products.order');
        Route::get('/orders/{order}/konfirmasi', [OrderController::class, 'confirmation'])->name('orders.confirmation');
    });

    Route::middleware('role:seller')->group(function () {
        Route::get('/penjual', [SellerController::class, 'dashboard'])->name('penjual.dashboard');
        Route::get('/penjual/produk', [SellerController::class, 'produk'])->name('penjual.produk');
        Route::get('/penjual/produk/tambah', [SellerController::class, 'createProduct'])->name('penjual.produk.tambah');
        Route::post('/penjual/produk', [SellerController::class, 'storeProduct'])->name('penjual.produk.simpan');
        Route::get('/penjual/pesanan', [SellerController::class, 'pesanan'])->name('penjual.pesanan');
        Route::get('/penjual/toko', [SellerController::class, 'toko'])->name('penjual.toko');
        Route::patch('/penjual/pesanan/{order}/status', [SellerController::class, 'updateOrderStatus'])->name('penjual.pesanan.status');

        Route::get('/seller/dashboard', [SellerController::class, 'dashboard'])->name('seller.dashboard');
        Route::get('/seller/products', [SellerController::class, 'produk'])->name('seller.products.index');
        Route::get('/seller/products/create', [SellerController::class, 'createProduct'])->name('seller.products.create');
        Route::post('/seller/products', [SellerController::class, 'storeProduct'])->name('seller.products.store');
        Route::get('/seller/orders', [SellerController::class, 'pesanan'])->name('seller.orders.index');
        Route::patch('/seller/orders/{order}/status', [SellerController::class, 'updateOrderStatus'])->name('seller.orders.status');
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
        Route::patch('/admin/users/{user}/status', [AdminController::class, 'updateUserStatus'])->name('admin.users.status');
        Route::get('/admin/products', [AdminController::class, 'products'])->name('admin.products');
        Route::get('/admin/orders', [AdminController::class, 'orders'])->name('admin.orders');
    });
});
