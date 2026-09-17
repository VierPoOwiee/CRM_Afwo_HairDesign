<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pencatatan modal produk (harga beli/restock) untuk laporan keuntungan produk.
     *
     * - pembelian_produk       : riwayat pembelian/restock tiap produk (harga beli per satuan).
     * - riwayat_stok_produk    : jejak perubahan stok (restock, penjualan, pemakaian layanan, batalkan transaksi).
     * - produk.harga_modal_rata_rata : weighted average cost per satuan, diperbarui otomatis saat restock.
     * - detail_transaksi       : snapshot modal + keuntungan untuk penjualan produk retail.
     * - detail_transaksi_produk: snapshot modal terpakai untuk produk yang dipakai saat layanan.
     */
    public function up(): void
    {
        Schema::create('pembelian_produk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('produk')->cascadeOnDelete();
            $table->integer('qty');
            $table->decimal('harga_beli', 15, 2);
            $table->decimal('harga_modal_rata_rata', 15, 2)->default(0);
            $table->integer('stok_setelah');
            $table->text('keterangan')->nullable();
            $table->foreignId('dicatat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->date('tanggal');
            $table->timestamps();

            $table->index(['produk_id', 'tanggal']);
        });

        Schema::create('riwayat_stok_produk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('produk')->cascadeOnDelete();
            $table->enum('jenis', ['restock', 'penjualan', 'pemakaian_layanan', 'batal', 'penyesuaian'])->default('penyesuaian');
            $table->decimal('perubahan', 15, 2);
            $table->decimal('stok_akhir', 15, 2);
            $table->decimal('harga_modal_rata_rata', 15, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->foreignId('dicatat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('tanggal');
            $table->timestamps();

            $table->index(['produk_id', 'tanggal']);
        });

        Schema::table('produk', function (Blueprint $table) {
            if (! Schema::hasColumn('produk', 'harga_modal_rata_rata')) {
                $table->decimal('harga_modal_rata_rata', 15, 2)->default(0)->after('harga_per_satuan');
            }
        });

        Schema::table('detail_transaksi', function (Blueprint $table) {
            if (! Schema::hasColumn('detail_transaksi', 'harga_modal_satuan')) {
                $table->decimal('harga_modal_satuan', 15, 2)->nullable()->after('harga_saat_transaksi');
            }
            if (! Schema::hasColumn('detail_transaksi', 'keuntungan')) {
                $table->decimal('keuntungan', 15, 2)->nullable()->after('harga_modal_satuan');
            }
            $table->index(['id_produk', 'tipe_item']);
        });

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

        Schema::table('detail_transaksi', function (Blueprint $table) {
            if (Schema::hasIndex('detail_transaksi', 'detail_transaksi_id_produk_tipe_item_index')) {
                $table->dropIndex('detail_transaksi_id_produk_tipe_item_index');
            }
            if (Schema::hasColumn('detail_transaksi', 'keuntungan')) {
                $table->dropColumn('keuntungan');
            }
            if (Schema::hasColumn('detail_transaksi', 'harga_modal_satuan')) {
                $table->dropColumn('harga_modal_satuan');
            }
        });

        Schema::table('produk', function (Blueprint $table) {
            if (Schema::hasColumn('produk', 'harga_modal_rata_rata')) {
                $table->dropColumn('harga_modal_rata_rata');
            }
        });

        Schema::dropIfExists('riwayat_stok_produk');
        Schema::dropIfExists('pembelian_produk');
    }
};