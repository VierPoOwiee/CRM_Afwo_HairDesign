<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EmptyDataPagesTest extends TestCase
{
    use DatabaseTruncation;

    private function seedMaster(bool $denganKaryawan = true): array
    {
        $now = now();

        $layananId = DB::table('layanan')->insertGetId([
            'nama_layanan' => 'Potong Uji',
            'kategori' => 'Potong',
            'aktif' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('harga_layanan')->insert([
            'id_layanan' => $layananId,
            'varian' => 'S',
            'harga_dasar_min' => 50000,
            'harga_dasar_max' => 75000,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $produkId = DB::table('produk')->insertGetId([
            'nama_produk' => 'Produk Uji',
            'merek' => 'Merek Uji',
            'kategori_produk' => 'Color',
            'satuan_penggunaan' => '/10ml',
            'netto' => 100,
            'sisa_penggunaan' => 100,
            'harga_per_pcs' => 10000,
            'harga_per_penggunaan' => 1000,
            'harga_modal_rata_rata' => 500,
            'stok' => 10,
            'aktif' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('merek')->insert([
            'nama' => 'Merek Uji',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $karyawanId = null;

        if ($denganKaryawan) {
            $karyawanId = DB::table('karyawans')->insertGetId([
                'nama' => 'Stylist Uji',
                'kontak' => '081200000001',
                'skema_komisi' => 'per_layanan',
                'gaji_pokok' => 1000000,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return ['layanan' => $layananId, 'produk' => $produkId, 'karyawan' => $karyawanId];
    }

    private function owner(): User
    {
        return User::factory()->create([
            'role' => 'owner',
            'aktif' => true,
            'wajib_ganti_password' => false,
        ]);
    }

    public function test_halaman_owner_tetap_berfungsi_tanpa_data_transaksional(): void
    {
        $master = $this->seedMaster(true);
        $this->actingAs($this->owner());

        $routes = [
            'dashboard',
            'laporan.penjualan',
            'laporan.ai-chat',
            'laporan.pelanggan-aktif',
            'laporan.rekap-komisi',
            'laporan.rekap-komisi.cetak',
            'laporan.pendapatan-karyawan',
            'laporan.keuntungan-produk',
            'pengguna.index',
            'pelanggan.index',
            'pelanggan.arsip',
            'layanan.index',
            'produk.index',
            'karyawan.index',
            'karyawan.arsip',
            'transaksi.index',
            'transaksi.create',
            'appointment.index',
            'appointment.create',
            'absensi.index',
        ];

        foreach ($routes as $route) {
            $response = $this->get(route($route));
            $this->assertSame(200, $response->status(), "Halaman {$route} gagal dimuat (status {$response->status()}).");
        }

        $response = $this->get(route('laporan.rekap-komisi.slip', $master['karyawan']));
        $this->assertSame(200, $response->status(), 'Slip pendapatan gagal dimuat.');
    }

    public function test_halaman_tetap_berfungsi_saat_data_karyawan_direset(): void
    {
        $this->seedMaster(false);
        $this->actingAs($this->owner());

        $routes = [
            'dashboard',
            'laporan.penjualan',
            'laporan.ai-chat',
            'laporan.pelanggan-aktif',
            'laporan.rekap-komisi',
            'laporan.rekap-komisi.cetak',
            'laporan.pendapatan-karyawan',
            'laporan.keuntungan-produk',
            'transaksi.index',
            'transaksi.create',
            'appointment.index',
            'appointment.create',
            'absensi.index',
        ];

        foreach ($routes as $route) {
            $response = $this->get(route($route));
            $this->assertSame(200, $response->status(), "Halaman {$route} gagal dimuat tanpa karyawan (status {$response->status()}).");
        }
    }
}
