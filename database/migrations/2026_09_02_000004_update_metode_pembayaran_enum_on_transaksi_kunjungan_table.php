<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Perluas enum agar nilai baru diterima terlebih dahulu.
        DB::statement("ALTER TABLE transaksi_kunjungan MODIFY COLUMN metode_pembayaran ENUM('cash','qris','qris_bni','qris_bri','debit','kartu_kredit','transfer') NOT NULL");

        // 2) Data QRIS lama dialihkan ke QRIS BNI agar tidak hilang.
        DB::table('transaksi_kunjungan')
            ->where('metode_pembayaran', 'qris')
            ->update(['metode_pembayaran' => 'qris_bni']);

        // 3) Hapus nilai 'qris' lama dari enum.
        DB::statement("ALTER TABLE transaksi_kunjungan MODIFY COLUMN metode_pembayaran ENUM('cash','qris_bni','qris_bri','debit','kartu_kredit','transfer') NOT NULL");
    }

    public function down(): void
    {
        DB::table('transaksi_kunjungan')
            ->whereIn('metode_pembayaran', ['qris_bni', 'qris_bri'])
            ->update(['metode_pembayaran' => 'qris']);

        DB::statement("ALTER TABLE transaksi_kunjungan MODIFY COLUMN metode_pembayaran ENUM('cash','qris','debit','kartu_kredit','transfer') NOT NULL");
    }
};