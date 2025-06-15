<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany; // <-- Ditambahkan untuk type-hinting
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// Model 'Transaksi' sudah di-import dengan benar
use App\Models\Transaksi;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        // Tambahkan atribut lain dari database Anda jika perlu
        'roles',
        'alamat',
        'no_telp',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Mendefinisikan relasi ke model Transaksi.
     * Satu User (Pembeli) dapat memiliki banyak transaksi.
     */
    public function transaksis(): HasMany
    {
        // PERBAIKAN: Mengganti Transaction::class menjadi Transaksi::class
        return $this->hasMany(Transaction::class, 'user_id');
    }
}
