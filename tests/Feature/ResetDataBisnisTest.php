<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ResetDataBisnisTest extends TestCase
{
    use DatabaseTruncation;

    /**
     * Isi satu baris untuk setiap kelompok tabel agar reset benar-benar teruji.
     */
    private function seedData(): array
    {
        $now = now();

        $userId = DB::table('users')->insertGetId([
            'name' => 'Owner Uji',
            'username' => 'owner-uji',
            'email' => 'owner-uji@example.test',
            'password' => Hash::make('rahasia'),
            'role' => 'owner',
            'aktif' => true,
            'wajib_ganti_password' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $layananId = DB::table('layanan')->insertGetId([
            'nama_layanan' => 'Potong Uji',
            'kategori' => 'Potong',
            'aktif' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $hargaLayananId = DB::table('harga_layanan')->insertGetId([
            'id_layanan' => $layananId,
            'varian' => 'S',
            'harga_dasar_min' => 50000,
            'harga_dasar_max' => 75000,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('default_produk_layanan')->insert([
            'id_harga_layanan' => $hargaLayananId,
            'kategori_produk' => 'Color',
            'default_ml' => 60,
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

        $merekId = DB::table('merek')->insertGetId([
            'nama' => 'Merek Uji',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('layanan_produk')->insert([
            'id_layanan' => $layananId,
            'id_produk' => $produkId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('pembelian_produk')->insert([
            'produk_id' => $produkId,
            'qty' => 10,
            'harga_beli' => 5000,
            'harga_modal_rata_rata' => 500,
            'stok_setelah' => 10,
            'dicatat_oleh' => $userId,
            'tanggal' => $now->toDateString(),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('riwayat_stok_produk')->insert([
            'produk_id' => $produkId,
            'jenis' => 'restock',
            'perubahan' => 10,
            'stok_akhir' => 10,
            'harga_modal_rata_rata' => 500,
            'dicatat_oleh' => $userId,
            'tanggal' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $karyawanId = DB::table('karyawans')->insertGetId([
            'nama' => 'Stylist Uji',
            'kontak' => '081200000001',
            'skema_komisi' => 'per_layanan',
            'gaji_pokok' => 1000000,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Karyawan yang sudah dihapus (soft delete) tetap harus ikut aturan reset.
        DB::table('karyawans')->insert([
            'nama' => 'Stylist Lama',
            'skema_komisi' => 'per_layanan',
            'gaji_pokok' => 0,
            'deleted_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $pelangganId = DB::table('pelanggans')->insertGetId([
            'nama' => 'Pelanggan Uji',
            'jenis_kelamin' => 'P',
            'no_wa' => '081200000002',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Pelanggan soft-deleted juga harus terhapus oleh reset.
        DB::table('pelanggans')->insert([
            'nama' => 'Pelanggan Lama',
            'no_wa' => '081200000003',
            'deleted_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $transaksiId = DB::table('transaksi_kunjungan')->insertGetId([
            'id_pelanggan' => $pelangganId,
            'jenis_pengerjaan' => 'sendiri',
            'no_struk' => 'INV-UJI-001',
            'waktu_kunjungan' => $now,
            'total_bayar' => 50000,
            'metode_pembayaran' => 'cash',
            'status' => 'selesai',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $detailId = DB::table('detail_transaksi')->insertGetId([
            'id_transaksi' => $transaksiId,
            'id_staf' => $karyawanId,
            'tipe_item' => 'layanan',
            'id_layanan' => $layananId,
            'qty' => 1,
            'harga_saat_transaksi' => 50000,
            'subtotal' => 50000,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('detail_transaksi_produk')->insert([
            'id_detail_transaksi' => $detailId,
            'id_produk' => $produkId,
            'pemakaian_ml' => 10,
            'harga_per_unit' => 1000,
            'subtotal' => 10000,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('komisi_transaksi')->insert([
            'id_transaksi' => $transaksiId,
            'id_staf' => $karyawanId,
            'jumlah_komisi' => 5000,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('komisi_harian_spesial')->insert([
            'id_staf' => $karyawanId,
            'tanggal' => $now->toDateString(),
            'total_omset_dasar' => 50000,
            'persen' => 10,
            'jumlah_komisi' => 5000,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $appointmentId = DB::table('appointments')->insertGetId([
            'id_pelanggan' => $pelangganId,
            'id_karyawan' => $karyawanId,
            'tanggal' => $now->toDateString(),
            'jam_mulai' => '10:00:00',
            'durasi_menit' => 60,
            'status' => 'terkonfirmasi',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('appointment_layanan')->insert([
            'appointment_id' => $appointmentId,
            'layanan_id' => $layananId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('absensis')->insert([
            'id_staf' => $karyawanId,
            'tanggal' => $now->toDateString(),
            'hadir' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('insight_ai')->insert([
            'periode' => $now->toDateString(),
            'data_ringkasan' => json_encode(['omset_bulan_ini' => 50000]),
            'konten_insight' => json_encode(['headline' => 'Uji']),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('pertanyaan_ai')->insert([
            'sesi_id' => 'sesi-uji',
            'periode' => $now->toDateString(),
            'pertanyaan' => 'Pertanyaan uji?',
            'jawaban' => 'Jawaban uji.',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('sessions')->insert([
            'id' => 'sesi-login-uji',
            'user_id' => $userId,
            'payload' => 'payload',
            'last_activity' => $now->timestamp,
        ]);

        DB::table('cache')->insert([
            'key' => 'cache-uji',
            'value' => 'value',
            'expiration' => $now->timestamp + 3600,
        ]);

        DB::table('password_reset_tokens')->insert([
            'email' => 'owner-uji@example.test',
            'token' => 'token-uji',
            'created_at' => $now,
        ]);

        return [
            'users' => $userId,
            'layanan' => $layananId,
            'harga_layanan' => $hargaLayananId,
            'produk' => $produkId,
            'merek' => $merekId,
            'karyawan' => $karyawanId,
        ];
    }

    private function masterCounts(): array
    {
        return [
            'users' => DB::table('users')->count(),
            'layanan' => DB::table('layanan')->count(),
            'harga_layanan' => DB::table('harga_layanan')->count(),
            'default_produk_layanan' => DB::table('default_produk_layanan')->count(),
            'layanan_produk' => DB::table('layanan_produk')->count(),
            'produk' => DB::table('produk')->count(),
            'merek' => DB::table('merek')->count(),
            'pembelian_produk' => DB::table('pembelian_produk')->count(),
            'riwayat_stok_produk' => DB::table('riwayat_stok_produk')->count(),
        ];
    }

    public function test_dry_run_tidak_mengubah_data(): void
    {
        $this->seedData();

        $sebelum = $this->masterCounts();
        $pelangganSebelum = DB::table('pelanggans')->count();
        $transaksiSebelum = DB::table('transaksi_kunjungan')->count();
        $sesiSebelum = DB::table('sessions')->count();
        $cacheSebelum = DB::table('cache')->count();

        $this->artisan('data:reset', [
            '--dry-run' => true,
            '--keep-karyawan' => true,
            '--no-backup' => true,
        ])
            ->expectsOutputToContain('Mode --dry-run')
            ->assertSuccessful();

        $this->assertSame($sebelum, $this->masterCounts());
        $this->assertSame($pelangganSebelum, DB::table('pelanggans')->count());
        $this->assertSame($transaksiSebelum, DB::table('transaksi_kunjungan')->count());
        $this->assertSame($sesiSebelum, DB::table('sessions')->count());
        $this->assertSame($cacheSebelum, DB::table('cache')->count());
    }

    public function test_reset_mengosongkan_grup_b_dan_sistem_tetapi_mempertahankan_master(): void
    {
        $this->seedData();

        $sebelum = $this->masterCounts();

        $this->artisan('data:reset', [
            '--force' => true,
            '--keep-karyawan' => true,
            '--no-backup' => true,
        ])->assertSuccessful();

        foreach ([
            'pelanggans',
            'transaksi_kunjungan',
            'detail_transaksi',
            'detail_transaksi_produk',
            'komisi_transaksi',
            'komisi_harian_spesial',
            'appointments',
            'appointment_layanan',
            'absensis',
            'insight_ai',
            'pertanyaan_ai',
            'sessions',
            'cache',
            'cache_locks',
            'jobs',
            'job_batches',
            'failed_jobs',
            'password_reset_tokens',
        ] as $tabel) {
            $this->assertSame(0, DB::table($tabel)->count(), "Tabel {$tabel} seharusnya kosong.");
        }

        $this->assertSame($sebelum, $this->masterCounts());
        $this->assertSame(1, DB::table('riwayat_stok_produk')->count());
    }

    public function test_keep_karyawan_mempertahankan_termasuk_yang_soft_deleted(): void
    {
        $this->seedData();

        $this->artisan('data:reset', [
            '--force' => true,
            '--keep-karyawan' => true,
            '--no-backup' => true,
        ])->assertSuccessful();

        $this->assertSame(2, DB::table('karyawans')->count());
        $this->assertSame(1, DB::table('karyawans')->whereNotNull('deleted_at')->count());
    }

    public function test_reset_karyawan_mengosongkan_termasuk_yang_soft_deleted(): void
    {
        $this->seedData();

        $this->artisan('data:reset', [
            '--force' => true,
            '--reset-karyawan' => true,
            '--no-backup' => true,
        ])->assertSuccessful();

        $this->assertSame(0, DB::table('karyawans')->count());
        // Master lain tetap utuh.
        $this->assertSame(1, DB::table('layanan')->count());
        $this->assertSame(1, DB::table('produk')->count());
    }

    public function test_reset_riwayat_stok_opsional(): void
    {
        $this->seedData();

        $this->artisan('data:reset', [
            '--force' => true,
            '--keep-karyawan' => true,
            '--reset-riwayat-stok' => true,
            '--no-backup' => true,
        ])->assertSuccessful();

        $this->assertSame(0, DB::table('riwayat_stok_produk')->count());
        $this->assertSame(1, DB::table('pembelian_produk')->count());
    }

    public function test_non_interaktif_tanpa_flag_karyawan_ditolak(): void
    {
        $this->seedData();

        $this->artisan('data:reset', [
            '--force' => true,
            '--no-backup' => true,
            '--no-interaction' => true,
        ])->assertFailed();

        // Tidak ada keputusan diam-diam: data tetap utuh.
        $this->assertSame(1, DB::table('pelanggans')->whereNull('deleted_at')->count());
        $this->assertSame(2, DB::table('karyawans')->count());
    }

    public function test_production_tanpa_force_ditolak(): void
    {
        $this->seedData();

        $this->app->detectEnvironment(fn () => 'production');

        $this->artisan('data:reset', [
            '--keep-karyawan' => true,
            '--no-backup' => true,
        ])->assertFailed();

        $this->assertSame(1, DB::table('transaksi_kunjungan')->count());
        $this->assertSame(1, DB::table('users')->count());
    }

    public function test_konfirmasi_harus_mengetik_reset_persis(): void
    {
        $this->seedData();

        $this->artisan('data:reset', [
            '--keep-karyawan' => true,
            '--no-backup' => true,
        ])
            ->expectsQuestion('Ketik RESET (huruf besar) untuk melanjutkan penghapusan', 'reset')
            ->assertFailed();

        $this->assertSame(1, DB::table('transaksi_kunjungan')->count());
    }

    public function test_konfirmasi_reset_menjalankan_penghapusan(): void
    {
        $this->seedData();

        $this->artisan('data:reset', [
            '--keep-karyawan' => true,
            '--no-backup' => true,
        ])
            ->expectsQuestion('Ketik RESET (huruf besar) untuk melanjutkan penghapusan', 'RESET')
            ->assertSuccessful();

        $this->assertSame(0, DB::table('transaksi_kunjungan')->count());
    }

    public function test_user_seeder_tidak_menimpa_akun_yang_sudah_ada(): void
    {
        $user = User::create([
            'name' => 'Owner Lama',
            'username' => 'owner',
            'password' => 'password-lama',
            'role' => 'owner',
            'aktif' => true,
            'wajib_ganti_password' => false,
        ]);

        $hashLama = $user->password;

        $this->artisan('db:seed', ['--class' => UserSeeder::class, '--force' => true])
            ->assertSuccessful();

        $this->assertSame($hashLama, $user->fresh()->password);
        $this->assertSame('Owner Lama', $user->fresh()->name);
    }

    public function test_user_seeder_tidak_membuat_akun_di_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        $this->artisan('db:seed', ['--class' => UserSeeder::class, '--force' => true])
            ->assertSuccessful();

        $this->assertSame(0, DB::table('users')->count());
    }

    public function test_database_seeder_tidak_menjalankan_seeder_uji_di_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        $this->artisan('db:seed', ['--force' => true])->assertSuccessful();

        $this->assertSame(0, DB::table('layanan')->count());
        $this->assertSame(0, DB::table('produk')->count());
        $this->assertSame(0, DB::table('users')->count());
    }
}
