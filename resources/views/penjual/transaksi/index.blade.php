@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Daftar Transaksi Penjual</h1> {{-- Changed title for clarity --}}
        <a href="{{ route('penjual.dashboard') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded inline-block transition">
            &larr; Kembali
        </a>
    </div>

    {{-- Status Filter Tabs (Optional but good for UX) --}}
    <div class="flex mb-6 space-x-2">
        @php
            $statuses = [
                'menunggu_pembayaran' => 'Menunggu Pembayaran',
                'dikemas' => 'Dikemas',
                'dikirim' => 'Dikirim',
                'diterima' => 'Diterima'
            ];
        @endphp
        @foreach ($statuses as $key => $label)
            <a href="{{ route('penjual.transaksi.index', ['status' => $key]) }}"
               class="py-2 px-4 rounded transition
                      {{ $currentStatus == $key ? 'bg-blue-600 text-white shadow' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                {{ $label }}
                @if (isset($statusCounts[$key]) && $statusCounts[$key] > 0)
                    <span id="status-count-{{ $key }}" class="ml-1 text-sm font-semibold {{ $currentStatus == $key ? 'bg-blue-700' : 'bg-gray-300 text-gray-800' }} px-2 py-0.5 rounded-full">
                        {{ $statusCounts[$key] }}
                    </span>
                @endif
            </a>
        @endforeach
    </div>
    {{-- End Status Filter Tabs --}}

    <div class="bg-white shadow-md rounded overflow-hidden"> {{-- Added overflow-hidden for rounded corners --}}
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Pesan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th> {{-- Added Order ID --}}
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pembeli</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Belanja</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($transaksis as $item)
                    <tr id="transaksi-row-{{ $item->id }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $item->created_at->format('d M Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $item->id }} {{-- Assuming Transaksi ID is your Order ID --}}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $item->pembeli->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            Rp {{ number_format($item->total_harga, 0, ',', '.') }} {{-- Assuming total_harga is on Transaksi model --}}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if ($item->status_transaksi == 'menunggu_pembayaran') bg-yellow-100 text-yellow-800
                                @elseif ($item->status_transaksi == 'dikemas') bg-blue-100 text-blue-800
                                @elseif ($item->status_transaksi == 'dikirim') bg-indigo-100 text-indigo-800
                                @elseif ($item->status_transaksi == 'diterima') bg-green-100 text-green-800
                                @endif">
                                {{ $statuses[$item->status_transaksi] ?? $item->status_transaksi }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            {{-- Status Update Form --}}
                            @if ($item->status_transaksi == 'menunggu_pembayaran')
                                {{-- Typically, 'menunggu_pembayaran' would be handled by buyer payment confirmation,
                                     but if seller can initiate packing, uncomment below --}}
                                {{-- <form action="{{ route('penjual.transaksi.updateStatus', $item->id) }}" method="POST" class="inline-block mr-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="dikemas">
                                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded-full text-xs">
                                        Kemas
                                    </button>
                                </form> --}}
                            @elseif ($item->status_transaksi == 'dikemas')
                                <form action="{{ route('penjual.transaksi.updateStatus', $item->id) }}" method="POST" class="inline-block mr-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="dikirim">
                                    <button type="submit" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-1 px-3 rounded-full text-xs">
                                        Kirim
                                    </button>
                                </form>
                            @endif

                            {{-- Details Button (Modal or new page) --}}
                            <button onclick="openDetailsModal({{ json_encode($item->details) }}, '{{ $item->id }}')"
                                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-3 rounded-full text-xs">
                                Details
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center px-6 py-4 text-gray-500">Belum ada transaksi pada status ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination Links --}}
        <div class="px-6 py-4">
            {{ $transaksis->links() }}
        </div>
    </div>
</div>

{{-- Details Modal (using a simple JavaScript approach) --}}
<div id="detailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-auto relative">
        <h2 class="text-xl font-bold mb-4">Detail Pesanan #<span id="modalOrderId"></span></h2>
        <button onclick="closeDetailsModal()" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</button>
        <div id="modalDetailsContent" class="mb-4">
            {{-- Product details will be injected here by JavaScript --}}
        </div>
        <div class="flex justify-end">
            <button onclick="closeDetailsModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">Tutup</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    console.log('Echo object:', window.Echo);

    if (window.Echo) {
        console.log('Echo found, attempting to connect...');

        window.Echo.private('user.{{ auth()->id() }}')
            .listen('.transaction.status.updated', (e) => {
                console.log('🎉 Event status update diterima!', e);

                const oldStatusCounter = document.getElementById(`status-count-${e.old_status}`);
                const newStatusCounter = document.getElementById(`status-count-${e.new_status}`);

                if (oldStatusCounter) {
                    let count = parseInt(oldStatusCounter.textContent);
                    oldStatusCounter.textContent = count > 0 ? count - 1 : 0;
                }
                if (newStatusCounter) {
                    let count = parseInt(newStatusCounter.textContent);
                    newStatusCounter.textContent = count + 1;
                }

                const transactionRow = document.getElementById(`transaksi-row-${e.transaction_id}`);
                if (transactionRow) {
                    // Beri efek fade out agar perpindahan lebih mulus
                    transactionRow.style.transition = 'opacity 0.5s ease-out';
                    transactionRow.style.opacity = '0';

                    // Hapus elemen dari DOM setelah animasi selesai
                    setTimeout(() => {
                        transactionRow.remove();
                    }, 500);
                }
                alert(`Pesanan #${e.transaction_id} statusnya diupdate menjadi "${e.new_status_label}"`);
            })
            .error((error) => {
                console.error('❌ Echo Gagal terhubung ke channel:', error);
            });
    } else {
        console.error('❌ Echo tidak ditemukan! Pastikan aset (app.js) sudah di-build dan di-load.');
    }

    function openDetailsModal(details, orderId) {
        const modal = document.getElementById('detailsModal');
        const modalOrderId = document.getElementById('modalOrderId');
        const modalContent = document.getElementById('modalDetailsContent');

        modalOrderId.textContent = orderId;
        modalContent.innerHTML = ''; // Clear previous content

        if (details && details.length > 0) {
            let html = '<ul class="list-disc pl-5 space-y-2">';
            details.forEach(detail => {
                // Check if 'product' property exists before trying to access 'nama_barang'
                const productName = detail.product ? detail.product.nama_barang : 'Produk tidak ditemukan';
                html += `
                    <li>
                        <strong>${productName}</strong><br>
                        Jumlah: ${detail.jumlah}<br>
                        Subtotal: Rp ${new Intl.NumberFormat('id-ID').format(detail.subtotal)}
                    </li>
                `;
            });
            html += '</ul>';
            modalContent.innerHTML = html;
        } else {
            modalContent.innerHTML = '<p class="text-gray-600">Tidak ada detail produk untuk transaksi ini.</p>';
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex'); // To show it with flexbox
    }

    function closeDetailsModal() {
        const modal = document.getElementById('detailsModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Close modal if clicked outside
    window.onclick = function(event) {
        const modal = document.getElementById('detailsModal');
        if (event.target == modal) {
            closeDetailsModal();
        }
    }
</script>
@endpush
@endsection