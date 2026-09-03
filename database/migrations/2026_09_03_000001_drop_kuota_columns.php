<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('harga_layanan', 'included_products')) {
            Schema::table('harga_layanan', function (Blueprint $table) {
                $table->dropColumn('included_products');
            });
        }

        Schema::table('detail_transaksi_produk', function (Blueprint $table) {
            if (Schema::hasColumn('detail_transaksi_produk', 'included_ml')) {
                $table->dropColumn('included_ml');
            }
            if (Schema::hasColumn('detail_transaksi_produk', 'extra_ml')) {
                $table->dropColumn('extra_ml');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('harga_layanan', 'included_products')) {
            Schema::table('harga_layanan', function (Blueprint $table) {
                $table->json('included_products')->nullable()->after('harga_dasar_max');
            });
        }

        Schema::table('detail_transaksi_produk', function (Blueprint $table) {
            if (! Schema::hasColumn('detail_transaksi_produk', 'included_ml')) {
                $table->decimal('included_ml', 8, 2)->nullable()->after('pemakaian_ml');
            }
            if (! Schema::hasColumn('detail_transaksi_produk', 'extra_ml')) {
                $table->decimal('extra_ml', 8, 2)->nullable()->after('included_ml');
            }
        });
    }
};