<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('transaksis', function (Blueprint $table) {
        $table->id('id');
        $table->integer('total_harga');
        $table->timestamp('tanggal_transaksi')->useCurrent();
        $table->unsignedBigInteger('payment_id')->nullable();
        $table->enum('status_transaksi', ['menunggu pembayaran', 'dikemas', 'dikirim', 'diterima']);
        $table->unsignedBigInteger('user_id');
        $table->timestamps();

        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};