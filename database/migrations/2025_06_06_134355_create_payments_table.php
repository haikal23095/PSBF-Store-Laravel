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
    Schema::create('payments', function (Blueprint $table) {
        $table->id('payment_id');
        $table->string('payment_method');
        $table->enum('status_payment', ['pending', 'paid']);
        $table->unsignedBigInteger('transaksi_id');
        $table->timestamps();

        $table->foreign('transaksi_id')->references('id')->on('transaksis')->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
