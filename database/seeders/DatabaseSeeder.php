<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // UserSeeder punya guard sendiri: tidak melakukan apa pun di production.
        $this->call(UserSeeder::class);

        if (app()->environment('production')) {
            // Seeder di bawah mengisi data contoh / membuat baris baru setiap dijalankan.
            // Jangan dipanggil otomatis di production agar tidak menimpa atau menggandakan
            // harga layanan, produk, dan data operasional yang sudah diubah owner.
            $this->command?->warn('Seeder data uji & master non-idempotent dilewati di production.');
            $this->command?->line('Jalankan seeder tertentu secara manual hanya setelah datanya direview.');

            return;
        }

        $this->call(LayananHargaKomisiSeeder::class);
        $this->call(ProdukDummySeeder::class);
        $this->call(LayananProdukSeeder::class);
        $this->call(DefaultProdukLayananSeeder::class);
        $this->call(TransaksiDummySeeder::class);
        $this->call(AppointmentDummySeeder::class);
        $this->call(AbsensiDummySeeder::class);
    }
}
