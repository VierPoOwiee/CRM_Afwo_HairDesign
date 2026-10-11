<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_transaksi_produk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_detail_transaksi')->constrained('detail_transaksi')->cascadeOnDelete();
            $table->foreignId('id_produk')->constrained('produk');
            $table->decimal('pemakaian_ml', 8, 2)->default(0);
            $table->decimal('harga_per_unit', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();

            $table->index('id_detail_transaksi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_transaksi_produk');
    }
};
