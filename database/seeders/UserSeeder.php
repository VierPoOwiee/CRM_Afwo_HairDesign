<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // PENTING: password di bawah hanya untuk data awal/testing.
        // Wajib diganti setiap user setelah login pertama.
        User::updateOrCreate(
            ['username' => 'owner'],
            [
                'name' => 'Owner Afwo',
                'email' => null,
                'role' => 'owner',
                'password' => Hash::make('ganti-password-ini'),
            ]
        );

        User::updateOrCreate(
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
