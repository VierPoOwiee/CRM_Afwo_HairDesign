<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Karyawan;
use App\Models\Layanan;
use App\Models\Pelanggan;
use App\Models\Produk;
use App\Models\TransaksiKunjungan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthAccessTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    private function isiData(): array
    {
        $pelanggan = Pelanggan::create(['nama' => 'Klien', 'no_wa' => '+6289876543210']);
        $karyawan = Karyawan::create(['nama' => 'Staf', 'kontak' => '0812', 'skema_komisi' => 'per_layanan', 'persen_komisi_harian' => 0, 'gaji_pokok' => 0]);
        $layanan = Layanan::create(['nama_layanan' => 'Potong', 'kategori' => 'Potong', 'aktif' => true, 'termasuk_potong' => true]);
        $produk = Produk::create(['nama_produk' => 'Produk', 'merek' => 'M', 'kategori_produk' => 'dijual', 'satuan' => 'pcs', 'harga_per_satuan' => 10000, 'harga_modal_rata_rata' => 5000, 'stok' => 5, 'aktif' => true]);
        $transaksi = TransaksiKunjungan::create(['id_pelanggan' => $pelanggan->id, 'jenis_pengerjaan' => 'sendiri', 'no_struk' => TransaksiKunjungan::generateNoStruk(), 'waktu_kunjungan' => now(), 'metode_pembayaran' => 'cash', 'total_bayar' => 10000, 'status' => 'selesai']);
        $appointment = Appointment::create(['id_pelanggan' => $pelanggan->id, 'id_karyawan' => $karyawan->id, 'tanggal' => '2026-10-20', 'jam_mulai' => '10:00', 'durasi_menit' => 60]);

        return compact('pelanggan', 'karyawan', 'layanan', 'produk', 'transaksi', 'appointment');
    }

    private function routeSemuaRole(array $d): array
    {
        return [
            ['get', route('pelanggan.index'), 200],
            ['get', route('pelanggan.create'), 200],
            ['get', route('pelanggan.show', $d['pelanggan']), 200],
            ['get', route('pelanggan.edit', $d['pelanggan']), 200],
            ['get', route('pelanggan.arsip'), 200],
            ['get', route('layanan.index'), 200],
            ['get', route('layanan.create'), 200],
            ['get', route('layanan.show', $d['layanan']), 200],
            ['get', route('layanan.edit', $d['layanan']), 200],
            ['get', route('produk.index'), 200],
            ['get', route('produk.create'), 200],
            ['get', route('produk.show', $d['produk']), 200],
            ['get', route('produk.edit', $d['produk']), 200],
            ['get', route('produk.restock-form', $d['produk']), 200],
            ['get', route('karyawan.index'), 200],
            ['get', route('karyawan.create'), 200],
            ['get', route('karyawan.edit', $d['karyawan']), 200],
            ['get', route('karyawan.arsip'), 200],
            ['get', route('appointment.index'), 200],
            ['get', route('appointment.create'), 200],
            ['get', route('appointment.show', $d['appointment']), 200],
            ['get', route('appointment.edit', $d['appointment']), 200],
            ['get', route('appointment.hari', '2026-10-20'), 200],
            ['get', route('absensi.index'), 200],
            ['get', route('transaksi.index'), 200],
            ['get', route('transaksi.create'), 200],
            ['get', route('profile.show'), 200],
        ];
    }

    private function routeOwnerOnly(array $d): array
    {
        return [
            ['get', route('dashboard'), 200],
            ['get', route('pengguna.index'), 200],
            ['get', route('laporan.penjualan'), 200],
            ['get', route('laporan.pelanggan-aktif'), 200],
            ['get', route('laporan.pelanggan-riwayat', $d['pelanggan']), 200],
            ['get', route('laporan.rekap-komisi'), 200],
            ['get', route('laporan.pendapatan-karyawan'), 200],
            ['get', route('laporan.keuntungan-produk'), 200],
            ['get', route('laporan.rekap-komisi.cetak'), 200],
            ['get', route('laporan.rekap-komisi.slip', $d['karyawan']), 200],
            ['get', route('laporan-komisi.index'), 302],
        ];
    }

    public function test_tamu_dialihkan_ke_login(): void
    {
        $this->get('/pelanggan')->assertRedirect('/login');
        $this->get('/transaksi')->assertRedirect('/login');
        $this->get(route('login'))->assertOk();
    }

    public function test_kasir_akses_semua_halaman_operasional(): void
    {
        $this->actingAs($this->user('kasir'));
        $d = $this->isiData();

        foreach ($this->routeSemuaRole($d) as [$method, $url, $expected]) {
            $this->{$method}($url)->assertStatus($expected);
        }
    }

    public function test_kasir_dilarang_masuk_dashboard_dan_laporan(): void
    {
        $this->actingAs($this->user('kasir'));
        $d = $this->isiData();

        $this->get(route('dashboard'))->assertForbidden();
        $this->get(route('pengguna.index'))->assertForbidden();

        foreach ($this->routeOwnerOnly($d) as [$method, $url, $expected] ) {
            if ($method !== 'get' || $expected === 302) {
                continue; // laporan-komisi lama adalah redirect 302, bukan 403
            }
            $this->{$method}($url)->assertForbidden();
        }
    }

    public function test_owner_akses_dashboard_dan_laporan(): void
    {
        $this->actingAs($this->user('owner'));
        $d = $this->isiData();

        foreach ($this->routeSemuaRole($d) as [$method, $url] ) {
            $this->{$method}($url)->assertStatus(200);
        }
        foreach ($this->routeOwnerOnly($d) as [$method, $url, $expected]) {
            $this->{$method}($url)->assertStatus($expected);
        }
    }

    public function test_laporan_komisi_lama_redirect_ke_rekap_komisi(): void
    {
        $owner = $this->user('owner');
        $this->actingAs($owner);

        $this->get(route('laporan-komisi.index'))
            ->assertRedirect(route('laporan.rekap-komisi'));
    }

    public function test_login_benar_menurut_peran(): void
    {
        User::factory()->create(['username' => 'ow', 'password' => 'secret123', 'role' => 'owner']);
        User::factory()->create(['username' => 'ks', 'password' => 'secret123', 'role' => 'kasir']);

        $this->post(route('login.post'), ['username' => 'OW', 'password' => 'secret123'])
            ->assertRedirect(route('dashboard'));

        $this->post(route('logout'))->assertRedirect(route('login'));

        $this->post(route('login.post'), ['username' => 'ks', 'password' => 'secret123'])
            ->assertRedirect(route('pelanggan.index'));
    }

    public function test_login_gagal_dibatasi_rate_limiter(): void
    {
        User::factory()->create(['username' => 'ks', 'password' => 'secret123', 'role' => 'kasir']);

        RateLimiter::clear('ks|'.$this->app['request']->ip());

        for ($i = 0; $i < 6; $i++) {
            $this->post(route('login.post'), ['username' => 'ks', 'password' => 'salah']);
        }

        $response = $this->post(route('login.post'), ['username' => 'ks', 'password' => 'secret123']);
        $response->assertSessionHasErrors('username');
        $this->assertStringContainsString('Terlalu banyak percobaan', session('errors')->first('username'));
    }

    public function test_akun_nonaktif_tidak_bisa_login(): void
    {
        User::factory()->create(['username' => 'nonaktif', 'password' => 'secret123', 'role' => 'kasir', 'aktif' => false]);

        $this->post(route('login.post'), ['username' => 'nonaktif', 'password' => 'secret123'])
            ->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_user_wajib_ganti_password_diarahkan_ganti_password(): void
    {
        User::factory()->create(['username' => 'baru', 'password' => 'secret123', 'role' => 'kasir', 'wajib_ganti_password' => true]);

        $this->post(route('login.post'), ['username' => 'baru', 'password' => 'secret123'])
            ->assertRedirect(route('profile.show').'#ganti-password');
    }
}