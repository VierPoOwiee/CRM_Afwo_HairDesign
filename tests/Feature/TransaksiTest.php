<?php

namespace Tests\Feature;

use App\Models\DetailTransaksi;
use App\Models\DetailTransaksiProduk;
use App\Models\Karyawan;
use App\Models\KomisiTransaksi;
use App\Models\Layanan;
use App\Models\Pelanggan;
use App\Models\Produk;
use App\Models\RiwayatStokProduk;
use App\Models\TransaksiKunjungan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransaksiTest extends TestCase
{
    use RefreshDatabase;

    private function loginKasir(): User
    {
        $user = User::factory()->create(['role' => 'kasir']);
        $this->actingAs($user);

        return $user;
    }

    private function buatPelanggan(): Pelanggan
    {
        return Pelanggan::create([
            'nama' => 'Test Client',
            'no_wa' => '+628'.rand(100000000, 999999999),
        ]);
    }

    private function buatKaryawan(string $skema = 'per_layanan', float $persen = 0): Karyawan
    {
        return Karyawan::create([
            'nama' => 'Staf Test',
            'kontak' => '0812',
            'skema_komisi' => $skema,
            'persen_komisi_harian' => $persen,
            'gaji_pokok' => 0,
        ]);
    }

    private function buatProdukDijual(int $stok = 20, float $modal = 10000, float $harga = 20000): Produk
    {
        return Produk::create([
            'nama_produk' => 'Produk Test',
            'merek' => 'Test Brand',
            'kategori_produk' => 'dijual',
            'satuan' => 'pcs',
            'harga_per_satuan' => $harga,
            'harga_modal_rata_rata' => $modal,
            'stok' => $stok,
            'aktif' => true,
        ]);
    }

    private function buatLayanan(): Layanan
    {
        return Layanan::create([
            'nama_layanan' => 'Potong Test',
            'kategori' => 'Potong',
            'aktif' => true,
            'termasuk_potong' => true,
        ]);
    }

    private function payloadTransaksi(array $overrides = []): array
    {
        $pelanggan = $this->buatPelanggan();
        $staf = $this->buatKaryawan();

        return array_merge([
            'id_pelanggan' => $pelanggan->id,
            'jenis_pengerjaan' => 'sendiri',
            'waktu_kunjungan' => now()->format('Y-m-d H:i'),
            'metode_pembayaran' => 'cash',
            'items' => [
                [
                    'tipe_item' => 'layanan',
                    'id_layanan' => $this->buatLayanan()->id,
                    'id_staf_1' => $staf->id,
                    'id_staf_2' => null,
                    'qty' => 1,
                    'harga_saat_transaksi' => 100000,
                    'diskon' => null,
                    'komisi_nominal_1' => 50000,
                    'komisi_nominal_2' => null,
                    'gram_pemakaian_tambahan' => 0,
                ],
            ],
        ], $overrides);
    }

    public function test_format_no_struk_urutan_harian(): void
    {
        $t1 = TransaksiKunjungan::create([
            'id_pelanggan' => $this->buatPelanggan()->id,
            'jenis_pengerjaan' => 'sendiri',
            'no_struk' => TransaksiKunjungan::generateNoStruk(),
            'waktu_kunjungan' => now(),
            'metode_pembayaran' => 'cash',
            'status' => 'selesai',
        ]);
        $t2 = TransaksiKunjungan::create([
            'id_pelanggan' => $this->buatPelanggan()->id,
            'jenis_pengerjaan' => 'sendiri',
            'no_struk' => TransaksiKunjungan::generateNoStruk(),
            'waktu_kunjungan' => now(),
            'metode_pembayaran' => 'cash',
            'status' => 'selesai',
        ]);

        $prefix = 'TRX-'.now()->format('Ymd').'-';

        $this->assertStringStartsWith($prefix, $t1->no_struk);
        $this->assertSame($prefix.'000001', $t1->no_struk);
        $this->assertSame($prefix.'000002', $t2->no_struk);
        $this->assertMatchesRegularExpression('/^TRX-\d{8}-\d{6}$/', $t2->no_struk);
    }

    public function test_store_menghitung_subtotal_dan_total_bayar_di_server(): void
    {
        $this->loginKasir();
        $payload = $this->payloadTransaksi();
        $payload['items'] = [
            array_merge($payload['items'][0], [
                'diskon' => 10000,
                'qty' => 2,
            ]),
        ];

        $response = $this->post(route('transaksi.store'), $payload);
        $response->assertRedirect();

        $transaksi = TransaksiKunjungan::first();
        $this->assertSame('190000.00', (string) $transaksi->total_bayar);
        $this->assertSame('190000.00', (string) $transaksi->details()->first()->subtotal);
    }

    public function test_store_pcs_mengurangi_stok(): void
    {
        $this->loginKasir();
        $produk = $this->buatProdukDijual(20);
        $payload = $this->payloadTransaksi();
        $payload['items'] = [
            [
                'tipe_item' => 'produk',
                'id_produk' => $produk->id,
                'id_staf_1' => null,
                'id_staf_2' => null,
                'qty' => 3,
                'harga_saat_transaksi' => 20000,
                'diskon' => null,
                'komisi_nominal_1' => null,
                'komisi_nominal_2' => null,
                'gram_pemakaian_tambahan' => 0,
            ],
        ];

        $this->post(route('transaksi.store'), $payload)->assertRedirect();

        $this->assertSame(17, Produk::find($produk->id)->stok);
        $riwayat = RiwayatStokProduk::where('produk_id', $produk->id)
            ->where('jenis', RiwayatStokProduk::JENIS_PENJUALAN)
            ->first();
        $this->assertNotNull($riwayat);
        $this->assertSame('-3.00', (string) $riwayat->perubahan);
        $this->assertSame('17.00', (string) $riwayat->stok_akhir);
    }

    public function test_cancel_mengembalikan_stok_dan_menghapus_komisi(): void
    {
        $this->loginKasir();
        $produk = $this->buatProdukDijual(20);
        $staf = $this->buatKaryawan();
        $pelanggan = $this->buatPelanggan();

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
            'id_staf' => $staf->id,
            'tipe_item' => 'produk',
            'id_produk' => $produk->id,
            'qty' => 3,
            'harga_saat_transaksi' => 20000,
            'harga_modal_satuan' => 10000,
            'keuntungan' => 30000,
            'subtotal' => 60000,
            'komisi_nominal' => null,
        ]);
        $produk->decrement('stok', 3);
        KomisiTransaksi::create([
            'id_transaksi' => $transaksi->id,
            'id_staf' => $staf->id,
            'jumlah_komisi' => 5000,
        ]);

        $this->put(route('transaksi.cancel', $transaksi))->assertRedirect();

        $this->assertSame('batal', $transaksi->fresh()->status);
        $this->assertSame(20, Produk::find($produk->id)->stok);
        $this->assertDatabaseMissing('komisi_transaksi', ['id_transaksi' => $transaksi->id]);
        $this->assertDatabaseHas('riwayat_stok_produk', [
            'produk_id' => $produk->id,
            'jenis' => RiwayatStokProduk::JENIS_BATAL,
            'stok_akhir' => '20.00',
        ]);
        $this->assertNotNull($detail->fresh());
    }

    public function test_metode_pembayaran_whitelist(): void
    {
        $this->loginKasir();
        $payload = $this->payloadTransaksi(['metode_pembayaran' => 'saham']);
        $this->post(route('transaksi.store'), $payload)->assertSessionHasErrors('metode_pembayaran');
    }

    public function test_berdua_membuat_dua_baris_komisi(): void
    {
        $this->loginKasir();
        $staf1 = $this->buatKaryawan();
        $staf2 = $this->buatKaryawan();
        $pelanggan = $this->buatPelanggan();
        $layanan = $this->buatLayanan();

        $payload = [
            'id_pelanggan' => $pelanggan->id,
            'jenis_pengerjaan' => 'berdua',
            'waktu_kunjungan' => now()->format('Y-m-d H:i'),
            'metode_pembayaran' => 'cash',
            'items' => [
                [
                    'tipe_item' => 'layanan',
                    'id_layanan' => $layanan->id,
                    'id_staf_1' => $staf1->id,
                    'id_staf_2' => $staf2->id,
                    'qty' => 1,
                    'harga_saat_transaksi' => 120000,
                    'diskon' => null,
                    'komisi_nominal_1' => 40000,
                    'komisi_nominal_2' => 40000,
                    'gram_pemakaian_tambahan' => 0,
                ],
            ],
        ];

        $this->post(route('transaksi.store'), $payload)->assertRedirect();

        $transaksi = TransaksiKunjungan::first();
        $this->assertSame(2, DetailTransaksi::where('id_transaksi', $transaksi->id)->count());
        $this->assertSame(2, KomisiTransaksi::where('id_transaksi', $transaksi->id)->count());
        $this->assertDatabaseHas('komisi_transaksi', [
            'id_transaksi' => $transaksi->id,
            'id_staf' => $staf1->id,
            'jumlah_komisi' => '40000.00',
        ]);
        $this->assertDatabaseHas('komisi_transaksi', [
            'id_transaksi' => $transaksi->id,
            'id_staf' => $staf2->id,
            'jumlah_komisi' => '40000.00',
        ]);
    }

    public function test_jenis_berdua_wajib_staf_kedua(): void
    {
        $this->loginKasir();
        $payload = $this->payloadTransaksi(['jenis_pengerjaan' => 'berdua']);
        $this->post(route('transaksi.store'), $payload)->assertSessionHasErrors('items.0.id_staf_2');
    }

    public function test_gram_tambahan_agen_jawaban_tarif_kelebihan(): void
    {
        $detail = new DetailTransaksi;
        $dasar = 100000.0;
        $tarif = 100000.0;

        $detail->gram_pemakaian_tambahan = 0;
        $this->assertSame($dasar, $detail->computeHargaSuggested($dasar, $tarif));

        $detail->gram_pemakaian_tambahan = 2;
        $this->assertSame($dasar + 100000, $detail->computeHargaSuggested($dasar, $tarif));

        $detail->gram_pemakaian_tambahan = 10;
        $this->assertSame($dasar + 100000, $detail->computeHargaSuggested($dasar, $tarif));

        $detail->gram_pemakaian_tambahan = 11;
        $this->assertSame($dasar + 200000, $detail->computeHargaSuggested($dasar, $tarif));

        $this->assertSame($dasar, $detail->computeHargaSuggested($dasar, null));
    }

    public function test_pemakaian_layanan_mengurangi_stok_dan_mencatat_modal(): void
    {
        $this->loginKasir();
        $bahan = Produk::create([
            'nama_produk' => 'Cat Test',
            'merek' => 'Test Brand',
            'kategori_produk' => 'Color',
            'satuan' => '/10ml',
            'harga_per_satuan' => 50000,
            'harga_modal_rata_rata' => 20000,
            'stok' => 1000,
            'aktif' => true,
        ]);
        $payload = $this->payloadTransaksi();
        $staf = Karyawan::first();
        $payload['items'][0]['id_staf_1'] = $staf->id;
        $payload['items'][0]['produk_penggunaan'] = [
            ['id_produk' => $bahan->id, 'pemakaian_ml' => 35],
        ];

        $this->post(route('transaksi.store'), $payload)->assertRedirect();

        $this->assertSame(965, Produk::find($bahan->id)->stok);

        $terpakai = DetailTransaksiProduk::first();
        $this->assertSame('35.00', (string) $terpakai->pemakaian_ml);
        // 35ml = 3.5 unit × 50000 → subtotal 175000
        $this->assertSame('175000.00', (string) $terpakai->subtotal);
        // modal terpakai = 3.5 unit × 20000 = 70000
        $this->assertSame('70000.00', (string) $terpakai->harga_modal_terpakai);

        $this->assertDatabaseHas('riwayat_stok_produk', [
            'produk_id' => $bahan->id,
            'jenis' => RiwayatStokProduk::JENIS_PEMAKAIAN_LAYANAN,
            'perubahan' => '-35.00',
            'stok_akhir' => '965.00',
        ]);
    }

    public function test_store_tanpa_item_layanan_ditolak(): void
    {
        $this->loginKasir();
        $payload = $this->payloadTransaksi();
        $payload['items'][0]['tipe_item'] = 'layanan';
        $payload['items'][0]['id_layanan'] = null;

        $this->post(route('transaksi.store'), $payload)->assertSessionHasErrors('items.0.id_layanan');
    }
}