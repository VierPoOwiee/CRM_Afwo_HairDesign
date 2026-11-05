<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // PENTING: seeder ini TIDAK boleh membuat/menimpa akun di production.
        // Password default di bawah hanya untuk data awal/testing di non-production.
        if (app()->environment('production')) {
            $this->command?->info('UserSeeder dilewati di production. Akun yang sudah ada tidak diubah.');

            return;
        }

        // firstOrCreate: jangan menimpa user/password yang sudah pernah dibuat.
        User::firstOrCreate(
            ['username' => 'owner'],
            [
                'name' => 'Owner Afwo',
                'email' => null,
                'role' => 'owner',
                'password' => Hash::make('12345678'),
            ]
        );

        User::firstOrCreate(
            ['username' => 'kasir'],
            [
                'name' => 'Kasir Afwo',
                'email' => null,
                'role' => 'kasir',
                'password' => Hash::make('ganti-password-ini'),
            ]
        );
    }
}
