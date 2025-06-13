<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name' => 'penjual_01',
                'email' => 'penjual@example.com',
                'roles' => 'penjual',
                'password' => Hash::make('password123'),
                'alamat' => 'Jl. Penjual No. 1, Jakarta',
                'no_telp' => '081234567890',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'pembeli_01',
                'email' => 'pembeli@example.com',
                'roles' => 'pembeli',
                'password' => Hash::make('password456'),
                'alamat' => 'Jl. Pembeli No. 2, Bandung',
                'no_telp' => '082345678901',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
