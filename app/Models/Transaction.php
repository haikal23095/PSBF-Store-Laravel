<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transaksis';

    protected $fillable = ['user_id', 'total_harga', 'tanggal_transaksi', 'status_transaksi', 'payment_id'];

    public function details()
    {
        return $this->hasMany(TransactionDetail::class, 'transaksi_id');
    }

    public function pembeli()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
