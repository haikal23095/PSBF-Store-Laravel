@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Proses Pembayaran</h1>

    <div class="bg-white shadow rounded-lg p-6 text-center">
        <p class="text-lg text-gray-700 mb-4">Transaksi Anda dengan ID: <span class="font-semibold">{{ $transaksi->id }}</span> telah berhasil dibuat.</p>
        <p class="text-lg text-gray-700 mb-6">Total yang harus dibayar: <span class="font-bold text-indigo-600">Rp {{ number_format($transaksi->total_harga, 0, ',', '.') }}</span></p>

        <button id="pay-button" class="px-8 py-4 bg-green-600 text-white text-xl font-bold rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">Bayar Sekarang</button>

        <div class="mt-8 text-sm text-gray-600">
            <p>Silakan klik tombol "Bayar Sekarang" untuk melanjutkan pembayaran melalui Midtrans.</p>
            <p>Setelah pembayaran selesai, Anda akan diarahkan kembali ke halaman ini atau ke riwayat transaksi Anda.</p>
        </div>
    </div>
</div>

{{-- 
    ================================================================
    == PERUBAHAN ==
    URL skrip sekarang di-hardcode ke URL produksi Midtrans.
    ================================================================
--}}
<script type="text/javascript" 
    src="{{ config('services.midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
    data-client-key="{{ config('services.midtrans.client_key') }}"
></script>

<script type="text/javascript">
    // Gunakan window.onload untuk memastikan SEMUA konten, termasuk skrip eksternal, dimuat sepenuhnya.
    window.onload = function() {
        const payButton = document.getElementById('pay-button');
        if (payButton) {
            payButton.addEventListener('click', function() {
                // PENTING: Pastikan snapToken diteruskan dengan benar dari Laravel.
                const snapToken = '{{ $snapToken }}';

                // Periksa apakah objek Snap sudah terdefinisi sebelum memanggil pay
                if (typeof snap !== 'undefined' && snap.pay) {
                    snap.pay(snapToken, {
                        onSuccess: function(result){
                            showCustomAlert("Pembayaran Berhasil!");
                            console.log(result);
                            window.location.href = "{{ route('pembeli.transaksi.show', $transaksi->id) }}"; // Redirect ke detail transaksi
                        },
                        onPending: function(result){
                            showCustomAlert("Pembayaran Pending!");
                            console.log(result);
                            window.location.href = "{{ route('pembeli.transaksi.show', $transaksi->id) }}"; // Redirect ke detail transaksi
                        },
                        onError: function(result){
                            showCustomAlert("Pembayaran Gagal!");
                            console.log(result);
                            window.location.href = "{{ route('pembeli.transaksi.show', $transaksi->id) }}"; // Redirect ke detail transaksi
                        },
                        onClose: function(){
                            showCustomAlert('Anda menutup popup tanpa menyelesaikan pembayaran');
                        }
                    });
                } else {
                    console.error("Midtrans Snap object is not defined. Please ensure Snap.js loaded correctly.");
                    const snapScript = document.querySelector('script[src*="snap.js"]');
                    if (snapScript) {
                        console.error("Snap.js script tag found. Src: " + snapScript.src + ", Client Key: " + snapScript.getAttribute('data-client-key'));
                    } else {
                        console.error("Snap.js script tag NOT found in the DOM.");
                    }
                    showCustomAlert("Terjadi kesalahan pada pembayaran. Silakan coba lagi.");
                }
            });
        }
    };

    // Fungsi untuk menampilkan alert kustom
    function showCustomAlert(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center z-50';
        alertDiv.innerHTML = `
            <div class="bg-white p-6 rounded-lg shadow-xl text-center max-w-sm w-full mx-4">
                <p class="text-lg font-semibold text-gray-800 mb-4">${message}</p>
                <button id="custom-alert-ok" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">OK</button>
            </div>
        `;
        document.body.appendChild(alertDiv);

        document.getElementById('custom-alert-ok').addEventListener('click', function() {
            document.body.removeChild(alertDiv);
        });
    }
</script>
@endsection