@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Daftar Produk</h1>

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

                            <a href="#" class="block text-center mt-3 px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                                Masukkan Keranjang
                            </a>
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
