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
{   if (!Schema::hasTable('transaksi_details')){
    Schema::create('transaksi_details', function (Blueprint $table) {
        $table->id('id');
        $table->unsignedBigInteger('product_id');
        $table->integer('jumlah');
        $table->integer('subtotal');
        $table->unsignedBigInteger('transaksi_id');
        $table->timestamps();

        $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        $table->foreign('transaksi_id')->references('id')->on('transaksis')->onDelete('cascade');
    });
}
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_details');
    }
};