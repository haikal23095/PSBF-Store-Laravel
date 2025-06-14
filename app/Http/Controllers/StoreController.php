<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

}
