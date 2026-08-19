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
            Produk::create([
                'nama_produk' => $nama,
                'merek' => $merek,
                'kategori_produk' => $kategori,
                'satuan' => $satuan,
                'harga_per_satuan' => $hargaPer10,
                'stok' => $stok,
                'aktif' => true,
            ]);
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
        Produk::create(['nama_produk' => 'Alfaparf Shampoo 250ml', 'merek' => 'Alfaparf', 'kategori_produk' => 'dijual', 'satuan' => 'pcs', 'harga_per_satuan' => 185000, 'stok' => 30, 'aktif' => true]);
        Produk::create(['nama_produk' => 'Milbon Hair Serum', 'merek' => 'Milbon', 'kategori_produk' => 'dijual', 'satuan' => 'pcs', 'harga_per_satuan' => 220000, 'stok' => 30, 'aktif' => true]);
        Produk::create(['nama_produk' => 'Matrix Conditioner 250ml', 'merek' => 'Matrix', 'kategori_produk' => 'dijual', 'satuan' => 'pcs', 'harga_per_satuan' => 165000, 'stok' => 30, 'aktif' => true]);
    }
}
