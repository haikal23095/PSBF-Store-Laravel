<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $primaryKey = 'payment_id';

    protected $fillable = [
        'payment_method',
        'status_payment',
        'transaksi_id',
    ];

    // If 'status_payment' needs to be casted to an enum, you can add it here.
    // protected $casts = [
    //     'status_payment' => PaymentStatusEnum::class, // Example if you have an enum class
    // ];

    public function transaksi()
    {
        return $this->belongsTo(Transaction::class, 'transaksi_id', 'id');
    }
}