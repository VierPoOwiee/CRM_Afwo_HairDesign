<?php

namespace Tests\Feature;

use App\Models\DetailTransaksi;
use App\Models\Karyawan;
use App\Models\KomisiHarianSpesial;
use App\Models\KomisiTransaksi;
use App\Models\Layanan;
use App\Models\Pelanggan;
use App\Models\Produk;
use App\Models\TransaksiKunjungan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class KomisiTest extends TestCase
{
    use RefreshDatabase;

    private function loginKasir(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'kasir']));
    }

    private function buatStaf(string $skema = 'per_layanan', float $persen = 0): Karyawan
    {
        return Karyawan::create([
            'nama' => 'Staf '.fake()->unique()->firstName(),
            'kontak' => '0812',
            'skema_komisi' => $skema,
            'persen_komisi_harian' => $persen,
            'gaji_pokok' => 0,
        ]);
    }

    private function buatTransaksi(Karyawan $staf, array $detailData = []): TransaksiKunjungan
    {
        $pelanggan = Pelanggan::create(['nama' => 'Test', 'no_wa' => '+6282222222222']);

        return TransaksiKunjungan::create([
            'id_pelanggan' => $pelanggan->id,
            'jenis_pengerjaan' => 'sendiri',
            'no_struk' => TransaksiKunjungan::generateNoStruk(),
            'waktu_kunjungan' => $detailData['waktu_kunjungan'] ?? now(),
            'metode_pembayaran' => 'cash',
            'total_bayar' => $detailData['total_bayar'] ?? 0,
            'status' => $detailData['status'] ?? 'selesai',
        ]);
    }

    private function buatDetailLayanan(TransaksiKunjungan $trx, Karyawan $staf, float $komisi, bool $termasukPotong = true): DetailTransaksi
    {
        $layanan = Layanan::create([
            'nama_layanan' => 'Layanan '.fake()->unique()->firstName(),
            'kategori' => 'Potong',
            'aktif' => true,
            'termasuk_potong' => $termasukPotong,
        ]);

        return DetailTransaksi::create([
            'id_transaksi' => $trx->id,
            'id_staf' => $staf->id,
            'tipe_item' => 'layanan',
            'id_layanan' => $layanan->id,
            'qty' => 1,
            'harga_saat_transaksi' => 100000,
            'subtotal' => 100000,
            'komisi_nominal' => $komisi,
        ]);
    }

    public function test_komisi_otomatis_menjumlahkan_komisi_nominal_per_staf(): void
    {
        $this->loginKasir();
        $staf = $this->buatStaf();
        $trx = $this->buatTransaksi($staf);
        $this->buatDetailLayanan($trx, $staf, 50000);
        $this->buatDetailLayanan($trx, $staf, 30000);

        KomisiTransaksi::syncForTransaksi($trx);

        $this->assertDatabaseHas('komisi_transaksi', [
            'id_transaksi' => $trx->id,
            'id_staf' => $staf->id,
            'jumlah_komisi' => '80000.00',
        ]);
        $this->assertSame(1, KomisiTransaksi::where('id_transaksi', $trx->id)->count());
    }

    public function test_staf_persen_omset_harian_tidak_masuk_komisi_transaksi(): void
    {
        $this->loginKasir();
        $staf = $this->buatStaf('persen_omset_harian', 10);
        $trx = $this->buatTransaksi($staf);
        $this->buatDetailLayanan($trx, $staf, 50000);

        KomisiTransaksi::syncForTransaksi($trx);

        $this->assertSame(0, KomisiTransaksi::where('id_transaksi', $trx->id)->count());
    }

    public function test_edit_manual_komisi_staf_tidak_ditimpa(): void
    {
        $this->loginKasir();
        $staf = $this->buatStaf();
        $trx = $this->buatTransaksi($staf);
        $this->buatDetailLayanan($trx, $staf, 50000);
        KomisiTransaksi::syncForTransaksi($trx);

        $komisi = KomisiTransaksi::first();
        $this->assertSame('50000.00', (string) $komisi->jumlah_komisi);

        // Owner mengedit komisi staf secara manual
        $this->put(route('transaksi.komisi-staf.update', $trx), [
            'komisi_staf_id' => $komisi->id,
            'jumlah_komisi' => 70000,
        ])->assertRedirect();

        $this->assertSame('70000.00', (string) $komisi->fresh()->jumlah_komisi);
    }

    public function test_edit_komisi_detail_memicu_resync(): void
    {
        $this->loginKasir();
        $staf = $this->buatStaf();
        $trx = $this->buatTransaksi($staf);
        $detail = $this->buatDetailLayanan($trx, $staf, 50000);
        KomisiTransaksi::syncForTransaksi($trx);

        $this->put(route('transaksi.komisi.update', $trx), [
            'detail_id' => $detail->id,
            'komisi_nominal' => 75000,
        ])->assertRedirect();

        $this->assertSame('75000.00', (string) KomisiTransaksi::first()->jumlah_komisi);
    }

    public function test_komisi_harian_menghitung_per_tanggal_termasuk_produk(): void
    {
        $this->loginKasir();
        $staf = $this->buatStaf('persen_omset_harian', 10);
        $trx = $this->buatTransaksi($staf);
        $this->buatDetailLayanan($trx, $staf, 0, termasukPotong: false);

        // Item produk ikut dihitung sebagai omset harian
        $produk = Produk::create([
            'nama_produk' => 'Produk Test',
            'merek' => 'M',
            'kategori_produk' => 'dijual',
            'satuan' => 'pcs',
            'harga_per_satuan' => 20000,
            'harga_modal_rata_rata' => 10000,
            'stok' => 10,
            'aktif' => true,
        ]);
        DetailTransaksi::create([
            'id_transaksi' => $trx->id,
            'id_staf' => $staf->id,
            'tipe_item' => 'produk',
            'id_produk' => $produk->id,
            'qty' => 1,
            'harga_saat_transaksi' => 20000,
            'subtotal' => 20000,
            'komisi_nominal' => null,
        ]);

        KomisiHarianSpesial::calculateForDate($staf, now());

        // Omset = 100000 (layanan tidak termasuk potong) + 20000 (produk) = 120000 → 10% = 12000
        $this->assertDatabaseHas('komisi_harian_spesial', [
            'id_staf' => $staf->id,
            'tanggal' => now()->toDateString(),
            'total_omset_dasar' => '120000.00',
            'persen' => '10.00',
            'jumlah_komisi' => '12000',
        ]);
    }

    public function test_komisi_harian_tidak_menghitung_layanan_termasuk_potong(): void
    {
        $staf = $this->buatStaf('persen_omset_harian', 10);
        $trx = $this->buatTransaksi($staf);
        $this->buatDetailLayanan($trx, $staf, 0, termasukPotong: true);

        KomisiHarianSpesial::calculateForDate($staf, now());

        $this->assertDatabaseHas('komisi_harian_spesial', [
            'id_staf' => $staf->id,
            'total_omset_dasar' => '0.00',
            'jumlah_komisi' => '0',
        ]);
    }

    public function test_komisi_harian_idempoten_saat_dijalankan_ulang(): void
    {
        $this->loginKasir();
        $staf = $this->buatStaf('persen_omset_harian', 10);
        $trx = $this->buatTransaksi($staf);
        $this->buatDetailLayanan($trx, $staf, 0, termasukPotong: false);

        $kode = Artisan::call('komisi:hitung-harian', ['tanggal' => now()->toDateString()]);
        $this->assertSame(0, $kode);
        $jumlahSetelahPertama = KomisiHarianSpesial::where('id_staf', $staf->id)->where('tanggal', now()->toDateString())->count();

        Artisan::call('komisi:hitung-harian', ['tanggal' => now()->toDateString()]);
        $jumlahSetelahKedua = KomisiHarianSpesial::where('id_staf', $staf->id)->where('tanggal', now()->toDateString())->count();

        $this->assertSame(1, $jumlahSetelahPertama);
        $this->assertSame(1, $jumlahSetelahKedua);
        $this->assertSame('10000.00', (string) KomisiHarianSpesial::first()->jumlah_komisi);
    }

    public function test_komisi_harian_hitung_ulang_endpoint(): void
    {
        $this->loginKasir();
        $owner = User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner);

        $staf = $this->buatStaf('persen_omset_harian', 10);
        $trx = $this->buatTransaksi($staf);
        $this->buatDetailLayanan($trx, $staf, 0, termasukPotong: false);

        $this->post(route('laporan.rekap-komisi.hitung-ulang'), [
            'id_staf' => $staf->id,
            'tanggal' => now()->toDateString(),
        ])->assertRedirect();

        $this->assertSame('10000.00', (string) KomisiHarianSpesial::first()->jumlah_komisi);
    }

    public function test_pendapatan_karyawan_gaji_pokok_plus_komisi(): void
    {
        $this->loginKasir();
        $owner = User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner);

        $staf = $this->buatStaf();
        $staf->update(['gaji_pokok' => 1000000]);

        $trx = $this->buatTransaksi($staf);
        $this->buatDetailLayanan($trx, $staf, 50000);
        KomisiTransaksi::syncForTransaksi($trx);

        $response = $this->get(route('laporan.pendapatan-karyawan', ['bulan' => now()->format('Y-m')]));
        $response->assertOk();

        $response->assertSee(number_format(1000000, 0, ',', '.')); // gaji pokok
        $response->assertSee(number_format(50000, 0, ',', '.'));   // komisi
    }
}