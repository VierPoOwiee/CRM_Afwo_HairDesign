<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_transaksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_transaksi')->constrained('transaksi_kunjungan')->cascadeOnDelete();
            $table->foreignId('id_staf')->nullable()->constrained('karyawans');
            $table->enum('tipe_item', ['layanan', 'produk']);
            $table->foreignId('id_layanan')->nullable()->constrained('layanan');
            $table->foreignId('id_produk')->nullable()->constrained('produk');
            $table->string('varian_dipilih')->nullable();
            $table->string('ketebalan_rambut')->nullable();
            $table->decimal('gram_pemakaian_tambahan', 8, 2)->default(0);
            $table->decimal('qty', 8, 2)->default(1);
            $table->decimal('harga_saat_transaksi', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->decimal('komisi_nominal', 15, 2)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['id_transaksi', 'tipe_item']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_transaksi');
    }
};
