<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'owner@afwo.com'],
            [
                'name' => 'Owner Afwo',
                'role' => 'owner',
                'password' => Hash::make('password'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'kasir@afwo.com'],
            [
                'name' => 'Kasir Afwo',
                'role' => 'kasir',
                'password' => Hash::make('password'),
            ]
        );
    }
}
