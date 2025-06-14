<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransaksiController;

// --- Rute Autentikasi ---
Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


// --- Rute untuk Penjual ---
if (request()->getPort() == 8001) {
    Route::middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':penjual'])->group(function () {
        Route::get('/penjual/dashboard', [DashboardController::class, 'penjualDashboard'])->name('penjual.dashboard');

        Route::resource('/penjual/produk', ProductController::class)->names('penjual.produk');

        Route::get('/penjual/transaksi', [TransaksiController::class, 'indexPenjual'])->name('penjual.transaksi.index');
        Route::get('/penjual/transaksi/{transaksi}', [TransaksiController::class, 'showPenjual'])->name('penjual.transaksi.show');
        Route::patch('/penjual/transaksi/{transaksi}/update-status', [TransaksiController::class, 'updateStatusPenjual'])->name('penjual.transaksi.updateStatus');

    });
}


// --- Rute untuk Pembeli ---
if (request()->getPort() == 8000) {
    Route::middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':pembeli'])->group(function () {
        Route::get('/store', [StoreController::class, 'index'])->name('pembeli.store');

        // Rute Keranjang
        Route::post('/cart/add/{product}', [StoreController::class, 'addToCart'])->name('pembeli.cart.add');
        Route::get('/cart', [StoreController::class, 'cart'])->name('pembeli.cart');
        Route::patch('/cart/update/{productId}', [StoreController::class, 'updateCart'])->name('pembeli.cart.update');
        Route::delete('/cart/remove/{productId}', [StoreController::class, 'removeCartItem'])->name('pembeli.cart.remove');

        // Rute Checkout
        Route::post('/checkout', [StoreController::class, 'checkout'])->name('pembeli.checkout');

        // Midtrans Payment Gateway View Route
        Route::get('/payment-gateway', function() {
            // This route is primarily for displaying the payment page after checkout.
            // It expects $snapToken and $transaksi to be passed from the checkout method.
            // You might want to make this more robust if direct access is possible.
            return redirect()->route('pembeli.store')->with('error', 'Akses tidak langsung ke halaman pembayaran.');
        })->name('pembeli.payment_gateway');

        // Midtrans Notification Handler (Webhook)
        Route::post('/midtrans-notification', [StoreController::class, 'handleMidtransNotification'])->name('midtrans.notification');


        // Rute Transaksi untuk Pembeli
        Route::get('/transaksi', [TransaksiController::class, 'indexPembeli'])->name('pembeli.transaksi.index');
        Route::get('/transaksi/{transaksi}', [TransaksiController::class, 'showPembeli'])->name('pembeli.transaksi.show');
        Route::patch('/transaksi/{transaksi}/update-status-diterima', [TransaksiController::class, 'updateStatusDiterima'])->name('pembeli.transaksi.updateStatusDiterima');
    });
}

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