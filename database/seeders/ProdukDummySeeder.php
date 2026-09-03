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
        // kategori_produk = tipe spesifik (Color, Bleaching, Oxidant, dsb) untuk auto-fill produk default.
        $buat('Milbon Color', 'Milbon', 100000, '/10ml', 'Color');
        $buat('Alfaparf Color', 'Alfaparf', 100000, '/10ml', 'Color');
        $buat('Keaune Color', 'Keaune', 100000, '/10ml', 'Color');
        $buat('Alfaparf Keratin', 'Alfaparf', 100000, '/10ml', 'Keratin');
        $buat('Omni Keratin', 'Omni', 100000, '/10ml', 'Keratin');
        $buat('Milbon Smoothing', 'Milbon', 100000, '/10ml', 'Smoothing');
        $buat('Alfaparf Hairtreatment Complete', 'Alfaparf', 100000, '/10ml', 'Hairtreatment');
        $buat('Alfaparf Creambath', 'Alfaparf', 100000, '/10ml', 'Creambath');
        $buat('Matrix Creambath', 'Matrix', 100000, '/10ml', 'Creambath');
        $buat('Milbon Bleaching', 'Milbon', 100000, '/10ml', 'Bleaching');
        $buat('Alfaparf Bleaching', 'Alfaparf', 100000, '/10ml', 'Bleaching');
        $buat('Keaune Bleaching', 'Keaune', 100000, '/10ml', 'Bleaching');
        $buat('Milbon Oxidant', 'Milbon', 100000, '/10ml', 'Oxidant');
        $buat('Alfaparf Oxidant', 'Alfaparf', 100000, '/10ml', 'Oxidant');
        $buat('Keaune Oxidant', 'Keaune', 100000, '/10ml', 'Oxidant');

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
