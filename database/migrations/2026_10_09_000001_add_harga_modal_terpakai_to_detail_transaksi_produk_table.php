<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom harga_modal_terpakai pada detail_transaksi_produk tidak pernah
     * terpasang karena bagian terkait ditambahkan ke migration lama
     * (2026_09_18_000001_add_modal_produk_tables) setelah migration tersebut
     * sudah tercatat dijalankan.
     */
    public function up(): void
    {
        Schema::table('detail_transaksi_produk', function (Blueprint $table) {
            if (! Schema::hasColumn('detail_transaksi_produk', 'harga_modal_terpakai')) {
                $table->decimal('harga_modal_terpakai', 15, 2)->nullable()->after('harga_per_unit');
            }
        });
    }

    public function down(): void
    {
        Schema::table('detail_transaksi_produk', function (Blueprint $table) {
            if (Schema::hasColumn('detail_transaksi_produk', 'harga_modal_terpakai')) {
                $table->dropColumn('harga_modal_terpakai');
            }
        });
    }
};