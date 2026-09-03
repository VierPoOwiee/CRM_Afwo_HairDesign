<?php

namespace Database\Seeders;

use App\Models\DefaultProdukLayanan;
use App\Models\HargaLayanan;
use App\Models\Layanan;
use Illuminate\Database\Seeder;

// ============================================================
// DUMMY default produk per varian layanan (belum final!).
// Nilai berikut perkiraan masuk akal dari notes lama (mis.
// "80/100 ml") + aturan 'warna pasti pakai bleaching (2 produk)'.
// Sesuaikan nilai kategori/ml-nya sebelum dipakai resmi ke form.
// Varian yang TIDAK terdaftar di sini = pakai logika lama
// (harga dasar, tanpa auto-fill produk).
// ============================================================
class DefaultProdukLayananSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Fashion Color' => [
                'S'  => [['Color', 80], ['Bleaching', 100]],
                'M'  => [['Color', 100], ['Bleaching', 140]],
                'L'  => [['Color', 120], ['Bleaching', 160]],
                'XL' => [['Color', 150], ['Bleaching', 200]],
            ],
            'Highlight' => [
                'S'  => [['Bleaching', 60], ['Color', 40]],
                'M'  => [['Bleaching', 80], ['Color', 60]],
                'L'  => [['Bleaching', 100], ['Color', 80]],
                'XL' => [['Bleaching', 120], ['Color', 100]],
            ],
            'Basic Color Two Apply' => [
                'default' => [['Color', 150], ['Bleaching', 100]],
            ],
            'Peek A Boo' => [
                'default' => [['Color', 80], ['Bleaching', 80]],
            ],
            'Cat Akar' => [
                'default' => [['Color', 60], ['Bleaching', 20]],
            ],
            'Bleach Akar' => [
                'default' => [['Bleaching', 80]],
            ],

            // ===== Layanan 1 produk: harga dihitung otomatis dari pemakaian =====
            // Nilai dummy (belum final!) — sesuaikan sebelum dipakai resmi.
            'Keratin Premium' => [
                'S'  => [['Keratin', 80]],
                'M'  => [['Keratin', 90]],
                'L'  => [['Keratin', 100]],
                'XL' => [['Keratin', 130]],
            ],
            'Smoothing' => [
                'Akar' => [['Smoothing', 60]],
                'S'    => [['Smoothing', 80]],
                'M'    => [['Smoothing', 100]],
                'L'    => [['Smoothing', 120]],
                'XL'   => [['Smoothing', 150]],
            ],
            'Creambath' => [
                'default' => [['Creambath', 60]],
            ],
            'Hairmask' => [
                'default' => [['Creambath', 60]],
            ],
            'Treatment Komplit' => [
                'default' => [
                    ['Hairtreatment', 80],
                    ['Creambath', 60],
                ],
            ],
            'Hair Detox' => [
                'default' => [['Hairtreatment', 60]],
            ],
        ];

        foreach ($data as $namaLayanan => $varianMap) {
            $layanan = Layanan::where('nama_layanan', $namaLayanan)->first();
            if (! $layanan) {
                continue;
            }

            foreach ($varianMap as $varian => $rows) {
                $hgl = HargaLayanan::where('id_layanan', $layanan->id)
                    ->where('varian', $varian)
                    ->first();

                if (! $hgl) {
                    continue;
                }

                foreach ($rows as [$kategori, $ml]) {
                    DefaultProdukLayanan::updateOrCreate(
                        ['id_harga_layanan' => $hgl->id, 'kategori_produk' => $kategori],
                        ['default_ml' => $ml]
                    );
                }
            }
        }
    }
}