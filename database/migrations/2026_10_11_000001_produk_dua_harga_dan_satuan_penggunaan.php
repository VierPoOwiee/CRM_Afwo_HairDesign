<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Produk kini punya DUA harga: harga_per_pcs (jual retail) dan
// harga_per_penggunaan (dipakai saat layanan, mis. per 10ml). Satuan
// penggunaan bisa diatur bebas per produk. Opsi kategori "dijual" dan
// "dipakai_layanan" dihapus; produk lama dipetakan ke kategori layanan.
return new class extends Migration
{
    public function up(): void
    {
        // 1) Kolom harga baru
        Schema::table('produk', function (Blueprint $table) {
            $table->decimal('harga_per_pcs', 15, 2)->default(0)->after('harga_per_satuan');
            $table->decimal('harga_per_penggunaan', 15, 2)->default(0)->after('harga_per_pcs');
        });

        // 2) Backfill harga lama ke kolom yang sesuai
        DB::statement("UPDATE produk SET harga_per_pcs = harga_per_satuan WHERE kategori_produk = 'dijual'");
        DB::statement("UPDATE produk SET harga_per_penggunaan = harga_per_satuan WHERE kategori_produk <> 'dijual'");

        // 3) Petakan kategori lama ('dijual' & 'dipakai_layanan') ke kategori layanan
        //    spesifik berdasarkan nama produk.
        $keywordMap = [
            'Serum' => 'Serum',
            'Masker' => 'Masker',
            'Repair' => 'Repair',
            'Keriting' => 'Keriting',
            'Color' => 'Color',
            'Bleaching' => 'Bleaching',
            'Oxidant' => 'Oxidant',
            'Keratin' => 'Keratin',
            'Smoothing' => 'Smoothing',
            'Creambath' => 'Creambath',
            'Hairtreatment' => 'Hairtreatment',
        ];
        $rows = DB::table('produk')
            ->select('id', 'nama_produk')
            ->whereIn('kategori_produk', ['dijual', 'dipakai_layanan'])
            ->get();
        foreach ($rows as $row) {
            $kategori = 'Hairtreatment';
            foreach ($keywordMap as $needle => $target) {
                if (stripos((string) $row->nama_produk, $needle) !== false) {
                    $kategori = $target;
                    break;
                }
            }
            DB::table('produk')->where('id', $row->id)->update(['kategori_produk' => $kategori]);
        }

        // 4) Kecilkan enum kategori (hapus 'dijual' & 'dipakai_layanan')
        DB::statement("ALTER TABLE produk MODIFY kategori_produk ENUM('Color','Bleaching','Oxidant','Keratin','Smoothing','Hairtreatment','Creambath','Serum','Repair','Keriting','Masker') NOT NULL DEFAULT 'Color'");

        // 5) Ganti kolom satuan -> satuan_penggunaan (teks bebas, default /10ml)
        DB::statement("ALTER TABLE produk CHANGE satuan satuan_penggunaan VARCHAR(50) NOT NULL DEFAULT '/10ml'");
        DB::table('produk')->where('satuan_penggunaan', 'pcs')->update(['satuan_penggunaan' => '/10ml']);
        DB::table('produk')->whereNull('satuan_penggunaan')->update(['satuan_penggunaan' => '/10ml']);

        // 6) Buang kolom harga lama
        Schema::table('produk', function (Blueprint $table) {
            $table->dropColumn('harga_per_satuan');
        });
    }

    public function down(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->decimal('harga_per_satuan', 15, 2)->default(0)->after('kategori_produk');
        });
        DB::statement('UPDATE produk SET harga_per_satuan = GREATEST(harga_per_pcs, harga_per_penggunaan)');

        // Perlebar enum dulu supaya nilai 'dijual' bisa dipakai.
        DB::statement("ALTER TABLE produk MODIFY kategori_produk ENUM('dijual','dipakai_layanan','Color','Bleaching','Oxidant','Keratin','Smoothing','Hairtreatment','Creambath','Serum','Repair','Keriting','Masker') NOT NULL DEFAULT 'dipakai_layanan'");

        DB::statement("ALTER TABLE produk CHANGE satuan_penggunaan satuan ENUM('pcs','/10ml') NOT NULL DEFAULT '/10ml'");
        DB::table('produk')->where('harga_per_pcs', '>', 0)->update(['satuan' => 'pcs', 'kategori_produk' => 'dijual']);

        Schema::table('produk', function (Blueprint $table) {
            $table->dropColumn(['harga_per_pcs', 'harga_per_penggunaan']);
        });
    }
};
