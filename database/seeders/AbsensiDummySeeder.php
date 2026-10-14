<?php

namespace Database\Seeders;

use App\Models\Absensi;
use App\Models\Karyawan;
use Illuminate\Database\Seeder;

class AbsensiDummySeeder extends Seeder
{
    public function run(): void
    {
        $karyawan = Karyawan::all();

        if ($karyawan->isEmpty()) {
            $this->command->error('Belum ada karyawan. Jalankan TransaksiDummySeeder dulu.');

            return;
        }

        $awalBulan = now()->startOfMonth();
        $hariIni = now()->startOfDay();

        $totalDiisi = 0;

        for ($tanggal = $awalBulan->copy(); $tanggal->lte($hariIni); $tanggal->addDay()) {
            // Libur Minggu
            if ($tanggal->isSunday()) {
                continue;
            }

            // Kadang Sabtu hanya separuh staf masuk
            $sabtu = $tanggal->isSaturday();

            foreach ($karyawan as $k) {
                $hadir = true;

                // 1-2 karyawan sesekali tidak masuk di hari kerja
                if (! $sabtu && (($k->id + $tanggal->day) % 11 === 0 || ($k->id + $tanggal->day) % 17 === 0)) {
                    $hadir = false;
                }

                // Sabtu: separuh karyawan masuk
                if ($sabtu) {
                    $hadir = $k->id % 2 === 0;
                }

                Absensi::updateOrCreate(
                    ['id_staf' => $k->id, 'tanggal' => $tanggal->toDateString()],
                    ['hadir' => $hadir]
                );

                $totalDiisi++;
            }
        }

        $this->command->info('Absensi dummy dibuat: '.$totalDiisi.' catatan kehadiran dari awal bulan sampai hari ini.');
    }
}