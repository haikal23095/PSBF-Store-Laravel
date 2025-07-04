@extends('layouts.app')
@php
    use Illuminate\Support\Str;
@endphp
@section('content')
<div class="container mx-auto px-4 py-8">
    {{-- Tombol Kembali --}}
    <div class="mb-6">
        <a href="{{ route('pembeli.transaksi.index', ['status' => $transaksi->status_transaksi]) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
            <i class="fas fa-arrow-left mr-2"></i>
            Kembali ke Daftar Transaksi
        </a>
    </div>

    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        {{-- Header Kartu Transaksi --}}
        <div class="bg-gray-100 px-6 py-4 border-b">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Detail Transaksi #{{ $transaksi->id }}</h2>
                    <p class="text-sm text-gray-600">Tanggal: {{ $transaksi->created_at->format('d F Y, H:i') }}</p>
                </div>
                <div class="text-right">
                    <span id="transaction-status-badge" class="px-4 py-2 rounded-full text-sm font-semibold
                        @switch($transaksi->status_transaksi)
                            @case('menunggu_pembayaran') bg-yellow-200 text-yellow-800 @break
                            @case('dikemas') bg-blue-200 text-blue-800 @break
                            @case('dikirim') bg-indigo-200 text-indigo-800 @break
                            @case('diterima') bg-green-200 text-green-800 @break
                            @case('dibatalkan') bg-red-200 text-red-800 @break
                            @default bg-gray-200 text-gray-800
                        @endswitch">
                        {{ str_replace('_', ' ', Str::title($transaksi->status_transaksi)) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Isi Kartu --}}
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Detail Produk --}}
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-3 border-b pb-2">Produk yang Dibeli</h3>
                    <div class="space-y-4">
                        @foreach ($transaksi->details as $detail)
                        <div class="flex items-start space-x-4">
                            <img src="{{ $detail->product->gambar ? asset('storage/' . $detail->product->gambar) : 'https://placehold.co/100x100/e2e8f0/e2e8f0?text=No+Image' }}" alt="{{ $detail->product->nama_barang }}" class="w-20 h-20 object-cover rounded-md">
                            <div class="flex-1">
                                <p class="font-semibold text-gray-800">{{ $detail->product->nama_barang }}</p>
                                <p class="text-sm text-gray-500">{{ $detail->jumlah }} x Rp {{ number_format($detail->product->harga, 0, ',', '.') }}</p>
                            </div>
                            <p class="text-md font-semibold text-gray-900">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Ringkasan Pembayaran --}}
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-3 border-b pb-2">Ringkasan Pembayaran</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <p class="text-gray-600">Total Harga:</p>
                            <p class="font-semibold text-gray-800">Rp {{ number_format($transaksi->total_harga, 0, ',', '.') }}</p>
                        </div>
                        <div class="flex justify-between">
                            <p class="text-gray-600">Metode Pembayaran:</p>
                            <p class="font-semibold text-gray-800">{{ $transaksi->payment->payment_method ?? 'N/A' }}</p>
                        </div>
                        <div class="flex justify-between">
                            <p class="text-gray-600">Status Pembayaran:</p>
                            <p class="font-semibold text-gray-800 capitalize">{{ $transaksi->payment->status_payment ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Aksi Pembeli --}}
            <div id="buyer-action-container" class="mt-8 pt-6 border-t">
                @if ($transaksi->status_transaksi == 'dikirim')
                    <h3 class="text-lg font-semibold text-center mb-3">Barang sudah Anda terima?</h3>
                    <p class="text-center text-gray-600 mb-4">Konfirmasi untuk menyelesaikan transaksi.</p>
                    <form action="{{ route('pembeli.transaksi.updateStatusDiterima', $transaksi) }}" method="POST" class="text-center">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="w-full md:w-auto px-8 py-3 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700">
                            <i class="fas fa-check-circle mr-2"></i>
                            Konfirmasi Pesanan Diterima
                        </button>
                    </form>
                @elseif($transaksi->status_transaksi == 'diterima')
                    <div class="text-center text-green-600">
                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                        <p class="font-semibold">Transaksi telah selesai pada {{ $transaksi->updated_at->format('d F Y, H:i') }}.</p>
                    </div>  
                @else
                    <p class="text-center text-gray-600">Tidak ada aksi yang dapat dilakukan saat ini.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        console.log('🔍 Transaction detail page loaded');
        console.log('Echo object:', window.Echo);
        console.log('Current user ID:', {{ Auth::id() }});
        console.log('Current transaction ID:', {{ $transaksi->id }});

        if (typeof window.Echo !== 'undefined') {
            console.log('✅ Echo found, setting up listener...');
            
            window.Echo.private('user.{{ Auth::id() }}')
                .listen('.transaction.status.updated', (e) => {
                    console.log('🎉 Event status update received:', e);
                    console.log('📊 Event details:', {
                        event_transaction_id: e.transaction_id,
                        current_page_transaction_id: {{ $transaksi->id }},
                        old_status: e.old_status,
                        new_status: e.new_status,
                        match: e.transaction_id === {{ $transaksi->id }}
                    });

                    // Cek apakah event ini untuk transaksi yang sedang dilihat
                    if (e.transaction_id === {{ $transaksi->id }}) {
                        console.log('✅ Event matches current transaction');
                        
                        updateTransactionUI(e.new_status, e.new_status_label);
                        
                        // Show notification
                        showNotification(`Status transaksi diperbarui menjadi "${e.new_status_label}"`);
                    } else {
                        console.log('ℹ️ Event for different transaction, ignoring');
                    }
                })
                .error((error) => {
                    console.error('❌ Echo listen error:', error);
                });

            // Debug connection
            if (window.Echo.connector && window.Echo.connector.pusher) {
                window.Echo.connector.pusher.connection.bind('connected', () => {
                    console.log('✅ WebSocket connected on detail page');
                });
            }
        } else {
            console.error('❌ Laravel Echo tidak ditemukan');
        }
    });

    function updateTransactionUI(newStatus, newStatusLabel) {
        const statusBadge = document.getElementById('transaction-status-badge');
        const actionContainer = document.getElementById('buyer-action-container');

        if (statusBadge) {
            // Update text
            statusBadge.textContent = newStatusLabel || newStatus;

            // Update classes based on status
            const statusClasses = {
                'menunggu_pembayaran': 'px-4 py-2 rounded-full text-sm font-semibold bg-yellow-200 text-yellow-800',
                'dikemas': 'px-4 py-2 rounded-full text-sm font-semibold bg-blue-200 text-blue-800',
                'dikirim': 'px-4 py-2 rounded-full text-sm font-semibold bg-indigo-200 text-indigo-800',
                'diterima': 'px-4 py-2 rounded-full text-sm font-semibold bg-green-200 text-green-800'
            };

            statusBadge.className = statusClasses[newStatus] || 'px-4 py-2 rounded-full text-sm font-semibold bg-gray-200 text-gray-800';
        }

        // Update action container based on new status
        if (actionContainer) {
            if (newStatus === 'dikirim') {
                actionContainer.innerHTML = `
                    <h3 class="text-lg font-semibold text-center mb-3">Barang sudah Anda terima?</h3>
                    <p class="text-center text-gray-600 mb-4">Konfirmasi untuk menyelesaikan transaksi.</p>
                    <form action="{{ route('pembeli.transaksi.updateStatusDiterima', $transaksi) }}" method="POST" class="text-center">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="w-full md:w-auto px-8 py-3 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700">
                            <i class="fas fa-check-circle mr-2"></i>
                            Konfirmasi Pesanan Diterima
                        </button>
                    </form>
                `;
            } else if (newStatus === 'diterima') {
                actionContainer.innerHTML = `
                    <div class="text-center text-green-600">
                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                        <p class="font-semibold">Transaksi telah selesai.</p>
                    </div>
                `;
            } else {
                actionContainer.innerHTML = `
                    <p class="text-center text-gray-600">Tidak ada aksi yang dapat dilakukan saat ini.</p>
                `;
            }
        }
    }

    function showNotification(message) {
        // Create toast notification
        const toast = document.createElement('div');
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #4CAF50;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            z-index: 9999;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            font-size: 14px;
            max-width: 400px;
        `;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            toast.remove();
        }, 5000);
    }
</script>
@endpush