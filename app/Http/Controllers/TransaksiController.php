<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // <-- 1. Tambahkan DB Facade

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

    // Method lain seperti showPembeli(), updateStatusDiterima(), dll.
}
