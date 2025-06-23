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
    use Illuminate\Support\Facades\Log; // Import Log facade for better error logging
    use Midtrans\Config;
    use Midtrans\Snap;
    use Midtrans\Notification;

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

        public function checkout()
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
                $transactionDetailsData = []; // To store data for TransactionDetail creation later
                
                foreach ($cartItems as $item) {
                    $totalPrice += $item['quantity'] * $item['price'];
                }

                // Buat entri Transaksi baru
                $transaksi = Transaction::create([
                    'user_id' => $user->id,
                    'total_harga' => $totalPrice,
                    'tanggal_transaksi' => now(),
                    'status_transaksi' => 'menunggu_pembayaran', // Status awal transaksi
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
                    } else {
                        // Handle if product not found (optional)
                    }
                }

                // Inisialisasi konfigurasi Midtrans
                Config::$serverKey = config('services.midtrans.server_key');
                Config::$isProduction = config('services.midtrans.is_production');
                Config::$isSanitized = true; // Always sanitize
                Config::$is3ds = true; // Always enable 3DS

                // Siapkan parameter transaksi untuk Midtrans Snap
                $params = [
                    'transaction_details' => [
                        'order_id' => (string)$transaksi->id, // Gunakan ID transaksi sebagai order_id Midtrans, pastikan string
                        'gross_amount' => $totalPrice,
                    ],
                    'customer_details' => [
                        'first_name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->no_telp,
                        'address' => $user->alamat,
                    ],
                    'item_details' => [],
                ];

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

                // DEBUGGING: Log session data before saving
                Log::info('Checkout: Saving to session', [
                    'snap_token_set' => !empty($snapToken),
                    'transaksi_id_set' => $transaksi->id
                ]);

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

                // PERUBAHAN PENTING UNTUK PRG
                // Simpan snapToken dan transaksi ID ke sesi untuk diambil di rute GET selanjutnya
                Session::put('midtrans_snap_token', $snapToken);
                Session::put('midtrans_transaksi_id', $transaksi->id);

                // DEBUGGING: Log after session put, before redirect
                Log::info('Checkout: Session data after put', [
                    'midtrans_snap_token_session' => Session::get('midtrans_snap_token'),
                    'midtrans_transaksi_id_session' => Session::get('midtrans_transaksi_id')
                ]);

                // REDIRECT ke rute GET baru untuk menampilkan halaman pembayaran
                return redirect()->route('pembeli.show_payment_page');

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error during checkout: ' . $e->getMessage()); // Log the error for debugging
                return redirect()->route('pembeli.cart')->with('error', 'Terjadi kesalahan saat checkout: ' . $e->getMessage());
            }
        }

        // METODE BARU UNTUK MENAMPILKAN HALAMAN PEMBAYARAN VIA GET
        public function showPaymentPage()
        {
            // DEBUGGING: Log session data upon page load
            Log::info('Show Payment Page: Session data at start', [
                'midtrans_snap_token_session' => Session::get('midtrans_snap_token'),
                'midtrans_transaksi_id_session' => Session::get('midtrans_transaksi_id')
            ]);

            // Ambil snapToken dan transaksi ID dari sesi
            $snapToken = Session::get('midtrans_snap_token');
            $transaksiId = Session::get('midtrans_transaksi_id');

            // Pastikan ada data di sesi
            if (!$snapToken || !$transaksiId) {
                Log::warning('Payment page accessed without required session data.'); // Log if this happens
                return redirect()->route('pembeli.cart')->with('error', 'Tidak ada data pembayaran yang ditemukan. Silakan coba checkout lagi.');
            }

            // Ambil kembali objek transaksi dari database
            $transaksi = Transaction::find($transaksiId);

            if (!$transaksi) {
                Log::error("Transaction not found for ID: {$transaksiId} during showPaymentPage."); // Log if transaction is not found
                return redirect()->route('pembeli.cart')->with('error', 'Transaksi tidak ditemukan. Silakan coba checkout lagi.');
            }

            // Hapus data dari sesi setelah digunakan (agar tidak bisa di-refresh terus)
            Session::forget('midtrans_snap_token');
            Session::forget('midtrans_transaksi_id');

            // DEBUGGING: Log after successful retrieval and before view
            Log::info('Show Payment Page: Data retrieved successfully, preparing view.');

            return view('pembeli.payment_gateway', compact('snapToken', 'transaksi'));
        }

        // Midtrans Notification Handler (for Webhook)
        public function handleMidtransNotification(Request $request)
        {
            // Log untuk memverifikasi apakah notifikasi mencapai method ini
            Log::info('Midtrans notification received: Request data', $request->all());

            // Set your Merchant Server Key
            Config::$serverKey = config('services.midtrans.server_key');
            Config::$isProduction = config('services.midtrans.is_production');
            Config::$isSanitized = true;
            Config::$is3ds = true;

            // Get notification payload as PHP object
            $notif = new Notification(); // Use imported Notification class

            $transactionStatus = $notif->transaction_status;
            $orderId = $notif->order_id;
            $fraudStatus = $notif->fraud_status;
            $paymentType = $notif->payment_type;

            // Log detail notifikasi
            Log::info('Midtrans Notification Details', [
                'order_id' => $orderId,
                'transaction_status' => $transactionStatus,
                'fraud_status' => $fraudStatus,
                'payment_type' => $paymentType
            ]);


            $transaksi = Transaction::find($orderId);

            if ($transaksi) {
                Log::info("Transaction found, processing notification for ID: {$transaksi->id}");
                switch ($transactionStatus) {
                    case 'capture':
                        // For credit card transaction, check 'fraud' status
                        if ($paymentType == 'credit_card') {
                            switch ($fraudStatus) {
                                case 'challenge':
                                    $transaksi->status_transaksi = 'menunggu_pembayaran'; // Still waiting if challenged
                                    if ($transaksi->payment) {
                                        $transaksi->payment->status_payment = 'pending';
                                    }
                                    break;
                                default:
                                    $transaksi->status_transaksi = 'dikemas'; // Ready to be packed
                                    if ($transaksi->payment) {
                                        $transaksi->payment->status_payment = 'paid';
                                    }
                                    break;
                            }
                        }
                        break;
                    case 'settlement':
                        $transaksi->status_transaksi = 'dikemas'; // Ready to be packed
                        if ($transaksi->payment) {
                            $transaksi->payment->status_payment = 'paid';
                        }
                        break;
                    case 'pending':
                        $transaksi->status_transaksi = 'menunggu_pembayaran';
                        if ($transaksi->payment) {
                            $transaksi->payment->status_payment = 'pending';
                        }
                        break;
                    case 'deny':
                    case 'expire':
                    case 'cancel':
                        $transaksi->status_transaksi = 'dibatalkan'; // Or 'gagal'
                        if ($transaksi->payment) {
                            $transaksi->payment->status_payment = 'failed';
                        }
                        break;
                }
                $transaksi->save();
                // Simpan perubahan status pembayaran juga
                if ($transaksi->payment) {
                    $transaksi->payment->save();
                    Log::info("Payment status updated to: {$transaksi->payment->status_payment} for transaction ID: {$transaksi->id}");
                }
                Log::info("Transaction status updated to: {$transaksi->status_transaksi} for ID: {$transaksi->id}");

            } else {
                Log::warning("Transaction not found for orderId: {$orderId} from Midtrans notification.");
            }

            return response()->json(['message' => 'Notification handled'], 200);
        }
        
    }
    