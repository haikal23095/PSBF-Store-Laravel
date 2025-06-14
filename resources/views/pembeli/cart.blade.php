@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Keranjang Belanja</h1>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    @if (empty($cartItems))
        <p class="text-center text-gray-500">Keranjang Anda kosong.</p>
        <div class="text-center mt-4">
            <a href="{{ route('pembeli.store') }}" class="inline-block px-6 py-3 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Lanjutkan Belanja</a>
        </div>
    @else
        <div class="bg-white shadow rounded-lg p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Aksi</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($cartItems as $productId => $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-full object-cover" src="{{ $item['image'] ? asset('storage/' . $item['image']) : 'https://placehold.co/100x100/e2e8f0/e2e8f0?text=No+Image' }}" alt="{{ $item['name'] }}">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $item['name'] }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">Rp {{ number_format($item['price'], 0, ',', '.') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <form action="{{ route('pembeli.cart.update', $productId) }}" method="POST" class="flex items-center">
                                        @csrf
                                        @method('PATCH')
                                        <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" class="w-20 border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                        <button type="submit" class="ml-2 text-indigo-600 hover:text-indigo-900 text-sm">Update</button>
                                    </form>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">Rp {{ number_format($item['quantity'] * $item['price'], 0, ',', '.') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <form action="{{ route('pembeli.cart.remove', $productId) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini dari keranjang?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 border-t border-gray-200 pt-6 flex justify-end items-center">
                <div class="text-lg font-bold text-gray-900">Total Harga:</div>
                <div class="text-2xl font-bold text-indigo-600 ml-4">Rp {{ number_format($totalPrice, 0, ',', '.') }}</div>
            </div>

            <div class="mt-8 flex justify-end">
                <a href="{{ route('pembeli.store') }}" class="mr-4 px-6 py-3 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Lanjutkan Belanja</a>
                <form action="{{ route('pembeli.checkout') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin melanjutkan ke pembayaran?');">
                    @csrf
                    <button type="submit" class="px-6 py-3 bg-green-600 text-white rounded-md hover:bg-green-700">
                        Proses Checkout
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection