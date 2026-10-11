<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Karyawan;
use App\Models\Layanan;
use App\Models\Pelanggan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AppointmentDummySeeder extends Seeder
{
    public function run(): void
    {
        $pelanggans = Pelanggan::orderBy('id')->get();
        $karyawans = Karyawan::orderBy('id')->get();
        $layanans = Layanan::where('aktif', true)->orderBy('id')->get();

        if ($pelanggans->isEmpty() || $karyawans->isEmpty() || $layanans->isEmpty()) {
            $this->command->error('Data pelanggan/karyawan/layanan belum ada. Jalankan TransaksiDummySeeder dulu.');

            return;
        }

        // Bersihkan sisa appointment lama yang tidak lengkap (warisan skema lama).
        $orphan = Appointment::whereNull('id_pelanggan')->orWhereNull('id_karyawan')->delete();
        if ($orphan > 0) {
            $this->command->info("Menghapus {$orphan} appointment lama yang tidak lengkap.");
        }

        $awalBulan = Carbon::now()->startOfMonth();
        $akhirBulan = Carbon::now()->endOfMonth();
        $hariIni = Carbon::now()->startOfDay();

        $jamSlot = [
            '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
            '13:00', '13:30', '14:00', '14:30', '15:00', '15:30',
            '16:00', '16:30', '17:00', '17:30', '18:00', '18:30', '19:00',
        ];
        $durasiPilihan = [30, 60, 90, 120, 150, 180];
        $preferensiList = [
            'Rambut tebal, minta stylist yang sabar.',
            'Alergi pewarna amonia, pakai yang bebas amonia.',
            'Suka hasil blow yang bervolume.',
            'Rambut sensitif, hindari panas tinggi.',
            'Konsultasi warna dulu sebelum diwarnai.',
            'Bawa referensi foto dari Instagram.',
            'Potongan pendek rapi untuk keperluan kerja.',
            'Minta jadwal tidak terlalu lama.',
            null,
            null,
        ];

        $pelangganIdx = 0;
        $karyawanIdx = 0;
        $layananIdx = 0;
        $total = 0;
        $no = 0;

        for ($tgl = $awalBulan->copy(); $tgl->lte($akhirBulan); $tgl->addDay()) {
            // Salon tutup hari Minggu.
            if ($tgl->isSunday()) {
                continue;
            }

            $jumlahHariIni = $tgl->isSaturday() ? 3 : (($tgl->day % 3) + 1);

            for ($i = 0; $i < $jumlahHariIni; $i++) {
                $no++;
                $jam = $jamSlot[($tgl->day * 3 + $i * 5) % count($jamSlot)];

                $pelanggan = $pelanggans[$pelangganIdx++ % $pelanggans->count()];
                $karyawan = $karyawans[$karyawanIdx++ % $karyawans->count()];

                // 1 - 2 layanan per appointment.
                $jumlahLayanan = ($no % 4 === 0) ? 2 : 1;
                $layananTerpilih = [];
                for ($j = 0; $j < $jumlahLayanan; $j++) {
                    $layananTerpilih[] = $layanans[$layananIdx++ % $layanans->count()];
                }

                $status = $tgl->lt($hariIni) ? 'selesai' : 'terkonfirmasi';

                $appointment = Appointment::updateOrCreate(
                    [
                        'tanggal' => $tgl->toDateString(),
                        'jam_mulai' => $jam,
                        'id_pelanggan' => $pelanggan->id,
                        'id_karyawan' => $karyawan->id,
                    ],
                    [
                        'durasi_menit' => $durasiPilihan[($no + $jumlahLayanan) % count($durasiPilihan)],
                        'preferensi' => $preferensiList[$no % count($preferensiList)],
                        'status' => $status,
                    ]
                );

                $appointment->layanans()->sync(collect($layananTerpilih)->pluck('id')->unique()->all());

                $total++;
            }
        }

        $this->command->info('Appointment dummy dibuat: '.$total.' janji temu untuk bulan '.$awalBulan->format('F Y').'.');
    }
}
