<?php

use App\Http\Controllers\AuthController;
<<<<<<< HEAD
=======
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SellerController;
>>>>>>> 62399c56dc0e9edfeac77b91d7f572045a1bdf2b
use Illuminate\Support\Facades\Route;

/* Auth session buatan sendiri */

Route::middleware('guest')->group(function () {
    Route::view('/masuk', 'auth.masuk', ['title' => 'Masuk'])->name('login');
    Route::view('/daftar', 'auth.daftar', ['title' => 'Daftar'])->name('register');

    Route::post('/masuk', [AuthController::class, 'login'])->name('login.store');
    Route::post('/daftar', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/keluar', [AuthController::class, 'logout'])->name('logout');
});

Route::view('/', 'beranda', [
    'title' => 'Beranda',
    'context' => 'Beranda pembeli • Fokus pada satu tindakan utama',
    'mode' => 'Mode Pembeli',
    'active' => 'beranda',
    'role' => 'pembeli',
])->name('beranda');

Route::view('/cari', 'cari', [
    'title' => 'Cari Produk',
    'mobileTitle' => 'Cari produk',
    'context' => 'Pencarian produk • Input suara dan teks',
    'mode' => 'Mode Pembeli',
    'active' => 'cari',
    'role' => 'pembeli',
    'produk' => array_slice(config('suaranniaga.produk'), 0, 3),
])->name('cari');

Route::get('/produk/{id}', function (int $id) {
    $produk = collect(config('suaranniaga.produk'))->firstWhere('id', $id);

    abort_if(! $produk, 404);

    return view('detail-produk', [
        'title' => 'Detail Produk',
        'context' => 'Detail produk • Informasi utama dan deskripsi audio',
        'mode' => 'Mode Pembeli',
        'active' => 'cari',
        'role' => 'pembeli',
        'produk' => $produk,
    ]);
})->name('produk.detail');

Route::view('/keranjang', 'keranjang', [
    'title' => 'Keranjang',
    'context' => 'Keranjang • Ringkasan barang sebelum checkout',
    'mode' => 'Mode Pembeli',
    'active' => 'keranjang',
    'role' => 'pembeli',
])->name('keranjang');

Route::view('/checkout', 'checkout', [
    'title' => 'Checkout',
    'context' => 'Checkout • Formulir sederhana dan terstruktur',
    'mode' => 'Mode Pembeli',
    'active' => 'keranjang',
    'role' => 'pembeli',
])->name('checkout');

Route::view('/pesanan', 'pesanan', [
    'title' => 'Pesanan Saya',
    'context' => 'Pesanan saya • Riwayat dan status pesanan',
    'mode' => 'Mode Pembeli',
    'active' => 'pesanan',
    'role' => 'pembeli',
    'pesanan' => config('suaranniaga.pesanan'),
])->name('pesanan');

Route::view('/pesanan/berhasil', 'pesanan-berhasil', [
    'title' => 'Pesanan Berhasil',
    'context' => 'Konfirmasi pesanan • Feedback jelas setelah aksi',
    'mode' => 'Mode Pembeli',
    'active' => 'pesanan',
    'role' => 'pembeli',
])->name('pesanan.sukses');

Route::view('/favorit', 'favorit', [
    'title' => 'Favorit',
    'context' => 'Favorit • Produk yang kamu simpan',
    'mode' => 'Mode Pembeli',
    'active' => 'favorit',
    'role' => 'pembeli',
])->name('favorit');

Route::view('/profil', 'profil', [
    'title' => 'Profil',
    'context' => 'Profil • Data akun dan preferensi',
    'mode' => 'Mode Pembeli',
    'active' => 'profil',
    'role' => 'pembeli',
])->name('profil');

Route::view('/pengaturan', 'pengaturan', [
    'title' => 'Pengaturan Aksesibilitas',
    'context' => 'Pengaturan aksesibilitas • Preferensi pengguna',
    'mode' => 'Mode Pembeli',
    'active' => 'pengaturan',
    'role' => 'pembeli',
])->name('pengaturan');

/* Seller */

Route::view('/penjual', 'penjual/dashboard', [
    'title' => 'Dashboard Penjual',
    'context' => 'Dashboard penjual • Ringkasan aktivitas toko',
    'mode' => 'Mode Penjual',
    'active' => 'ringkasan',
    'role' => 'penjual',
])->name('penjual.dashboard');

Route::view('/penjual/produk', 'penjual/produk', [
    'title' => 'Produk Saya',
    'context' => 'Produk saya • Daftar produk aktif',
    'mode' => 'Mode Penjual',
    'active' => 'produk',
    'role' => 'penjual',
])->name('penjual.produk');

Route::view('/penjual/produk/tambah', 'penjual/tambah-produk', [
    'title' => 'Tambah Produk',
    'context' => 'Tambah produk • Alur input suara bertahap',
    'mode' => 'Mode Penjual',
    'active' => 'tambah',
    'role' => 'penjual',
])->name('penjual.produk.tambah');

Route::view('/penjual/pesanan', 'penjual/pesanan', [
    'title' => 'Pesanan Masuk',
    'context' => 'Pesanan masuk • Antrean pesanan toko',
    'mode' => 'Mode Penjual',
    'active' => 'pesanan-masuk',
    'role' => 'penjual',
])->name('penjual.pesanan');

Route::view('/penjual/toko', 'penjual/toko', [
    'title' => 'Toko Saya',
    'context' => 'Toko saya • Profil toko dan pengiriman',
    'mode' => 'Mode Penjual',
    'active' => 'toko',
    'role' => 'penjual',
])->name('penjual.toko');
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
