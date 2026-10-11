<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Model stok "netto per pcs":
//  - produk.netto          : isi bersih per 1 pcs (mis. 100 ml / gram).
//  - produk.sisa_penggunaan: sisa isi pada kemasan yang sedang dipakai (unit netto).
//  - detail_transaksi_produk mencatat berapa pcs yang terpotong saat pemakaian
//    layanan + sisa sebelum/sesudah agar bisa dikembalikan persis saat batal/edit.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->decimal('netto', 10, 2)->nullable()->after('satuan_penggunaan');
            $table->decimal('sisa_penggunaan', 10, 2)->default(0)->after('netto');
        });

        Schema::table('detail_transaksi_produk', function (Blueprint $table) {
            $table->decimal('stok_terpotong', 8, 2)->default(0)->after('subtotal');
            $table->decimal('sisa_sebelum', 10, 2)->nullable()->after('stok_terpotong');
            $table->decimal('sisa_sesudah', 10, 2)->nullable()->after('sisa_sebelum');
        });
    }

    public function down(): void
    {
        Schema::table('detail_transaksi_produk', function (Blueprint $table) {
            $table->dropColumn(['stok_terpotong', 'sisa_sebelum', 'sisa_sesudah']);
        });

        Schema::table('produk', function (Blueprint $table) {
            $table->dropColumn(['netto', 'sisa_penggunaan']);
        });
    }
};
