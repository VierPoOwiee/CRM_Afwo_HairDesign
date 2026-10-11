<?php

namespace App\Console\Commands;

use App\Models\Karyawan;
use App\Models\KomisiHarianSpesial;
use Illuminate\Console\Command;

class HitungKomisiHarian extends Command
{
    protected $signature = 'komisi:hitung-harian {tanggal?}';
    protected $description = 'Hitung komisi harian untuk staf berskema persen_omset_harian. Default: hari ini.';

    public function handle(): int
    {
        $tanggal = $this->argument('tanggal') ?? now()->toDateString();

        $stafList = Karyawan::where('skema_komisi', 'persen_omset_harian')
            ->get();

        if ($stafList->isEmpty()) {
            $this->info('Tidak ada staf dengan skema persen_omset_harian.');
            return self::SUCCESS;
        }

        $this->info('Menghitung komisi harian untuk tanggal: ' . $tanggal);
        $this->newLine();

        foreach ($stafList as $staf) {
            KomisiHarianSpesial::calculateForDate($staf, $tanggal);
            $record = KomisiHarianSpesial::where('id_staf', $staf->id)
                ->where('tanggal', $tanggal)
                ->first();

            if ($record) {
                $this->line("  {$staf->nama}: Omset Rp" . number_format($record->total_omset_dasar, 0, ',', '.') .
                    " × {$record->persen}% = Rp" . number_format($record->jumlah_komisi, 0, ',', '.'));
            } else {
                $this->line("  {$staf->nama}: Tidak ada transaksi.");
            }
        }

        $this->newLine();
        $this->info('Selesai.');
        return self::SUCCESS;
    }
}
