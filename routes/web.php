<?php

// =========================================================================
// 1. FILE: routes/web.php
// DESKRIPSI: Rute telah diperbarui untuk memanggil middleware secara langsung
// guna melakukan debugging pada error 'Target class does not exist'.
// =========================================================================

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\ProductController; // <-- TAMBAHKAN INI
// Tidak perlu mengimpor RoleMiddleware di sini jika menggunakan FQCN (Fully Qualified Class Name)

// --- Rute Autentikasi ---
Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


// --- Rute untuk Penjual ---
// Grup rute ini hanya bisa diakses oleh pengguna yang sudah login DAN memiliki peran 'penjual'.
// PERUBAHAN: Memanggil class middleware secara langsung, bukan via alias 'role'.
if (request()->getPort() == 8001) {
Route::middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':penjual'])->group(function () {
    Route::get('/penjual/dashboard', [DashboardController::class, 'penjualDashboard'])->name('penjual.dashboard');
    
    // Rute CRUD Produk
    // Ini akan secara otomatis membuat rute untuk index, create, store, show, edit, update, destroy
    Route::resource('/penjual/produk', ProductController::class)->names('penjual.produk');
    Route::get('/penjual/transaksi', [DashboardController::class, 'transaksiPenjual'])->name('penjual.transaksi.index');


});
};



// --- Rute untuk Pembeli ---
// Grup rute ini hanya bisa diakses oleh pengguna yang sudah login DAN memiliki peran 'pembeli'.
// PERUBAHAN: Memanggil class middleware secara langsung, bukan via alias 'role'.
if (request()->getPort() == 8000) {

Route::middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':pembeli'])->group(function () {
    Route::get('/store', [StoreController::class, 'index'])->name('pembeli.store');
    // Tambahkan rute pembeli lainnya di sini (e.g., keranjang, riwayat transaksi)
});
};

// Fallback jika pengguna mencoba mengakses halaman tanpa login
Route::get('/dashboard', function() {
    if (!auth()->check()) {
        return redirect()->route('login');
    }
    if (auth()->user()->roles === 'penjual') {
        return redirect()->route('penjual.dashboard');
    }
    if (auth()->user()->roles === 'pembeli') {
        return redirect()->route('pembeli.store');
    }
    // Default fallback
    return redirect()->route('login');
})->name('dashboard');