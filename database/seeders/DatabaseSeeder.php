<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(UserSeeder::class);
        $this->call(LayananHargaKomisiSeeder::class);
        $this->call(ProdukDummySeeder::class);
        $this->call(LayananProdukSeeder::class);
        $this->call(TransaksiDummySeeder::class);
    }
}
