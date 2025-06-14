<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;

class DashboardController extends Controller
{
    public function penjualDashboard()
    {
        $user = Auth::user();
        $er = 5;
        return view('penjual.dashboard', compact('user', 'er'));
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