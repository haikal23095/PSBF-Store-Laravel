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


    // Create a new event instance.
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
        return [new PrivateChannel('user.' . $this->transaction->user_id)];
    }
    public function broadcastAs(): string
    {
        return 'transaction.status.updated';
    }
}
