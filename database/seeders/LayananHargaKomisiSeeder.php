<?php
// ============================================================
// database/seeders/LayananHargaKomisiSeeder.php
//
// PENTING -- baca sebelum jalanin:
// Baris dengan komentar "KOMISI DIKONFIRMASI" = sesuai aturan yang kamu kasih langsung.
// Baris dengan komentar "ESTIMASI ~7-9%, CEK ULANG" = aku hitung otomatis dari harga
// karena belum ada aturan komisi eksplisit dari kamu untuk layanan ini.
// JANGAN dipakai ke produksi sebelum kamu review & sesuaikan angka-angka itu.
// ============================================================

namespace Database\Seeders;

use App\Models\Layanan;
use App\Models\HargaLayanan;
use Illuminate\Database\Seeder;

class LayananHargaKomisiSeeder extends Seeder
{
    public function run(): void
    {
        // helper kecil biar penulisan lebih ringkas
        $buat = function (string $nama, string $kategori, array $varianList, bool $termasukPotong = false) {
            $layanan = Layanan::create([
                'nama_layanan' => $nama,
                'kategori' => $kategori,
                'termasuk_potong' => $termasukPotong,
            ]);

            foreach ($varianList as $v) {
                HargaLayanan::create([
                    'id_layanan' => $layanan->id,
                    'varian' => $v['varian'],
                    'harga_dasar_min' => $v['harga_min'],
                    'harga_dasar_max' => $v['harga_max'] ?? $v['harga_min'],
                    'komisi_min' => $v['komisi_min'] ?? null,
                    'komisi_max' => $v['komisi_max'] ?? null,
                ]);
            }

            return $layanan;
        };

        // ===================== POTONG (excluded dari basis komisi Ari) =====================
        $buat('Potong Wanita', 'Potong', [
            ['varian' => 'default', 'harga_min' => 200000],
        ], true);
        $buat('Potong Pria', 'Potong', [
            ['varian' => 'default', 'harga_min' => 150000],
        ], true);
        $buat('Potong Anak', 'Potong', [
            ['varian' => 'default', 'harga_min' => 150000],
        ], true);
        $buat('Potong Poni', 'Potong', [
            ['varian' => 'default', 'harga_min' => 75000],
        ], true);
        $buat('Potong Wanita + Cuci + Blow', 'Potong', [
            ['varian' => 'default', 'harga_min' => 300000],
        ], true);
        $buat('Potong Pria + Cuci + Blow', 'Potong', [
            ['varian' => 'default', 'harga_min' => 150000],
        ], true);

        // ===================== STYLING (KOMISI DIKONFIRMASI) =====================
        $buat('Cuci Catok / Styling + Blow', 'Styling', [
            ['varian' => '125rb', 'harga_min' => 125000, 'komisi_min' => 20000, 'komisi_max' => 20000],
            ['varian' => '150rb', 'harga_min' => 150000, 'komisi_min' => 20000, 'komisi_max' => 20000],
            ['varian' => '175rb', 'harga_min' => 175000, 'komisi_min' => 25000, 'komisi_max' => 25000],
        ]);

        // ===================== KERATIN PREMIUM (KOMISI DIKONFIRMASI, sama rata semua ukuran) =====================
        $buat('Keratin Premium', 'Treatment Rambut', [
            ['varian' => 'S', 'harga_min' => 900000, 'komisi_min' => 60000, 'komisi_max' => 150000],
            ['varian' => 'M', 'harga_min' => 1300000, 'komisi_min' => 60000, 'komisi_max' => 150000],
            ['varian' => 'L', 'harga_min' => 1700000, 'komisi_min' => 60000, 'komisi_max' => 150000],
            ['varian' => 'XL', 'harga_min' => 2000000, 'komisi_min' => 60000, 'komisi_max' => 150000],
        ]);

        // ===================== SMOOTHING (ESTIMASI ~7-9%, CEK ULANG) =====================
        $buat('Smoothing', 'Treatment Rambut', [
            ['varian' => 'Akar', 'harga_min' => 450000, 'komisi_min' => 32000, 'komisi_max' => 41000],
            ['varian' => 'S', 'harga_min' => 500000, 'komisi_min' => 35000, 'komisi_max' => 45000],
            ['varian' => 'M', 'harga_min' => 650000, 'komisi_min' => 46000, 'komisi_max' => 59000],
            ['varian' => 'L', 'harga_min' => 750000, 'komisi_min' => 53000, 'komisi_max' => 68000],
            ['varian' => 'XL', 'harga_min' => 1000000, 'komisi_min' => 70000, 'komisi_max' => 90000],
        ]);

        // ===================== PERM (ESTIMASI ~7-9%, CEK ULANG) =====================
        $buat('Perm Akar', 'Treatment Rambut', [
            ['varian' => 'default', 'harga_min' => 450000, 'komisi_min' => 32000, 'komisi_max' => 41000],
        ]);
        $buat('Blow Perm', 'Treatment Rambut', [
            ['varian' => 'default', 'harga_min' => 850000, 'harga_max' => 1500000, 'komisi_min' => 60000, 'komisi_max' => 135000],
        ]);
        $buat('Cold Perm', 'Treatment Rambut', [
            ['varian' => 'default', 'harga_min' => 500000, 'harga_max' => 1000000, 'komisi_min' => 35000, 'komisi_max' => 90000],
        ]);

        // ===================== BASIC COLOR (KOMISI DIKONFIRMASI) =====================
        $buat('Basic Color', 'Warna Rambut', [
            ['varian' => 'S', 'harga_min' => 650000, 'komisi_min' => 50000, 'komisi_max' => 60000],
            ['varian' => 'M', 'harga_min' => 850000, 'komisi_min' => 60000, 'komisi_max' => 70000],
            ['varian' => 'L', 'harga_min' => 1000000, 'komisi_min' => 80000, 'komisi_max' => 90000],
            ['varian' => 'XL', 'harga_min' => 1200000, 'komisi_min' => 90000, 'komisi_max' => 110000],
        ]);
        // Two Apply -- ESTIMASI, belum ada aturan komisi eksplisit dari kamu
        $buat('Basic Color Two Apply', 'Warna Rambut', [
            ['varian' => 'default', 'harga_min' => 1000000, 'harga_max' => 2000000, 'komisi_min' => 75000, 'komisi_max' => 175000],
        ]);

        // ===================== REFRESH COLOR TREATMENT (ESTIMASI ~7-9%, CEK ULANG) =====================
        $buat('Refresh Color Treatment', 'Warna Rambut', [
            ['varian' => 'S', 'harga_min' => 650000, 'komisi_min' => 46000, 'komisi_max' => 59000],
            ['varian' => 'M', 'harga_min' => 750000, 'komisi_min' => 53000, 'komisi_max' => 68000],
            ['varian' => 'L', 'harga_min' => 850000, 'komisi_min' => 60000, 'komisi_max' => 77000],
            ['varian' => 'XL', 'harga_min' => 950000, 'komisi_min' => 67000, 'komisi_max' => 86000],
        ]);

        // ===================== FASHION COLOR (KOMISI DIKONFIRMASI, flat semua ukuran) =====================
        $buat('Fashion Color', 'Warna Rambut', [
            ['varian' => 'S', 'harga_min' => 1800000, 'harga_max' => 2400000, 'komisi_min' => 90000, 'komisi_max' => 150000],
            ['varian' => 'M', 'harga_min' => 2400000, 'harga_max' => 2800000, 'komisi_min' => 90000, 'komisi_max' => 150000],
            ['varian' => 'L', 'harga_min' => 2800000, 'harga_max' => 3000000, 'komisi_min' => 90000, 'komisi_max' => 150000],
            ['varian' => 'XL', 'harga_min' => 3500000, 'komisi_min' => 90000, 'komisi_max' => 150000],
        ]);

        // ===================== HIGHLIGHT (KOMISI DIKONFIRMASI, flat semua ukuran) =====================
        $buat('Highlight', 'Warna Rambut', [
            ['varian' => 'S', 'harga_min' => 1400000, 'harga_max' => 1800000, 'komisi_min' => 90000, 'komisi_max' => 150000],
            ['varian' => 'M', 'harga_min' => 1800000, 'harga_max' => 2200000, 'komisi_min' => 90000, 'komisi_max' => 150000],
            ['varian' => 'L', 'harga_min' => 2400000, 'harga_max' => 2800000, 'komisi_min' => 90000, 'komisi_max' => 150000],
            ['varian' => 'XL', 'harga_min' => 2800000, 'harga_max' => 3000000, 'komisi_min' => 90000, 'komisi_max' => 150000],
        ]);

        // ===================== LAYANAN AKAR/BLEACH (ESTIMASI ~7-9%, CEK ULANG) =====================
        $buat('Peek A Boo', 'Warna Rambut', [
            ['varian' => 'default', 'harga_min' => 750000, 'harga_max' => 1200000, 'komisi_min' => 53000, 'komisi_max' => 108000],
        ]);
        $buat('Cat Akar', 'Warna Rambut', [
            ['varian' => 'default', 'harga_min' => 450000, 'harga_max' => 650000, 'komisi_min' => 32000, 'komisi_max' => 59000],
        ]);
        $buat('Bleach Akar', 'Warna Rambut', [
            ['varian' => 'default', 'harga_min' => 650000, 'harga_max' => 850000, 'komisi_min' => 46000, 'komisi_max' => 77000],
        ]);

        // ===================== TREATMENT (Creambath KOMISI DIKONFIRMASI, sisanya ESTIMASI) =====================
        $buat('Creambath', 'Treatment', [
            ['varian' => 'default', 'harga_min' => 250000, 'komisi_min' => 25000, 'komisi_max' => 25000],
        ]);
        $buat('Hairmask', 'Treatment', [
            ['varian' => 'default', 'harga_min' => 250000, 'komisi_min' => 18000, 'komisi_max' => 23000],
        ]);
        $buat('Treatment Komplit', 'Treatment', [
            ['varian' => 'default', 'harga_min' => 350000, 'komisi_min' => 25000, 'komisi_max' => 32000],
        ]);
        $buat('Hair Detox', 'Treatment', [
            ['varian' => 'default', 'harga_min' => 450000, 'komisi_min' => 32000, 'komisi_max' => 41000],
        ]);
    }
}