<?php

namespace Tests\Feature;

use App\Models\DetailTransaksi;
use App\Models\Karyawan;
use App\Models\Layanan;
use App\Models\Pelanggan;
use App\Models\Produk;
use App\Models\TransaksiKunjungan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StrukTransaksiTest extends TestCase
{
    use RefreshDatabase;

    private function loginKasir(): User
    {
        $user = User::factory()->create(['role' => 'kasir']);
        $this->actingAs($user);

        return $user;
    }

    private function buatPelanggan(string $nama = 'Budi Santoso'): Pelanggan
    {
        return Pelanggan::create([
            'nama' => $nama,
            'no_wa' => '+628'.rand(100000000, 999999999),
        ]);
    }

    private function buatKaryawan(string $nama): Karyawan
    {
        return Karyawan::create([
            'nama' => $nama,
            'kontak' => '0812',
            'skema_komisi' => 'per_layanan',
            'persen_komisi_harian' => 0,
            'gaji_pokok' => 0,
        ]);
    }

    private function buatLayanan(string $nama = 'Balayage Color Treatment'): Layanan
    {
        return Layanan::create([
            'nama_layanan' => $nama,
            'kategori' => 'Color',
            'aktif' => true,
            'termasuk_potong' => true,
        ]);
    }

    private function buatProduk(): Produk
    {
        return Produk::create([
            'nama_produk' => 'Olaplex No.3 Hair Perfector',
            'merek' => 'Test Brand',
            'kategori_produk' => 'Color',
            'satuan_penggunaan' => '/10ml',
            'harga_per_pcs' => 285000,
            'harga_per_penggunaan' => 28500,
            'harga_modal_rata_rata' => 150000,
            'stok' => 20,
            'aktif' => true,
        ]);
    }

    private function buatTransaksi(Pelanggan $pelanggan, array $overrides = []): TransaksiKunjungan
    {
        return TransaksiKunjungan::create(array_merge([
            'id_pelanggan' => $pelanggan->id,
            'jenis_pengerjaan' => 'sendiri',
            'no_struk' => TransaksiKunjungan::generateNoStruk(),
            'waktu_kunjungan' => now(),
            'metode_pembayaran' => 'cash',
            'status' => 'selesai',
            'total_bayar' => 0,
        ], $overrides));
    }

    /** (1) Transaksi sendiri, 1 layanan. */
    public function test_struk_sendiri_menampilkan_data_utama(): void
    {
        $this->loginKasir();
        $pelanggan = $this->buatPelanggan();
        $staf = $this->buatKaryawan('Agus Pratama');
        $layanan = $this->buatLayanan();

        $transaksi = $this->buatTransaksi($pelanggan, ['metode_pembayaran' => 'qris_bni']);
        DetailTransaksi::create([
            'id_transaksi' => $transaksi->id,
            'id_staf' => $staf->id,
            'tipe_item' => 'layanan',
            'id_layanan' => $layanan->id,
            'varian_dipilih' => 'L',
            'qty' => 1,
            'harga_saat_transaksi' => 150000,
            'subtotal' => 150000,
            'diskon' => 0,
        ]);
        $transaksi->update(['total_bayar' => 150000]);

        $response = $this->get(route('transaksi.struk', $transaksi));

        $response->assertOk();
        $response->assertSee(config('salon.nama'));
        $response->assertSee($transaksi->no_struk);
        $response->assertSee('Balayage Color Treatment');
        $response->assertSee('Total');
        $response->assertSee('QRIS BNI');
    }

    /** (2) Transaksi berdua: gabung nama stylist, baris staf ke-2 tidak jadi item. */
    public function test_struk_berdua_menggabungkan_stylist_dan_melewati_baris_kedua(): void
    {
        $this->loginKasir();
        $pelanggan = $this->buatPelanggan();
        $staf1 = $this->buatKaryawan('Agus Pratama');
        $staf2 = $this->buatKaryawan('Rina');
        $layanan = $this->buatLayanan();

        $transaksi = $this->buatTransaksi($pelanggan, ['jenis_pengerjaan' => 'berdua']);
        DetailTransaksi::create([
            'id_transaksi' => $transaksi->id,
            'id_staf' => $staf1->id,
            'tipe_item' => 'layanan',
            'id_layanan' => $layanan->id,
            'qty' => 1,
            'harga_saat_transaksi' => 120000,
            'subtotal' => 120000,
            'diskon' => 0,
        ]);
        DetailTransaksi::create([
            'id_transaksi' => $transaksi->id,
            'id_staf' => $staf2->id,
            'tipe_item' => 'layanan',
            'id_layanan' => $layanan->id,
            'qty' => 1,
            'harga_saat_transaksi' => 0,
            'subtotal' => 0,
            'diskon' => 0,
            'catatan' => '(Staf ke-2) ',
        ]);
        $transaksi->update(['total_bayar' => 120000]);

        $response = $this->get(route('transaksi.struk', $transaksi));

        $response->assertOk();
        $response->assertSee('Stylist: Agus Pratama & Rina');
        $response->assertDontSee('(Staf ke-2)');
    }

    /** (3) Produk retail dengan diskon: baris Diskon muncul, Total = Sub Total - Diskon. */
    public function test_struk_produk_retail_dengan_diskon(): void
    {
        $this->loginKasir();
        $pelanggan = $this->buatPelanggan();
        $produk = $this->buatProduk();

        $transaksi = $this->buatTransaksi($pelanggan);
        DetailTransaksi::create([
            'id_transaksi' => $transaksi->id,
            'id_staf' => null,
            'tipe_item' => 'produk',
            'id_produk' => $produk->id,
            'qty' => 2,
            'harga_saat_transaksi' => 285000,
            'diskon' => 50000,
            'subtotal' => 520000,
        ]);
        $transaksi->update(['total_bayar' => 520000]);

        $response = $this->get(route('transaksi.struk', $transaksi));

        $response->assertOk();
        $response->assertSee('Diskon');
        $response->assertSee('Olaplex No.3 Hair Perfector');
        // Sub total 2 x 285.000 = 570.000 ; Total = 520.000
        $response->assertSee(rupiah(570000));
        $response->assertSee(rupiah(520000));
    }

    /** (4) Transaksi batal menampilkan penanda DIBATALKAN. */
    public function test_struk_transaksi_batal(): void
    {
        $this->loginKasir();
        $pelanggan = $this->buatPelanggan();
        $staf = $this->buatKaryawan('Agus Pratama');
        $layanan = $this->buatLayanan();

        $transaksi = $this->buatTransaksi($pelanggan, ['status' => 'batal']);
        DetailTransaksi::create([
            'id_transaksi' => $transaksi->id,
            'id_staf' => $staf->id,
            'tipe_item' => 'layanan',
            'id_layanan' => $layanan->id,
            'qty' => 1,
            'harga_saat_transaksi' => 150000,
            'subtotal' => 150000,
            'diskon' => 0,
        ]);
        $transaksi->update(['total_bayar' => 150000]);

        $response = $this->get(route('transaksi.struk', $transaksi));

        $response->assertOk();
        $response->assertSee('DIBATALKAN');
    }

    /** Tambahan: lebar 58mm memakai kelas kertas struk-58. */
    public function test_struk_lebar_58mm(): void
    {
        $this->loginKasir();
        $pelanggan = $this->buatPelanggan();
        $transaksi = $this->buatTransaksi($pelanggan);

        $response = $this->get(route('transaksi.struk', ['transaksi' => $transaksi, 'lebar' => 58]));

        $response->assertOk();
        $response->assertSee('struk-58', false);
    }
}
