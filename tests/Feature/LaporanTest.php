<?php

namespace Tests\Feature;

use App\Models\DetailTransaksi;
use App\Models\Karyawan;
use App\Models\KomisiTransaksi;
use App\Models\Layanan;
use App\Models\Pelanggan;
use App\Models\Produk;
use App\Models\TransaksiKunjungan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanTest extends TestCase
{
    use RefreshDatabase;

    private function loginOwner(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'owner']));
    }

    private function buatStaf(): Karyawan
    {
        return Karyawan::create([
            'nama' => 'Staf Laporan',
            'kontak' => '0812',
            'skema_komisi' => 'per_layanan',
            'persen_komisi_harian' => 0,
            'gaji_pokok' => 0,
        ]);
    }

    private function buatTransaksi(array $data): TransaksiKunjungan
    {
        $pelanggan = Pelanggan::create(['nama' => $data['pelanggan'] ?? 'Test', 'no_wa' => '+628'.rand(100000000, 999999999)]);

        return TransaksiKunjungan::create([
            'id_pelanggan' => $pelanggan->id,
            'jenis_pengerjaan' => $data['jenis_pengerjaan'] ?? 'sendiri',
            'no_struk' => TransaksiKunjungan::generateNoStruk(),
            'waktu_kunjungan' => $data['waktu_kunjungan'],
            'metode_pembayaran' => $data['metode'] ?? 'cash',
            'total_bayar' => $data['total'],
            'status' => $data['status'] ?? 'selesai',
        ]);
    }

    public function test_laporan_penjualan_menjumlahkan_hanya_status_selesai(): void
    {
        $this->loginOwner();
        $hariIni = now();

        $selesai = $this->buatTransaksi(['waktu_kunjungan' => $hariIni, 'total' => 100000]);
        $selesai2 = $this->buatTransaksi(['waktu_kunjungan' => $hariIni, 'total' => 50000]);
        $this->buatTransaksi(['waktu_kunjungan' => $hariIni, 'total' => 999999, 'status' => 'batal']);

        $periode = [
            'dari' => $hariIni->copy()->startOfDay()->toDateString(),
            'sampai' => $hariIni->copy()->endOfDay()->toDateString(),
        ];

        $response = $this->get(route('laporan.penjualan', $periode));
        $response->assertOk();
        $response->assertSee('Rp' . number_format(150000, 0, ',', '.'));
        $this->assertSame(2, $response->viewData('jumlahTransaksi'));
    }

    public function test_laporan_penjualan_tidak_menampilkan_transaksi_batal_dalam_omset(): void
    {
        $this->loginOwner();
        $hariIni = now();
        $batal = $this->buatTransaksi(['waktu_kunjungan' => $hariIni, 'total' => 999999, 'status' => 'batal']);

        $periode = [
            'dari' => $hariIni->copy()->startOfDay()->toDateString(),
            'sampai' => $hariIni->copy()->endOfDay()->toDateString(),
        ];

        $response = $this->get(route('laporan.penjualan', $periode));
        $response->assertOk();
        $this->assertSame(0, $response->viewData('jumlahTransaksi'));
        $response->assertDontSee('Rp999.999');
    }

    public function test_filter_staf_mempersempit_laporan(): void
    {
        $this->loginOwner();
        $stafA = $this->buatStaf();
        $stafB = $this->buatStaf();
        $hariIni = now();

        $trxA = $this->buatTransaksi(['waktu_kunjungan' => $hariIni, 'total' => 70000]);
        $trxB = $this->buatTransaksi(['waktu_kunjungan' => $hariIni, 'total' => 30000]);

        $layanan = Layanan::create(['nama_layanan' => 'Layanan A', 'kategori' => 'Potong', 'aktif' => true, 'termasuk_potong' => true]);
        foreach ([[$trxA, $stafA], [$trxB, $stafB]] as [$trx, $staf]) {
            DetailTransaksi::create([
                'id_transaksi' => $trx->id,
                'id_staf' => $staf->id,
                'tipe_item' => 'layanan',
                'id_layanan' => $layanan->id,
                'qty' => 1,
                'harga_saat_transaksi' => $trx->total_bayar,
                'subtotal' => $trx->total_bayar,
                'komisi_nominal' => 0,
            ]);
        }

        $periode = [
            'dari' => $hariIni->copy()->startOfDay()->toDateString(),
            'sampai' => $hariIni->copy()->endOfDay()->toDateString(),
            'id_staf' => $stafA->id,
        ];

        $response = $this->get(route('laporan.penjualan', $periode));
        $response->assertOk();
        $this->assertSame(1, $response->viewData('jumlahTransaksi'));
        $response->assertSee('Rp' . number_format(70000, 0, ',', '.'));
    }

    public function test_keuntungan_produk_retail_menggunakan_snapshot(): void
    {
        $this->loginOwner();
        $hariIni = now();

        $produk = Produk::create([
            'nama_produk' => 'Krim Test',
            'merek' => 'M',
            'kategori_produk' => 'dijual',
            'satuan' => 'pcs',
            'harga_per_satuan' => 30000,
            'harga_modal_rata_rata' => 12000,
            'stok' => 10,
            'aktif' => true,
        ]);

        $trx = $this->buatTransaksi(['waktu_kunjungan' => $hariIni, 'total' => 60000]);
        DetailTransaksi::create([
            'id_transaksi' => $trx->id,
            'id_staf' => null,
            'tipe_item' => 'produk',
            'id_produk' => $produk->id,
            'qty' => 2,
            'harga_saat_transaksi' => 30000,
            'harga_modal_satuan' => 12000,
            'keuntungan' => 36000,
            'subtotal' => 60000,
            'komisi_nominal' => null,
        ]);

        $periode = [
            'dari' => $hariIni->copy()->startOfDay()->toDateString(),
            'sampai' => $hariIni->copy()->endOfDay()->toDateString(),
        ];

        $response = $this->get(route('laporan.keuntungan-produk', $periode));
        $response->assertOk();

        $this->assertSame(60000.0, (float) $response->viewData('retailTotals')['omset']);
        $this->assertSame(24000.0, (float) $response->viewData('retailTotals')['modal']);
        $this->assertSame(36000.0, (float) $response->viewData('retailTotals')['keuntungan']);
    }

    public function test_rekap_komisi_menampilkan_total_komisi_per_staf(): void
    {
        $this->loginOwner();
        $staf = $this->buatStaf();
        $hariIni = now();

        $trx = $this->buatTransaksi(['waktu_kunjungan' => $hariIni, 'total' => 100000]);
        KomisiTransaksi::create([
            'id_transaksi' => $trx->id,
            'id_staf' => $staf->id,
            'jumlah_komisi' => 25000,
        ]);

        $periode = [
            'dari' => $hariIni->copy()->startOfDay()->toDateString(),
            'sampai' => $hariIni->copy()->endOfDay()->toDateString(),
        ];

        $response = $this->get(route('laporan.rekap-komisi', $periode));
        $response->assertOk();
        $response->assertSee('Rp25.000');
    }
}