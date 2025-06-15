@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Daftar Produk</h1>
        {{-- Tombol Keranjang --}}
        <a href="{{ route('pembeli.cart') }}" class="relative inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
            <i class="fas fa-shopping-cart mr-2"></i>
            Keranjang
            {{-- Tampilkan jumlah item di keranjang jika ada --}}
            @php
                $cartCount = count(Session::get('cart', []));
            @endphp
            @if ($cartCount > 0)
                <span class="absolute top-0 right-0 -mt-2 -mr-2 px-2 py-1 text-xs font-bold bg-red-500 rounded-full">
                    {{ $cartCount }}
                </span>
            @endif
        </a>
    </div>

    <div class="md:w-1/4 mb-4">
        <form method="GET" action="{{ route('pembeli.store') }}">
            <label for="kategori" class="block text-sm font-medium text-gray-700 mb-2">Kategori:</label>
            <select name="kategori" id="kategori" onchange="this.form.submit()" class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Semua Kategori</option>
                @foreach ($kategoriList as $kategori)
                    <option value="{{ $kategori }}" {{ request('kategori') == $kategori ? 'selected' : '' }}>
                        {{ ucfirst($kategori) }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="md:w-3/4">
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if ($products->isEmpty())
            <p class="text-center text-gray-500">Belum ada produk tersedia.</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach ($products as $product)
                    <div class="bg-white shadow rounded-lg overflow-hidden min-h-[360px] flex flex-col">
                        <img src="{{ $product->gambar ? asset('storage/' . $product->gambar) : 'https://placehold.co/100x100/e2e8f0/e2e8f0?text=No+Image' }}" alt="{{ $product->nama_barang }}" class="w-full h-full object-cover">

                        <div class="p-4 h-56 flex flex-col justify-between">
                            <div>
                                <h2 class="text-lg font-semibold text-gray-800">{{ $product->nama_barang }}</h2>
                                <p class="text-sm text-gray-500 capitalize">{{ $product->kategori }}</p>
                                <p class="text-indigo-600 font-bold mt-2">Rp {{ number_format($product->harga, 0, ',', '.') }}</p>
                                <p class="text-sm text-gray-600 mt-1 h-16 overflow-hidden">
                                    Deskripsi: {{ Str::limit($product->deskripsi, 100) }}
                                </p>
                            </div>

                            {{-- Form untuk menambahkan ke keranjang --}}
                            <form action="{{ route('pembeli.cart.add', $product) }}" method="POST" class="mt-3">
                                @csrf
                                <input type="hidden" name="quantity" value="1"> {{-- Default quantity to 1 --}}
                                <button type="submit" class="block w-full text-center px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                                    Masukkan Keranjang
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>


            {{-- Pagination --}}
            <div class="mt-8">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
