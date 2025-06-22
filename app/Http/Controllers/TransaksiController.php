<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\DetailTransaksi; // Make sure to import your DetailTransaksi model
use App\Models\Produk; // Make sure to import your Produk model
use App\Models\User;

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

            return back()->with('success', 'Status transaksi berhasil diperbarui menjadi ' . $newStatus . '.');
        } else {
            return back()->with('error', 'Transisi status tidak valid.');
        }
    }

}
