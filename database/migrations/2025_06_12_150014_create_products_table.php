<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id('id'); // Menggunakan id() yang setara dengan bigIncrements('product_id')
            $table->string('nama_barang');
            $table->enum('kategori', ['hp', 'laptop', 'printer', 'kamera']);
            $table->integer('stok');
            $table->integer('harga');
            $table->text('deskripsi');
            $table->string('gambar')->nullable(); // Menyimpan path ke gambar, bisa null
            $table->unsignedBigInteger('user_id'); // Foreign key ke tabel users
            $table->timestamps(); // Kolom created_at dan updated_at

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};