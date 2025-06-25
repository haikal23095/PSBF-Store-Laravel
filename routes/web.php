<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransaksiController;
use Illuminate\Support\Facades\Auth;

// --- Rute Autentikasi ---
Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');



Route::post('/midtrans-notification', [StoreController::class, 'handleMidtransNotification'])->name('midtrans.notification');

// --- Rute untuk Penjual ---
if (request()->getPort() == 8001) {
    Route::middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':penjual'])->group(function () {
        Route::get('/penjual/dashboard', [DashboardController::class, 'penjualDashboard'])->name('penjual.dashboard');

        Route::resource('/penjual/produk', ProductController::class)->names('penjual.produk');

        Route::get('/penjual/transaksi', [TransaksiController::class, 'indexPenjual'])->name('penjual.transaksi.index');
        Route::get('/penjual/transaksi/{transaksi}', [TransaksiController::class, 'showPenjual'])->name('penjual.transaksi.show');
        Route::patch('/penjual/transaksi/{transaksi}/update-status', [TransaksiController::class, 'updateStatus'])->name('penjual.transaksi.updateStatus');
        Route::patch('/transaksi/{transaksi}/update-status', [TransaksiController::class, 'updateStatus'])->name('transaksi.updateStatus');
    });
}



// --- Rute untuk Pembeli ---
if (request()->getPort() == 8000) {
    Route::middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':pembeli'])->group(function () {
        Route::get('/store', [StoreController::class, 'index'])->name('pembeli.store');


        Route::post('/cart/add/{product}', [StoreController::class, 'addToCart'])->name('pembeli.cart.add');
        Route::get('/cart', [StoreController::class, 'cart'])->name('pembeli.cart');
        Route::patch('/cart/update/{productId}', [StoreController::class, 'updateCart'])->name('pembeli.cart.update');
        Route::delete('/cart/remove/{productId}', [StoreController::class, 'removeCartItem'])->name('pembeli.cart.remove');

        // Rute Checkout
        Route::post('/checkout', [StoreController::class, 'checkout'])->name('pembeli.checkout');

        // Midtrans Payment Gateway View Route - Ini adalah rute GET yang akan menampilkan halaman pembayaran setelah POST checkout
        Route::get('/payment-page', [StoreController::class, 'showPaymentPage'])->name('pembeli.show_payment_page');



        // Rute Transaksi Pembeli
        Route::get('/transaksi', [TransaksiController::class, 'indexPembeli'])->name('pembeli.transaksi.index');
        Route::get('/transaksi/{transaksi}', [TransaksiController::class, 'showPembeli'])->name('pembeli.transaksi.show');
        Route::patch('/transaksi/{transaksi}/update-status-diterima', [TransaksiController::class, 'updateStatusDiterima'])->name('pembeli.transaksi.updateStatusDiterima');
        // Rute untuk pengecekan status
        Route::get('/transaksi/{transaksi}/check-status', [App\Http\Controllers\TransaksiController::class, 'checkStatus'])->name('pembeli.transaksi.checkStatus');
        Route::patch('/transaksi/{transaksi}/diterima', [TransaksiController::class, 'updateStatusDiterima'])->name('pembeli.transaksi.updateStatusDiterima');
    });
}


// Fallback: Redirect berdasarkan role
Route::get('/dashboard', function () {
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    return match (Auth::user()->roles) {
        'penjual' => redirect()->route('penjual.dashboard'),
        'pembeli' => redirect()->route('pembeli.store'),
        default   => redirect()->route('login'),
    };
})->name('dashboard');
