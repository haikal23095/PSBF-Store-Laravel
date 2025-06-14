<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\TransactionDetail;

class DashboardController extends Controller
{
    public function penjualDashboard()
    {
        $user = Auth::user();

        $totalProduk = Product::where('user_id', $user->id)->count();

        $produkHabis = Product::where('user_id', $user->id)
            ->where('stok', 0)
            ->count();

        $pesananBaru = TransactionDetail::whereHas('product', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->count();

        $totalPendapatan = TransactionDetail::whereHas('product', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->sum('subtotal');

        return view('penjual.dashboard', compact(
            'user',
            'totalProduk',
            'produkHabis',
            'pesananBaru',
            'totalPendapatan'
        ));
    }

    public function transaksiPenjual()
    {
        $penjualId = Auth::id();

        $transaksi = Transaction::whereHas('details.product', function ($query) use ($penjualId) {
                $query->where('user_id', $penjualId);
            })
            ->with(['details.product', 'pembeli'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('penjual.transaksi.index', compact('transaksi'));
    }

}