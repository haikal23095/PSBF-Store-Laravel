@extends('layouts.app')
{{-- @include('layouts.app', ['title' => 'Dashboard Penjual']) --}}
@section('content')


<div class="container mx-auto py-8">
    <h1 class="text-3xl font-bold text-gray-800">Dashboard Penjual</h1>
    <p class="mt-2 text-gray-600">Selamat datang kembali, {{ $user->name }}!</p>

    <div class="mt-8">
        <a href="{{ route('penjual.produk.index') }}" class="block w-full md:w-auto md:inline-block bg-indigo-600 text-white text-center font-bold py-4 px-6 rounded-lg hover:bg-indigo-700 transition-colors duration-300 shadow-lg">
            Kelola Produk Anda &rarr;
        </a>

        {{-- Tombol tambahan untuk melihat transaksi --}}
        <a href="{{ route('penjual.transaksi.index') }}" class="block w-full md:w-auto md:inline-block bg-green-600 text-white text-center font-bold py-4 px-6 rounded-lg hover:bg-green-700 transition-colors duration-300 shadow-lg">
            Lihat Riwayat Transaksi &rarr;
        </a>
    </div>

    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Card Statistik -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-semibold text-gray-700">Total Produk</h3>
            <p class="text-3xl font-bold mt-2 text-indigo-600">{{ $totalProduk }}</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-semibold text-gray-700">Pesanan Baru</h3>
            <p class="text-3xl font-bold mt-2 text-green-600">{{ $pesananBaru }}</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-semibold text-gray-700">Total Pendapatan</h3>
            <p class="text-3xl font-bold mt-2 text-blue-600">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-semibold text-gray-700">Produk Habis</h3>
            <p class="text-3xl font-bold mt-2 text-red-600">{{ $produkHabis }}</p>
        </div>
    </div>
</div>

@endsection