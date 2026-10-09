<?php

namespace Database\Seeders;

use App\Models\Appointment;
use Illuminate\Database\Seeder;

class AppointmentDummySeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // ===== Hari ini =====
            ['offset' => 0, 'waktu' => '10:00', 'nama' => 'Andini Putri', 'no_wa' => '+6281311100001', 'layanan' => [['Cuci Catok / Styling + Blow', 'Styling']]],
            ['offset' => 0, 'waktu' => '10:00', 'nama' => 'Citra Lestari', 'no_wa' => '+6281311100003', 'layanan' => [['Potong Wanita', 'Potong'], ['Hairmask', 'Treatment']]],
            ['offset' => 0, 'waktu' => '11:30', 'nama' => 'Bella Safira', 'no_wa' => '+6281311100002', 'layanan' => [['Smoothing', 'Treatment Rambut']]],
            ['offset' => 0, 'waktu' => '13:00', 'nama' => 'Farhan Maulana', 'no_wa' => '+6281311100006', 'layanan' => [['Potong Pria + Cuci + Blow', 'Potong']]],
            ['offset' => 0, 'waktu' => '14:30', 'nama' => 'Intan Permata', 'no_wa' => '+6281311100009', 'layanan' => [['Basic Color', 'Warna Rambut']]],
            ['offset' => 0, 'waktu' => '16:00', 'nama' => 'Dinda Ayu', 'no_wa' => '+6281311100004', 'layanan' => [['Creambath', 'Treatment'], ['Potong Poni', 'Potong']]],

            // ===== Besok =====
            ['offset' => 1, 'waktu' => '09:30', 'nama' => 'Eka Ramadhani', 'no_wa' => '+6281311100005', 'layanan' => [['Fashion Color', 'Warna Rambut']]],
            ['offset' => 1, 'waktu' => '13:30', 'nama' => 'Gilang Prakasa', 'no_wa' => '+6281311100007', 'layanan' => [['Keratin Premium', 'Treatment Rambut']]],
            ['offset' => 1, 'waktu' => '15:00', 'nama' => 'Nadya Kusuma', 'no_wa' => '+6281311199991', 'layanan' => [['Highlight', 'Warna Rambut'], ['Cuci Catok / Styling + Blow', 'Styling']]],
            ['offset' => 1, 'waktu' => '17:00', 'nama' => 'Hendra Wijaya', 'no_wa' => '+6281311100008', 'layanan' => [['Potong Pria', 'Potong']]],

            // ===== Lusa sampai +4 hari =====
            ['offset' => 2, 'waktu' => '10:30', 'nama' => 'Joko Susilo', 'no_wa' => '+6281311100010', 'layanan' => [['Potong Pria + Cuci + Blow', 'Potong'], ['Hair Detox', 'Treatment']]],
            ['offset' => 2, 'waktu' => '14:00', 'nama' => 'Salsabila Zahra', 'no_wa' => '+6281311199992', 'layanan' => [['Basic Color Two Apply', 'Warna Rambut']]],
            ['offset' => 3, 'waktu' => '11:00', 'nama' => 'Rani Maharani', 'no_wa' => '+6281311199993', 'layanan' => [['Smoothing', 'Treatment Rambut'], ['Potong Wanita + Cuci + Blow', 'Potong']]],
            ['offset' => 3, 'waktu' => '16:30', 'nama' => 'Bayu Nugroho', 'no_wa' => '+6281311199994', 'layanan' => [['Cold Perm', 'Treatment Rambut']]],
            ['offset' => 4, 'waktu' => '09:00', 'nama' => 'Maya Anggraini', 'no_wa' => '+6281311199995', 'layanan' => [['Fashion Color', 'Warna Rambut']]],
            ['offset' => 5, 'waktu' => '13:00', 'nama' => 'Tasya Amelia', 'no_wa' => '+6281311199996', 'layanan' => [['Keratin Premium', 'Treatment Rambut'], ['Potong Wanita', 'Potong']]],
            ['offset' => 6, 'waktu' => '15:30', 'nama' => 'Dewi Sinta', 'no_wa' => '+6281311199997', 'layanan' => [['Cuci Catok / Styling + Blow', 'Styling']]],
        ];

        foreach ($data as $baris) {
            $tanggal = now()->addDays($baris['offset'])->toDateString();

            Appointment::updateOrCreate(
                [
                    'tanggal' => $tanggal,
                    'waktu' => $baris['waktu'],
                    'nama' => $baris['nama'],
                ],
                [
                    'service' => array_column($baris['layanan'], 0),
                    'kategori' => array_column($baris['layanan'], 1),
                    'no_wa' => $baris['no_wa'],
                ]
            );
        }

        $this->command->info('Appointment dummy dibuat: '.count($data).' janji temu (hari ini s/d +6 hari).');
    }
}