<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use Throwable;

class ResetDataBisnis extends Command
{
    /**
     * Tabel yang selalu dipertahankan (master data & akun).
     */
    private const TABEL_DIPERTAHANKAN = [
        'users',
        'layanan',
        'harga_layanan',
        'default_produk_layanan',
        'layanan_produk',
        'produk',
        'merek',
        'pembelian_produk',
        'migrations',
    ];

    /**
     * Tabel transaksional yang selalu dikosongkan.
     */
    private const TABEL_DIRESET = [
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
    ];

    /**
     * Tabel sistem yang selalu dibersihkan agar sesi/login lama tidak tersisa.
     */
    private const TABEL_SISTEM = [
        'sessions',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
        'password_reset_tokens',
    ];

    /**
     * Tabel master yang jumlahnya wajib sama sebelum dan sesudah reset.
     */
    private const TABEL_VERIFIKASI = [
        'users',
        'layanan',
        'harga_layanan',
        'produk',
        'merek',
    ];

    protected $signature = 'data:reset
        {--dry-run : Tampilkan ringkasan rencana reset tanpa mengubah data}
        {--force : Lewati konfirmasi RESET (backup tetap dijalankan kecuali --no-backup)}
        {--keep-karyawan : Pertahankan data karyawans (termasuk yang soft-deleted)}
        {--reset-karyawan : Kosongkan data karyawans (termasuk yang soft-deleted)}
        {--reset-riwayat-stok : Ikut kosongkan riwayat_stok_produk}
        {--no-backup : Jangan buat backup otomatis sebelum reset}';

    protected $description = 'Mengosongkan data transaksional CRM tanpa menghapus master data dan akun pengguna.';

    public function handle(): int
    {
        $connection = DB::connection();
        $database = $connection->getDatabaseName();
        $driver = $connection->getDriverName();
        $host = config('database.connections.'.$connection->getName().'.host', '-');

        $this->info("Database: {$database} @ {$host} ({$driver})");

        try {
            $connection->getPdo();
        } catch (Throwable $e) {
            $this->error('Tidak bisa terhubung ke database: '.$e->getMessage());

            return self::FAILURE;
        }

        $production = app()->environment('production');

        if ($production && ! $this->option('force')) {
            $this->newLine();
            $this->error('PERINGATAN KERAS: APP_ENV=production dan opsi --force tidak diberikan.');
            $this->warn('Reset data transaksional di production hanya boleh dijalankan setelah backup manual dan dengan --force.');
            $this->line('Perintah dihentikan. Tidak ada data yang diubah.');

            return self::FAILURE;
        }

        [$resetKaryawan, $karyawanValid] = $this->resolveKaryawanOption();

        if (! $karyawanValid) {
            return self::FAILURE;
        }

        $plan = $this->buildPlan($resetKaryawan);
        $sebelum = $this->snapshot($plan);

        $this->renderSummary($plan, $sebelum, $production);

        if ($this->option('dry-run')) {
            $this->newLine();
            $this->info('Mode --dry-run: ringkasan di atas hanya pratinjau. Tidak ada data yang diubah.');

            return self::SUCCESS;
        }

        if (! $this->option('force')) {
            // Mode non-interaktif tidak bisa meminta ketikan RESET; jangan menebak keputusan user.
            if (! $this->input->isInteractive()) {
                $this->newLine();
                $this->error('Mode non-interaktif tanpa --force tidak bisa meminta konfirmasi "RESET".');
                $this->line('Jalankan ulang dengan --force setelah yakin, atau jalankan langsung di terminal interaktif.');

                return self::FAILURE;
            }

            $jawaban = $this->ask('Ketik RESET (huruf besar) untuk melanjutkan penghapusan');

            if ($jawaban !== 'RESET') {
                $this->newLine();
                $this->warn('Reset dibatalkan: konfirmasi tidak cocok. Tidak ada data yang diubah.');

                return self::FAILURE;
            }
        } else {
            $this->line('Opsi --force aktif: konfirmasi ketik RESET dilewati.');
        }

        if (! $this->option('no-backup')) {
            $backup = $this->backupDatabase($database, $connection->getName());

            if (! $backup['ok']) {
                $this->newLine();
                $this->error('Backup otomatis GAGAL. Reset dibatalkan agar data tidak hilang tanpa cadangan.');
                $this->warn('Alasan: '.$backup['alasan']);
                $this->line('Lakukan backup manual dari terminal, misalnya:');
                $this->line('  mysqldump -u '.config('database.connections.'.$connection->getName().'.username', '<user>').' -p '.$database.' > backup_sebelum_reset.sql');
                $this->line('Setelah backup berhasil, jalankan ulang dengan --no-backup jika ingin melewati backup otomatis.');

                return self::FAILURE;
            }

            $this->info('Backup dibuat: '.$backup['path'].' ('.number_format($backup['ukuran'] / 1024, 1, ',', '.').' KB)');
        } else {
            $this->warn('Backup otomatis dilewati karena --no-backup. Pastikan Anda sudah punya backup manual.');
        }

        $this->newLine();
        $this->info('Mulai mengosongkan tabel...');

        $dihapus = [];
        $dilewati = [];

        try {
            // TRUNCATE di MySQL melakukan commit implisit dan mereset AUTO_INCREMENT.
            // Karena itu FOREIGN_KEY_CHECKS dimatikan sementara dan selalu dikembalikan di finally.
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            foreach ($plan['reset'] as $tabel) {
                if (! Schema::hasTable($tabel)) {
                    $dilewati[] = $tabel;
                    continue;
                }

                $jumlah = (int) DB::table($tabel)->count();
                DB::table($tabel)->truncate();
                $dihapus[$tabel] = $jumlah;
            }
        } catch (Throwable $e) {
            $this->newLine();
            $this->error('Reset gagal di tengah proses: '.$e->getMessage());
            $this->warn('Periksa kembali database dan backup sebelum mencoba lagi.');

            Log::error('data:reset gagal', [
                'waktu' => now()->toIso8601String(),
                'database' => $database,
                'error' => $e->getMessage(),
                'sudah_dikosongkan' => $dihapus,
            ]);

            return self::FAILURE;
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $sesudah = $this->snapshot($plan);
        $perubahanMaster = $this->verifyPreservedCounts($plan, $sebelum, $sesudah);

        $this->newLine();
        $this->renderAfterSummary($plan, $sebelum, $sesudah);

        if ($dilewati !== []) {
            $this->warn('Tabel berikut tidak ada di database sehingga dilewati: '.implode(', ', $dilewati));
        }

        Log::warning('data:reset dijalankan', [
            'waktu' => now()->toIso8601String(),
            'database' => $database,
            'environment' => app()->environment(),
            'opsi' => [
                'dry_run' => false,
                'force' => (bool) $this->option('force'),
                'keep_karyawan' => ! $resetKaryawan,
                'reset_karyawan' => $resetKaryawan,
                'reset_riwayat_stok' => (bool) $this->option('reset-riwayat-stok'),
                'no_backup' => (bool) $this->option('no-backup'),
            ],
            'baris_dihapus' => $dihapus,
            'tabel_dilewati' => $dilewati,
        ]);

        if ($perubahanMaster !== []) {
            $this->newLine();
            $this->error('VERIFIKASI GAGAL: jumlah tabel master berubah setelah reset!');
            foreach ($perubahanMaster as $tabel => $info) {
                $this->error("- {$tabel}: sebelum {$info['sebelum']}, sesudah {$info['sesudah']}");
            }
            $this->warn('Jangan gunakan aplikasi sebelum memulihkan database dari backup.');

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Verifikasi berhasil: jumlah users, layanan, harga_layanan, produk, dan merek tidak berubah.');

        $this->newLine();
        $this->warn('PENGINGAT PENTING:');
        $this->line('1. Stok produk dan sisa_penggunaan TIDAK dikembalikan. Lakukan stock opname manual lewat menu Produk (restock/penyesuaian) sesuai stok fisik asli.');
        $this->line('2. Semua user (termasuk yang menjalankan perintah ini) sudah logout dan harus login ulang.');
        $this->line('3. Jangan menjalankan php artisan db:seed di production; seeder data uji hanya untuk non-production.');

        $this->newLine();
        $this->info('Reset data transaksional selesai.');

        return self::SUCCESS;
    }

    /**
     * @return array{0: bool, 1: bool} [resetKaryawan, valid]
     */
    private function resolveKaryawanOption(): array
    {
        $keep = (bool) $this->option('keep-karyawan');
        $reset = (bool) $this->option('reset-karyawan');

        if ($keep && $reset) {
            $this->error('Opsi --keep-karyawan dan --reset-karyawan tidak boleh dipakai bersamaan.');

            return [false, false];
        }

        if ($reset) {
            return [true, true];
        }

        if ($keep) {
            return [false, true];
        }

        if (! $this->input->isInteractive()) {
            $this->newLine();
            $this->error('Mode non-interaktif tanpa keputusan karyawan akan dihentikan.');
            $this->line('Gunakan --keep-karyawan untuk mempertahankan stylist atau --reset-karyawan untuk mengosongkannya.');

            return [false, false];
        }

        $pertahankan = $this->confirm('Pertahankan data karyawan (stylist)?', true);

        return [! $pertahankan, true];
    }

    /**
     * @return array{reset: array<int, string>, aksi: array<string, string>, tabel: array<int, string>}
     */
    private function buildPlan(bool $resetKaryawan): array
    {
        $reset = array_merge(self::TABEL_DIRESET, self::TABEL_SISTEM);
        $aksi = [];

        foreach (self::TABEL_DIPERTAHANKAN as $tabel) {
            $aksi[$tabel] = 'PERTAHANKAN';
        }

        $aksi['riwayat_stok_produk'] = $this->option('reset-riwayat-stok') ? 'RESET' : 'PERTAHANKAN';

        if ($this->option('reset-riwayat-stok')) {
            $reset[] = 'riwayat_stok_produk';
        }

        foreach (self::TABEL_DIRESET as $tabel) {
            $aksi[$tabel] = 'RESET';
        }

        $aksi['karyawans'] = $resetKaryawan ? 'RESET' : 'PERTAHANKAN';

        if ($resetKaryawan) {
            $reset[] = 'karyawans';
        }

        foreach (self::TABEL_SISTEM as $tabel) {
            $aksi[$tabel] = 'RESET';
        }

        $tabel = array_keys($aksi);

        // Tabel lain yang tidak dikenal (mis. tabel backup lama) dibiarkan agar tidak merusak data.
        foreach ($this->allTables() as $nama) {
            if (! array_key_exists($nama, $aksi)) {
                $aksi[$nama] = 'DIBIARKAN';
                $tabel[] = $nama;
            }
        }

        return [
            'reset' => array_values(array_unique($reset)),
            'aksi' => $aksi,
            'tabel' => $tabel,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function allTables(): array
    {
        return collect(DB::select('SHOW TABLES'))
            ->map(fn ($row) => (string) array_values((array) $row)[0])
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @param  array{reset: array<int, string>, aksi: array<string, string>, tabel: array<int, string>}  $plan
     * @return array<string, int>
     */
    private function snapshot(array $plan): array
    {
        $hasil = [];

        foreach ($plan['tabel'] as $tabel) {
            if (Schema::hasTable($tabel)) {
                $hasil[$tabel] = (int) DB::table($tabel)->count();
            }
        }

        return $hasil;
    }

    /**
     * @param  array{reset: array<int, string>, aksi: array<string, string>, tabel: array<int, string>}  $plan
     * @param  array<string, int>  $sebelum
     */
    private function renderSummary(array $plan, array $sebelum, bool $production): void
    {
        $this->newLine();
        $this->line('Ringkasan rencana reset:');

        $baris = [];

        foreach ($plan['tabel'] as $tabel) {
            if (! Schema::hasTable($tabel)) {
                $baris[] = [$tabel, '-', 'LEWATI (tidak ada)'];
                continue;
            }

            $grup = match (true) {
                $plan['aksi'][$tabel] === 'DIBIARKAN' => '-',
                $tabel === 'karyawans' => 'C',
                in_array($tabel, self::TABEL_SISTEM, true) => 'D',
                in_array($tabel, self::TABEL_DIRESET, true) => 'B',
                default => 'A',
            };

            $baris[] = [$tabel, $grup, number_format($sebelum[$tabel] ?? 0, 0, ',', '.'), $plan['aksi'][$tabel]];
        }

        $this->table(['Tabel', 'Grup', 'Baris', 'Aksi'], $baris);

        if ($production) {
            $this->warn('APP_ENV=production terdeteksi. Reset hanya dijalankan karena --force diberikan.');
        }
    }

    /**
     * @param  array{reset: array<int, string>, aksi: array<string, string>, tabel: array<int, string>}  $plan
     * @param  array<string, int>  $sebelum
     * @param  array<string, int>  $sesudah
     */
    private function renderAfterSummary(array $plan, array $sebelum, array $sesudah): void
    {
        $this->line('Ringkasan sesudah reset:');

        $baris = [];

        foreach ($plan['tabel'] as $tabel) {
            if (! Schema::hasTable($tabel)) {
                continue;
            }

            $baris[] = [
                $tabel,
                number_format($sebelum[$tabel] ?? 0, 0, ',', '.'),
                number_format($sesudah[$tabel] ?? 0, 0, ',', '.'),
                $plan['aksi'][$tabel],
            ];
        }

        $this->table(['Tabel', 'Sebelum', 'Sesudah', 'Aksi'], $baris);
    }

    /**
     * @param  array{reset: array<int, string>, aksi: array<string, string>, tabel: array<int, string>}  $plan
     * @param  array<string, int>  $sebelum
     * @param  array<string, int>  $sesudah
     * @return array<string, array{sebelum: int, sesudah: int}>
     */
    private function verifyPreservedCounts(array $plan, array $sebelum, array $sesudah): array
    {
        $berubah = [];

        foreach (self::TABEL_VERIFIKASI as $tabel) {
            if (($plan['aksi'][$tabel] ?? null) !== 'PERTAHANKAN' || ! array_key_exists($tabel, $sebelum)) {
                continue;
            }

            $jumlahSesudah = $sesudah[$tabel] ?? (int) DB::table($tabel)->count();

            if ($jumlahSesudah !== $sebelum[$tabel]) {
                $berubah[$tabel] = ['sebelum' => $sebelum[$tabel], 'sesudah' => $jumlahSesudah];
            }
        }

        return $berubah;
    }

    /**
     * @return array{ok: bool, path?: string, ukuran?: int, alasan?: string}
     */
    private function backupDatabase(string $database, string $connectionName): array
    {
        $config = config('database.connections.'.$connectionName, []);

        $binary = $this->findMysqldump();

        if ($binary === null) {
            return [
                'ok' => false,
                'alasan' => 'binary mysqldump tidak ditemukan di PATH. Install MySQL client atau tambahkan folder bin MySQL ke PATH.',
            ];
        }

        $folder = storage_path('app/backups');
        File::ensureDirectoryExists($folder);

        $path = $folder.DIRECTORY_SEPARATOR.$database.'_'.now()->format('Ymd_His').'.sql';

        $args = [
            $binary,
            '--host='.($config['host'] ?? '127.0.0.1'),
            '--port='.($config['port'] ?? '3306'),
            '--user='.($config['username'] ?? 'root'),
            '--single-transaction',
            '--skip-lock-tables',
            '--no-tablespaces',
            '--default-character-set=utf8mb4',
            '--result-file='.$path,
            $database,
        ];

        if (! empty($config['unix_socket'])) {
            $args[] = '--socket='.$config['unix_socket'];
        }

        // Password hanya lewat environment MYSQL_PWD, tidak pernah masuk argumen/process list atau output.
        $env = [];

        if (($config['password'] ?? '') !== '') {
            $env['MYSQL_PWD'] = $config['password'];
        }

        try {
            $process = new Process($args, base_path(), $env ?: null, null, 300);
            $process->run();
        } catch (Throwable $e) {
            if (File::exists($path)) {
                File::delete($path);
            }

            return ['ok' => false, 'alasan' => $e->getMessage()];
        }

        if (! $process->isSuccessful()) {
            if (File::exists($path)) {
                File::delete($path);
            }

            $pesan = trim($process->getErrorOutput()) ?: 'mysqldump keluar dengan kode '.$process->getExitCode().'.';

            return ['ok' => false, 'alasan' => $pesan];
        }

        if (! File::exists($path) || File::size($path) === 0) {
            if (File::exists($path)) {
                File::delete($path);
            }

            return ['ok' => false, 'alasan' => 'File backup tidak terbentuk atau berukuran 0 byte.'];
        }

        return [
            'ok' => true,
            'path' => $path,
            'ukuran' => File::size($path),
        ];
    }

    /**
     * Cari mysqldump di PATH, lalu di lokasi umum XAMPP/Laragon/MySQL bila PATH belum diatur.
     */
    private function findMysqldump(): ?string
    {
        $binary = (new ExecutableFinder)->find('mysqldump');

        if ($binary !== null) {
            return $binary;
        }

        $kandidat = [
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            '/usr/local/mysql/bin/mysqldump',
            '/opt/homebrew/bin/mysqldump',
        ];

        foreach (array_merge(
            $kandidat,
            glob('C:\\laragon\\bin\\mysql\\*\\bin\\mysqldump.exe') ?: [],
            glob('C:\\Program Files\\MySQL\\MySQL Server *\\bin\\mysqldump.exe') ?: [],
        ) as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }
}
