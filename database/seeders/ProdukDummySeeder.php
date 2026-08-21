<?php
// database/seeders/ProdukDummySeeder.php
namespace Database\Seeders;

use App\Models\Produk;
use Illuminate\Database\Seeder;

class ProdukDummySeeder extends Seeder
{
    public function run(): void
    {
        $buat = function (string $nama, string $merek, float $hargaPer10, string $satuan, string $kategori, float $harga = null, int $stok = 500) {
            Produk::firstOrCreate(
                ['nama_produk' => $nama],
                [
                    'merek' => $merek,
                    'kategori_produk' => $kategori,
                    'satuan' => $satuan,
                    'harga_per_satuan' => $hargaPer10,
                    'stok' => $stok,
                    'aktif' => true,
                ]
            );
        };

        // ===== /10ml, dipakai_layanan (harga PER 10 GRAM / 10 ML) =====
        $buat('Milbon Color', 'Milbon', 120000, '/10ml', 'dipakai_layanan');
        $buat('Alfaparf Color', 'Alfaparf', 100000, '/10ml', 'dipakai_layanan');
        $buat('Keaune Color', 'Keaune', 70000, '/10ml', 'dipakai_layanan');
        $buat('Alfaparf Keratin', 'Alfaparf', 150000, '/10ml', 'dipakai_layanan');
        $buat('Omni Keratin', 'Omni', 90000, '/10ml', 'dipakai_layanan');
        $buat('Milbon Smoothing', 'Milbon', 110000, '/10ml', 'dipakai_layanan');
        $buat('Alfaparf Hairtreatment Complete', 'Alfaparf', 80000, '/10ml', 'dipakai_layanan');
        $buat('Alfaparf Creambath', 'Alfaparf', 60000, '/10ml', 'dipakai_layanan');
        $buat('Matrix Creambath', 'Matrix', 50000, '/10ml', 'dipakai_layanan');
        $buat('Milbon Bleaching', 'Milbon', 130000, '/10ml', 'dipakai_layanan');
        $buat('Alfaparf Bleaching', 'Alfaparf', 110000, '/10ml', 'dipakai_layanan');
        $buat('Keaune Bleaching', 'Keaune', 80000, '/10ml', 'dipakai_layanan');
        $buat('Milbon Oxidant', 'Milbon', 40000, '/10ml', 'dipakai_layanan');
        $buat('Alfaparf Oxidant', 'Alfaparf', 35000, '/10ml', 'dipakai_layanan');
        $buat('Keaune Oxidant', 'Keaune', 25000, '/10ml', 'dipakai_layanan');

        // ===== PCS, dijual (harga LANGSUNG PER 1 PCS, stok default lebih kecil) =====
        // Kategori "Dijual Per PCS" tidak memakai merek (merek = null).
        $buatPcs = function (string $nama, float $harga, int $stok = 30) {
            Produk::firstOrCreate(
                ['nama_produk' => $nama],
                [
                    'merek' => null,
                    'kategori_produk' => 'dijual',
                    'satuan' => 'pcs',
                    'harga_per_satuan' => $harga,
                    'stok' => $stok,
                    'aktif' => true,
                ]
            );
        };

        $buatPcs('Alfaparf Shampoo 250ml', 185000);
        $buatPcs('Milbon Hair Serum', 220000);
        $buatPcs('Matrix Conditioner 250ml', 165000);

        $buatPcs('Shampoo Vegan 300ml', 350000);
        $buatPcs('Conditioner Vegan 250ml', 375000);
        $buatPcs('Shampo Rontok 250ml', 350000);
        $buatPcs('Hair Tonic 125ml', 350000);
        $buatPcs('Penumbuh Rambut', 550000);
        $buatPcs('Shampo Ketombe Keaune 300ml', 350000);
        $buatPcs('Shampo Sensitive Keaune 300ml', 350000);
        $buatPcs('Hair Spray Keaune', 350000);
        $buatPcs('Silky Treatment 200ml', 350000);
        $buatPcs('Elujuda Serum 120ml', 450000);
        $buatPcs('Shampo Silver 200ml', 200000);
    }
}
