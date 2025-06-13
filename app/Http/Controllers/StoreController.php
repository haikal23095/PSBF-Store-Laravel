<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoreController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        // Nanti kita akan mengambil data produk dari database di sini
        $products = []; // Ganti dengan Product::all() atau sejenisnya
        return view('pembeli.store', compact('user', 'products'));
    }
}

