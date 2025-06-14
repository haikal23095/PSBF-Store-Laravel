<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        // Ambil produk milik penjual yang sedang login
        $products = Product::where('user_id', Auth::id())->latest()->paginate(10);
        return view('penjual.produk.index', compact('products'));
    }

    public function create()
    {
        return view('penjual.produk.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_barang' => 'required|string|max:255',
            'kategori' => 'required|in:hp,laptop,printer,kamera',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'deskripsi' => 'required|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // max 2MB
        ]);

        $path = null;
        if ($request->hasFile('gambar')) {
            // Simpan gambar di public/storage/products
            $path = $request->file('gambar')->store('products', 'public');
        }

        Product::create([
            'nama_barang' => $request->nama_barang,
            'kategori' => $request->kategori,
            'harga' => $request->harga,
            'stok' => $request->stok,
            'deskripsi' => $request->deskripsi,
            'gambar' => $path,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('penjual.produk.index')
                         ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $produk)
    {
        // Pastikan penjual hanya bisa mengedit produk miliknya
        if ($produk->user_id !== Auth::id()) {
            abort(403);
        }
        return view('penjual.produk.edit', compact('produk'));
    }

    public function update(Request $request, Product $produk)
    {
        // Pastikan penjual hanya bisa mengupdate produk miliknya
        if ($produk->user_id !== Auth::id()) {
            abort(403);
        }
        
        $request->validate([
            'nama_barang' => 'required|string|max:255',
            'kategori' => 'required|in:hp,laptop,printer,kamera',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'deskripsi' => 'required|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $path = $produk->gambar;
        if ($request->hasFile('gambar')) {
            // Hapus gambar lama jika ada
            if ($produk->gambar) {
                Storage::disk('public')->delete($produk->gambar);
            }
            // Simpan gambar baru
            $path = $request->file('gambar')->store('products', 'public');
        }

        $produk->update([
            'nama_barang' => $request->nama_barang,
            'kategori' => $request->kategori,
            'harga' => $request->harga,
            'stok' => $request->stok,
            'deskripsi' => $request->deskripsi,
            'gambar' => $path,
        ]);

        return redirect()->route('penjual.produk.index')
                         ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $produk)
    {
        // Pastikan penjual hanya bisa menghapus produk miliknya
        if ($produk->user_id !== Auth::id()) {
            abort(403);
        }

        // Hapus gambar dari storage
        if ($produk->gambar) {
            Storage::disk('public')->delete($produk->gambar);
        }

        $produk->delete();

        return redirect()->route('penjual.produk.index')
                         ->with('success', 'Produk berhasil dihapus.');
    }

    
}
