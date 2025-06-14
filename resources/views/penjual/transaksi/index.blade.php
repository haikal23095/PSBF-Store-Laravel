@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Daftar Transaksi Pembeli</h1>
        <a href="{{ route('penjual.dashboard') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded inline-block transition">
            &larr; Kembali
        </a>
    </div>

    <div class="bg-white shadow-md rounded">
        <table class="min-w-full">
            <thead>
                <tr>
                    <th class="px-4 py-2">Tanggal</th>
                    <th class="px-4 py-2">Produk</th>
                    <th class="px-4 py-2">Pembeli</th>
                    <th class="px-4 py-2">Jumlah</th>
                    <th class="px-4 py-2">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transaksi as $item)
                    @foreach ($item->details as $detail)
                        <tr class="border-t">
                            <td class="px-4 py-2">{{ $item->created_at->format('d M Y') }}</td>
                            <td class="px-4 py-2">{{ $detail->product->nama_barang ?? '-' }}</td>
                            <td class="px-4 py-2">{{ $item->pembeli->name ?? '-' }}</td>
                            <td class="px-4 py-2">{{ $detail->jumlah }}</td>
                            <td class="px-4 py-2">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="5" class="text-center px-4 py-6">Belum ada transaksi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
