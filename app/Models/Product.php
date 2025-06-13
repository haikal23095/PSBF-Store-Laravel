<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    // Menentukan primary key
    protected $primaryKey = 'product_id';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_barang',
        'kategori',
        'stok',
        'harga',
        'deskripsi',
        'gambar',
        'user_id',
    ];

    /**
     * Mendapatkan user yang memiliki produk ini.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}