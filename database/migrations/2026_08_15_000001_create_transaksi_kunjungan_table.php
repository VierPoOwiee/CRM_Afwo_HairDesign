<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_kunjungan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pelanggan')->constrained('pelanggans');
            $table->enum('jenis_pengerjaan', ['sendiri', 'berdua'])->default('sendiri');
            $table->string('no_struk')->unique();
            $table->datetime('waktu_kunjungan');
            $table->decimal('total_bayar', 15, 2)->default(0);
            $table->enum('metode_pembayaran', ['cash', 'qris', 'debit', 'kartu_kredit', 'transfer']);
            $table->enum('status', ['selesai', 'batal'])->default('selesai');
            $table->timestamps();

            $table->index('no_struk');
            $table->index('waktu_kunjungan');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_kunjungan');
    }
};
