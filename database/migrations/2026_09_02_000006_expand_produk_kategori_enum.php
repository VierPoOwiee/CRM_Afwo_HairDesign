<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Perluas enum kategori_produk: produk 'dipakai_layanan' lama kini dipisah
// jadi tipe spesifik (Color, Bleaching, Oxidant, Keratin, Smoothing,
// Hairtreatment, Creambath) supaya bisa dipakai untuk auto-fill produk
// default per varian layanan (default_produk_layanan).
return new class extends Migration
{
    public function up(): void
    {
        $kategoriLayanan = ['Color', 'Bleaching', 'Oxidant', 'Keratin', 'Smoothing', 'Hairtreatment', 'Creambath'];

        Schema::table('produk', function (Blueprint $table) use ($kategoriLayanan) {
            $table->enum('kategori_produk', array_merge(['dijual', 'dipakai_layanan'], $kategoriLayanan))
                ->default('dipakai_layanan')
                ->change();
        });

        // Rekategorisasi data yang sudah ada (dari seeder lama) berdasarkan nama produk.
        $map = [
            'Color' => ['Milbon Color', 'Alfaparf Color', 'Keaune Color'],
            'Bleaching' => ['Milbon Bleaching', 'Alfaparf Bleaching', 'Keaune Bleaching'],
            'Oxidant' => ['Milbon Oxidant', 'Alfaparf Oxidant', 'Keaune Oxidant'],
            'Keratin' => ['Alfaparf Keratin', 'Omni Keratin'],
            'Smoothing' => ['Milbon Smoothing'],
            'Hairtreatment' => ['Alfaparf Hairtreatment Complete'],
            'Creambath' => ['Alfaparf Creambath', 'Matrix Creambath'],
        ];

        foreach ($map as $kategori => $namaList) {
            DB::table('produk')->whereIn('nama_produk', $namaList)->update(['kategori_produk' => $kategori]);
        }
    }

    public function down(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->enum('kategori_produk', ['dijual', 'dipakai_layanan'])->default('dipakai_layanan')->change();
        });
    }
};