<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction; // Import model Transaksi
use App\Models\TransactionDetail; // Import model TransaksiDetail
use App\Models\Payment; // Import Payment model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session; // Import Session facade
use Illuminate\Support\Facades\DB; // For database transactions
use Midtrans\Config; // Import Midtrans Config
use Midtrans\Snap;   // Import Midtrans Snap

class StoreController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Product::query();

        // Filter kategori hanya jika kategori tidak kosong
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        $products = $query->paginate(8);

        // Ambil daftar kategori unik dari tabel produk
        $kategoriList = Product::select('kategori')->distinct()->pluck('kategori');

        return view('pembeli.store', compact('user', 'products', 'kategoriList'));
    }

    public function addToCart(Request $request, Product $product)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Session::get('cart', []); // Dapatkan keranjang dari sesi, jika tidak ada, buat array kosong

        $productId = $product->id;
        $quantity = $request->quantity;

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
        } else {
            $cart[$productId] = [
                'product_id' => $productId,
                'name' => $product->nama_barang,
                'price' => $product->harga,
                'image' => $product->gambar,
                'quantity' => $quantity,
            ];
        }

        Session::put('cart', $cart); // Simpan kembali keranjang ke sesi

        return redirect()->back()->with('success', 'Produk berhasil ditambahkan ke keranjang!');
    }

    public function cart()
    {
        $cartItems = Session::get('cart', []); // Dapatkan item keranjang dari sesi
        $totalPrice = 0;

        foreach ($cartItems as $item) {
            $totalPrice += $item['quantity'] * $item['price'];
        }

        return view('pembeli.cart', compact('cartItems', 'totalPrice'));
    }

    public function updateCart(Request $request, $productId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Session::get('cart', []);

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] = $request->quantity;
            Session::put('cart', $cart);
            return redirect()->route('pembeli.cart')->with('success', 'Jumlah produk berhasil diperbarui.');
        }

        return redirect()->route('pembeli.cart')->with('error', 'Produk tidak ditemukan di keranjang.');
    }

    public function removeCartItem($productId)
    {
        $cart = Session::get('cart', []);

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            Session::put('cart', $cart);
            return redirect()->route('pembeli.cart')->with('success', 'Produk berhasil dihapus dari keranjang.');
        }

        return redirect()->route('pembeli.cart')->with('error', 'Produk tidak ditemukan di keranjang.');
    }

    public function checkout(Request $request)
    {
        $user = Auth::user();
        $cartItems = Session::get('cart', []);

        if (empty($cartItems)) {
            return redirect()->route('pembeli.cart')->with('error', 'Keranjang Anda kosong.');
        }

        // Gunakan transaksi database
        DB::beginTransaction();
        try {
            $totalPrice = 0;
            foreach ($cartItems as $item) {
                $totalPrice += $item['quantity'] * $item['price'];
            }

            // Buat entri Transaksi baru
            $transaksi = Transaction::create([
                'user_id' => $user->id,
                'total_harga' => $totalPrice,
                'tanggal_transaksi' => now(),
                'status_transaksi' => 'menunggu pembayaran', // Status awal transaksi
                'payment_id' => null, // Temporarily null, will be updated after payment creation
            ]);

            // Tambahkan item ke TransaksiDetail dan perbarui stok produk
            foreach ($cartItems as $item) {
                TransactionDetail::create([
                    'transaksi_id' => $transaksi->id,
                    'product_id' => $item['product_id'],
                    'jumlah' => $item['quantity'],
                    'subtotal' => $item['quantity'] * $item['price'],
                ]);

                // Perbarui stok produk
                $product = Product::find($item['product_id']);
                if ($product) {
                    $product->stok -= $item['quantity'];
                    $product->save();
                }
            }

            // Inisialisasi konfigurasi Midtrans
            Config::$serverKey = config('services.midtrans.server_key');
            Config::$isProduction = config('services.midtrans.is_production');
            Config::$isSanitized = true; // Always sanitize
            Config::$is3ds = true; // Always enable 3DS

            // Siapkan parameter transaksi untuk Midtrans Snap
            $params = array(
                'transaction_details' => array(
                    'order_id' => $transaksi->id, // Gunakan ID transaksi sebagai order_id Midtrans
                    'gross_amount' => $totalPrice,
                ),
                'customer_details' => array(
                    'first_name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->no_telp,
                    'address' => $user->alamat,
                ),
                'item_details' => [],
            );

            // Tambahkan detail produk ke item_details Midtrans
            foreach ($cartItems as $item) {
                $params['item_details'][] = [
                    'id' => $item['product_id'],
                    'name' => $item['name'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                ];
            }

            // Dapatkan Snap Token dari Midtrans
            $snapToken = Snap::getSnapToken($params);

            // Buat entri Payment baru
            $payment = Payment::create([
                'transaksi_id' => $transaksi->id,
                'payment_method' => 'Midtrans Snap', // Metode pembayaran via Midtrans Snap
                'status_payment' => 'pending', // Status pembayaran awal
            ]);

            // Perbarui transaksi dengan payment_id yang baru dibuat
            $transaksi->payment_id = $payment->payment_id;
            $transaksi->save(); // Simpan perubahan pada transaksi

            // Hapus keranjang dari sesi setelah checkout berhasil
            Session::forget('cart');

            DB::commit();

            // Arahkan ke halaman pembayaran dengan Snap Token
            return view('pembeli.payment_gateway', compact('snapToken', 'transaksi'));

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('pembeli.cart')->with('error', 'Terjadi kesalahan saat checkout: ' . $e->getMessage());
        }
    }

    // Midtrans Notification Handler (for Webhook)
    public function handleMidtransNotification(Request $request)
    {
        // Set your Merchant Server Key
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        // Get notification payload as PHP object
        $notif = new \Midtrans\Notification();

        $transactionStatus = $notif->transaction_status;
        $orderId = $notif->order_id;
        $fraudStatus = $notif->fraud_status;
        $paymentType = $notif->payment_type;

        $transaksi = Transaction::find($orderId);

        if ($transaksi) {
            if ($transactionStatus == 'capture') {
                // For credit card transaction, check 'fraud' status
                if ($paymentType == 'credit_card') {
                    if ($fraudStatus == 'challenge') {
                        $transaksi->status_transaksi = 'menunggu pembayaran'; // Still waiting if challenged
                        if ($transaksi->payment) {
                            $transaksi->payment->status_payment = 'pending';
                        }
                    } else {
                        $transaksi->status_transaksi = 'dikemas'; // Ready to be packed
                        if ($transaksi->payment) {
                            $transaksi->payment->status_payment = 'paid';
                        }
                    }
                }
            } elseif ($transactionStatus == 'settlement') {
                $transaksi->status_transaksi = 'dikemas'; // Ready to be packed
                if ($transaksi->payment) {
                    $transaksi->payment->status_payment = 'paid';
                }
            } elseif ($transactionStatus == 'pending') {
                $transaksi->status_transaksi = 'menunggu pembayaran';
                if ($transaksi->payment) {
                    $transaksi->payment->status_payment = 'pending';
                }
            } elseif ($transactionStatus == 'deny') {
                $transaksi->status_transaksi = 'dibatalkan'; // Or 'gagal'
                if ($transaksi->payment) {
                    $transaksi->payment->status_payment = 'failed';
                }
            } elseif ($transactionStatus == 'expire') {
                $transaksi->status_transaksi = 'dibatalkan';
                if ($transaksi->payment) {
                    $transaksi->payment->status_payment = 'failed';
                }
            } elseif ($transactionStatus == 'cancel') {
                $transaksi->status_transaksi = 'dibatalkan';
                if ($transaksi->payment) {
                    $transaksi->payment->status_payment = 'failed';
                }
            }
            $transaksi->save();
        }

        return response()->json(['message' => 'Notification handled'], 200);
    }
}