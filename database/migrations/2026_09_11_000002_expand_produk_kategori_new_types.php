<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Tambah kategori produk baru: Serum, Repair, Keriting, Masker.
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE produk MODIFY kategori_produk ENUM('dijual','dipakai_layanan','Color','Bleaching','Oxidant','Keratin','Smoothing','Hairtreatment','Creambath','Serum','Repair','Keriting','Masker') NOT NULL DEFAULT 'dipakai_layanan'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE produk MODIFY kategori_produk ENUM('dijual','dipakai_layanan','Color','Bleaching','Oxidant','Keratin','Smoothing','Hairtreatment','Creambath') NOT NULL DEFAULT 'dipakai_layanan'");
    }
};