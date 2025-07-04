<?php

namespace App\Events;

use App\Models\Transaction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Transaction $transaction;
    public string $oldStatus;

    /**
     * Create a new event instance.
     *
     * @param Transaction $transaction
     * @param string $oldStatus // Tambahkan parameter ini
     */
    public function __construct(Transaction $transaction, string $oldStatus)
    {
        $this->transaction = $transaction;
        $this->oldStatus = $oldStatus;
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'transaction_id' => $this->transaction->id,
            'old_status'     => $this->oldStatus,
            'new_status'     => $this->transaction->status_transaksi,
            'new_status_label' => str_replace('_', ' ', ucwords($this->transaction->status_transaksi, '_')),
        ];
    }

    public function broadcastOn(): array
    {
        // 1. Ambil semua ID penjual yang unik dari detail transaksi
        $sellerIds = $this->transaction->details()
            ->with('product.user') // Eager load untuk efisiensi query
            ->get()
            ->pluck('product.user.id') // Ambil semua ID user (penjual) dari relasi produk
            ->unique() // Pastikan setiap ID penjual hanya ada satu
            ->filter() // Hapus nilai null jika ada
            ->all();

        // 2. Siapkan array untuk semua channel yang akan di-broadcast
        $channels = [];

        // 3. Tambahkan channel privat untuk PEMBELI
        $channels[] = new PrivateChannel('user.' . $this->transaction->user_id);

        // 4. Tambahkan channel privat untuk setiap PENJUAL yang terlibat
        foreach ($sellerIds as $sellerId) {
            // Hindari mengirim notifikasi ganda jika pembeli adalah penjualnya sendiri (kasus langka)
            if ($sellerId != $this->transaction->user_id) {
                $channels[] = new PrivateChannel('user.' . $sellerId);
            }
        }

        // 5. Kembalikan semua channel yang sudah dikumpulkan
        return $channels;
    }
    public function broadcastAs(): string
    {
        return 'transaction.status.updated';
    }
}
