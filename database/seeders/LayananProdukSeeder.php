<?php

namespace Database\Seeders;

use App\Models\Layanan;
use App\Models\Produk;
use Illuminate\Database\Seeder;

class LayananProdukSeeder extends Seeder
{
    public function run(): void
    {
        $produkMap = Produk::pluck('id', 'nama_produk')->toArray();

        $links = [
            'Basic Color'             => ['Milbon Color', 'Alfaparf Color', 'Keaune Color', 'Milbon Oxidant', 'Alfaparf Oxidant', 'Keaune Oxidant'],
            // Color + Bleaching (default produk warna memakai bleaching)
            'Basic Color Two Apply'   => ['Milbon Color', 'Alfaparf Color', 'Keaune Color', 'Milbon Bleaching', 'Alfaparf Bleaching', 'Keaune Bleaching'],
            'Refresh Color Treatment' => ['Milbon Color', 'Alfaparf Color', 'Keaune Color', 'Milbon Oxidant', 'Alfaparf Oxidant', 'Keaune Oxidant'],
            'Fashion Color'           => ['Milbon Color', 'Alfaparf Color', 'Keaune Color', 'Milbon Bleaching', 'Alfaparf Bleaching', 'Keaune Bleaching'],
            'Cat Akar'                => ['Milbon Color', 'Alfaparf Color', 'Keaune Color', 'Milbon Bleaching', 'Alfaparf Bleaching', 'Keaune Bleaching'],
            'Peek A Boo'              => ['Milbon Color', 'Alfaparf Color', 'Keaune Color', 'Milbon Bleaching', 'Alfaparf Bleaching', 'Keaune Bleaching'],
            'Bleach Akar'             => ['Milbon Bleaching', 'Alfaparf Bleaching', 'Keaune Bleaching', 'Milbon Oxidant', 'Alfaparf Oxidant', 'Keaune Oxidant'],
            'Highlight'               => ['Milbon Bleaching', 'Alfaparf Bleaching', 'Keaune Bleaching', 'Milbon Oxidant', 'Alfaparf Oxidant', 'Keaune Oxidant'],
            'Keratin Premium'         => ['Alfaparf Keratin', 'Omni Keratin'],
            'Smoothing'               => ['Milbon Smoothing'],
            'Creambath'               => ['Alfaparf Creambath', 'Matrix Creambath', 'Alfaparf Hairtreatment Complete'],
            'Hairmask'                => ['Alfaparf Creambath', 'Matrix Creambath', 'Alfaparf Hairtreatment Complete'],
            'Treatment Komplit'       => ['Alfaparf Creambath', 'Matrix Creambath', 'Alfaparf Hairtreatment Complete', 'Alfaparf Keratin'],
            'Hair Detox'              => ['Alfaparf Hairtreatment Complete'],
        ];

        foreach ($links as $namaLayanan => $produkNames) {
            $layanan = Layanan::where('nama_layanan', $namaLayanan)->first();
            if (!$layanan) continue;

            $produkIds = array_filter(array_map(fn ($n) => $produkMap[$n] ?? null, $produkNames));
            $layanan->produk()->syncWithoutDetaching($produkIds);
        }
    }
}
