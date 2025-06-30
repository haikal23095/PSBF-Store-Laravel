<?php
namespace App\Http\Controllers;
use App\Events\TransactionStatusUpdated; // 1. Import event baru
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\TransactionDetail; // Make sure to import your TransactionDetail model
use App\Models\Produk; // Make sure to import your Produk model
use App\Models\User;
use App\Models\Payment;
use Midtrans\Config;
use Midtrans\Transaction as MidtransTransaction;

class TransaksiController extends Controller
{
    /**
     * Menampilkan daftar transaksi untuk pembeli berdasarkan status.
     */
    public function indexPembeli(Request $request)
    {
        $validStatuses = ['menunggu_pembayaran', 'dikemas', 'dikirim', 'diterima'];
        $status = $request->get('status', 'menunggu_pembayaran');

        if (!in_array($status, $validStatuses)) {
            abort(404);
        }

        // Ambil data transaksi yang dipaginasi untuk status yang aktif (sudah benar)
        $transaksis = Auth::user()
            ->transaksis()
            ->where('status_transaksi', $status)
            ->withCount('details')
            ->with('details.product.user')
            ->latest() // Menggunakan latest() lebih ringkas dari orderBy('created_at', 'desc')
            ->paginate(10);
            
        // 2. Tambahkan query untuk menghitung jumlah di setiap status
        $statusCounts = Auth::user()->transaksis()
            ->select('status_transaksi', DB::raw('count(*) as total'))
            ->groupBy('status_transaksi')
            ->pluck('total', 'status_transaksi');

        // 3. Kirim data ke view menggunakan compact atau array
        return view('pembeli.transaksi', [
            'transaksis' => $transaksis,
            'statusCounts' => $statusCounts,
            'currentStatus' => $status,
        ]);
    }

    /**
     * Menampilkan daftar transaksi untuk penjual berdasarkan status.
     */
    public function indexPenjual(Request $request)
    {
        $validStatuses = ['menunggu_pembayaran', 'dikemas', 'dikirim', 'diterima'];
        $status = $request->get('status', 'menunggu_pembayaran');

        if (!in_array($status, $validStatuses)) {
            abort(404);
        }

        // Ambil ID pengguna yang sedang login (penjual)
        $sellerId = Auth::id();

        // Eager load 'details.product' and 'pembeli' relationships
        // Use a subquery or join to filter by seller's products
        $transaksis = Transaction::with(['details.product', 'pembeli'])
            ->where('status_transaksi', $status)
            ->whereHas('details.product', function ($query) use ($sellerId) {
                $query->where('user_id', $sellerId); // Filter products by seller's ID
            })
            ->latest()
            ->paginate(10);

        // For status counts, ensure it's efficient
        $statusCounts = Transaction::whereHas('details.product', function ($query) use ($sellerId) {
                $query->where('user_id', $sellerId);
            })
            ->select('status_transaksi', DB::raw('count(*) as total'))
            ->groupBy('status_transaksi')
            ->pluck('total', 'status_transaksi');


        return view('penjual.transaksi.index', [
            'transaksis' => $transaksis,
            'statusCounts' => $statusCounts,
            'currentStatus' => $status,
        ]);

    }

    public function updateStatusDiterima(Request $request, Transaction $transaksi)
    {
        // 1. Otorisasi: Pastikan pengguna adalah pemilik transaksi
        if (Auth::id() !== $transaksi->user_id) {
            return back()->with('error', 'Anda tidak memiliki akses ke transaksi ini.');
        }

        // 2. Validasi State: Pastikan hanya bisa konfirmasi jika statusnya 'dikirim'
        if ($transaksi->status_transaksi !== 'dikirim') {
            return back()->with('error', 'Status transaksi tidak valid untuk konfirmasi.');
        }

        // Simpan status lama untuk event
        $oldStatus = $transaksi->status_transaksi;

        // 3. Update Status di Database
        $transaksi->status_transaksi = 'diterima';
        $transaksi->save();

        // 4. Broadcast Event ke semua pihak terkait (pembeli & penjual)
        broadcast(new TransactionStatusUpdated($transaksi, $oldStatus))->toOthers();

        // 5. Redirect kembali dengan pesan sukses
        // Meskipun frontend akan update via JS, redirect ini berguna jika JS gagal.
        return redirect()->route('pembeli.transaksi.show', $transaksi)
                         ->with('success', 'Konfirmasi pesanan berhasil. Transaksi selesai.');
    }

    public function updateStatus(Request $request, Transaction $transaksi)
    {
        // Validate the incoming status
        $request->validate([
            'status' => ['required', 'string', 'in:dikemas,dikirim'], // Only allow these transitions
        ]);

        $sellerId = Auth::id();

        // IMPORTANT: Verify that this transaction contains products from the current seller
        // This prevents sellers from updating transactions they don't own parts of.
        $hasSellerProducts = $transaksi->details()->whereHas('product', function($query) use ($sellerId) {
            $query->where('user_id', $sellerId);
        })->exists();

        if (!$hasSellerProducts) {
            return back()->with('error', 'Anda tidak memiliki izin untuk mengupdate transaksi ini.');
        }

        // Define allowed transitions for the seller
        $allowedTransitions = [
            'menunggu_pembayaran' => ['dikemas'], // Seller can mark as 'dikemas' if 'menunggu_pembayaran' (if applicable)
            'dikemas' => ['dikirim'],             // Seller can mark as 'dikirim' if 'dikemas'
            // 'dikirim' and 'diterima' typically not updated by seller through this path
        ];

        $newStatus = $request->input('status');
        $currentStatus = $transaksi->status_transaksi;

        if (isset($allowedTransitions[$currentStatus]) && in_array($newStatus, $allowedTransitions[$currentStatus])) {
            $transaksi->status_transaksi = $newStatus;
            $transaksi->save();

            // Ini akan mengirim pembaruan ke pembeli secara real-time
            broadcast(new TransactionStatusUpdated($transaksi, $currentStatus))->toOthers();

            return back()->with('success', 'Status transaksi berhasil diperbarui menjadi ' . $newStatus . '.');
        } else {
            return back()->with('error', 'Transisi status tidak valid.');
        }
    }

       public function showPembeli(Transaction $transaksi)
    {
        // Pastikan pengguna yang sedang login adalah pemilik transaksi ini
        if (Auth::id() !== $transaksi->user_id) {
            abort(403, 'ANDA TIDAK MEMILIKI AKSES KE TRANSAKSI INI.');
        }

        // 2. Hapus 'payment' dari fungsi load()
        $transaksi->load('details.product', 'pembeli');

        // 3. Ambil data payment secara manual
        // Kode ini berdasarkan asumsi dari StoreController.php bahwa
        // tabel 'payments' memiliki kolom 'transaksi_id'
        $payment = Payment::where('transaksi_id', $transaksi->id)->first();

        // 4. Tambahkan data payment ke objek transaksi secara manual
        // agar bisa diakses di view dengan $transaksi->payment
        $transaksi->payment = $payment;


        // Kembalikan view dengan data transaksi yang sudah lengkap
        return view('pembeli.transaksi.show', compact('transaksi'));
    }
    public function checkStatus(Transaction $transaksi)
    {
        // Pastikan hanya pemilik yang bisa cek
        if (auth()->id() !== $transaksi->user_id) {
            abort(403);
        }

        // Konfigurasi Midtrans
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');

        try {
            // Panggil API untuk mendapatkan status transaksi dari Midtrans
            $status = MidtransTransaction::status($transaksi->id); // $transaksi->id adalah order_id Anda
            
            // Logika pembaruan status berdasarkan respons
            // Ini bisa direfaktor dari logika yang ada di handleMidtransNotification
            if ($status->transaction_status == 'settlement' || $status->transaction_status == 'capture') {
                $transaksi->status_transaksi = 'dikemas';
                if ($transaksi->payment) {
                    $transaksi->payment->status_payment = 'paid';
                    $transaksi->payment->save();
                }
                $transaksi->save();

                return redirect()->route('pembeli.transaksi.show', $transaksi)->with('success', 'Status pembayaran berhasil diperbarui!');
            }

            return redirect()->route('pembeli.transaksi.show', $transaksi)->with('info', 'Status pembayaran belum berubah: ' . $status->transaction_status);

        } catch (\Exception $e) {
            return redirect()->route('pembeli.transaksi.show', $transaksi)->with('error', 'Gagal mengecek status: ' . $e->getMessage());
        }
    }

}
