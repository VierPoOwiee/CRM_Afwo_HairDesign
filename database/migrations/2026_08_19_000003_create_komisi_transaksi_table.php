<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('komisi_transaksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_transaksi')->constrained('transaksi_kunjungan')->cascadeOnDelete();
            $table->foreignId('id_staf')->constrained('karyawans')->cascadeOnDelete();
            $table->decimal('jumlah_komisi', 15, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['id_transaksi', 'id_staf']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('komisi_transaksi');
    }
};
