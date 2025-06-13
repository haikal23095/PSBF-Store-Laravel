@extends('layouts.app')
{{-- @include('layouts.app', ['title' => 'Toko Online']) --}}
@section('content')
<div class="container mx-auto py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Jelajahi Produk Kami</h1>
        <p class="text-gray-600">Selamat berbelanja, {{ $user->name }}!</p>
    </div>

    <!-- Tampilkan daftar produk di sini -->
    <div class="text-center py-16 bg-gray-100 rounded-lg">
        <h2 class="text-xl font-semibold text-gray-700">Belum Ada Produk</h2>
        <p class="text-gray-500 mt-2">Daftar produk akan ditampilkan di sini setelah ditambahkan oleh penjual.</p>
    </div>
</div>
@endsection