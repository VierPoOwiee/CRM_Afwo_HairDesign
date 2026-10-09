<?php

namespace Tests\Feature;

use App\Models\DetailTransaksi;
use App\Models\Karyawan;
use App\Models\Pelanggan;
use App\Models\Produk;
use App\Models\RiwayatStokProduk;
use App\Models\TransaksiKunjungan;
use App\Models\User;
use App\Services\ProdukModalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModalKeuntunganTest extends TestCase
{
    use RefreshDatabase;

    private function loginKasir(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'kasir']));
    }

    public function test_restock_rata_rata_tertimbang(): void
    {
        $produk = Produk::create([
            'nama_produk' => 'Produk Test',
            'merek' => 'Test',
            'kategori_produk' => 'dijual',
            'satuan' => 'pcs',
            'harga_per_satuan' => 20000,
            'harga_modal_rata_rata' => 0,
            'stok' => 0,
            'aktif' => true,
        ]);

        // 10 pcs @ Rp10.000
        ProdukModalService::restock($produk, 10, 10000, 'Beli batch 1');
        $produk->refresh();
        $this->assertSame(10, $produk->stok);
        $this->assertSame('10000.00', (string) $produk->harga_modal_rata_rata);

        // 10 pcs @ Rp12.000 → rata-rata tertimbang = (10*10000 + 10*12000)/20 = 11000
        ProdukModalService::restock($produk, 10, 12000, 'Beli batch 2');
        $produk->refresh();
        $this->assertSame(20, $produk->stok);
        $this->assertSame('11000.00', (string) $produk->harga_modal_rata_rata);

        $this->assertDatabaseHas('pembelian_produk', ['produk_id' => $produk->id, 'qty' => '10.00', 'harga_beli' => '12000.00']);
        $this->assertDatabaseHas('riwayat_stok_produk', ['produk_id' => $produk->id, 'jenis' => RiwayatStokProduk::JENIS_RESTOCK, 'stok_akhir' => '20.00']);
    }

    public function test_restock_stok_nol_modal_menjadi_harga_beli(): void
    {
        $produk = Produk::create([
            'nama_produk' => 'Produk Test',
            'merek' => 'Test',
            'kategori_produk' => 'dijual',
            'satuan' => 'pcs',
            'harga_per_satuan' => 20000,
            'harga_modal_rata_rata' => 5000,
            'stok' => 0,
            'aktif' => true,
        ]);

        ProdukModalService::restock($produk, 5, 8000, 'Beli');
        $produk->refresh();

        $this->assertSame('8000.00', (string) $produk->harga_modal_rata_rata);
    }

    public function test_hitung_modal_baru_tanpa_menyimpan(): void
    {
        $this->assertSame(9500.0, ProdukModalService::hitungModalBaru(10, 10000, 10, 9000));
    }

    public function test_penjualan_retail_menyimpan_snapshot_modal_dan_keuntungan(): void
    {
        $this->loginKasir();
        $pelanggan = Pelanggan::create(['nama' => 'Test', 'no_wa' => '+6281111111111']);
        $produk = Produk::create([
            'nama_produk' => 'Produk Test',
            'merek' => 'Test',
            'kategori_produk' => 'dijual',
            'satuan' => 'pcs',
            'harga_per_satuan' => 30000,
            'harga_modal_rata_rata' => 12000,
            'stok' => 10,
            'aktif' => true,
        ]);

        $transaksi = TransaksiKunjungan::create([
            'id_pelanggan' => $pelanggan->id,
            'jenis_pengerjaan' => 'sendiri',
            'no_struk' => TransaksiKunjungan::generateNoStruk(),
            'waktu_kunjungan' => now(),
            'metode_pembayaran' => 'cash',
            'status' => 'selesai',
        ]);

        $detail = DetailTransaksi::create([
            'id_transaksi' => $transaksi->id,
            'id_staf' => null,
            'tipe_item' => 'produk',
            'id_produk' => $produk->id,
            'qty' => 2,
            'harga_saat_transaksi' => 30000,
            'harga_modal_satuan' => 12000,
            'keuntungan' => 60000 - (12000 * 2),
            'subtotal' => 60000,
            'komisi_nominal' => null,
        ]);
        $produk->decrement('stok', 2);

        $this->assertSame('12000.00', (string) $detail->harga_modal_satuan);
        $this->assertSame(36000.0, (float) $detail->keuntungan);
        $this->assertSame(8, $produk->fresh()->stok);

        // Simulasi harga modal berubah setelah penjualan → snapshot tidak berubah
        ProdukModalService::restock($produk, 10, 20000, 'Beli lagi');
        $this->assertSame('12000.00', (string) $detail->fresh()->harga_modal_satuan);
    }

    public function test_keuntungan_tidak_boleh_negatif_dari_diskon_berlebih(): void
    {
        $detail = new DetailTransaksi;
        $detail->harga_saat_transaksi = 100000;
        $detail->qty = 1;
        $detail->diskon = 150000;

        $this->assertSame(0.0, $detail->computeSubtotal());
    }
}