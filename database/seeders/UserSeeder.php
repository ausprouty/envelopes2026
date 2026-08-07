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
            ['email' => 'bob@hereslife.com'],
            [
                'name' => 'Bob',
                'password' => Hash::make('ChangeMe-Bob-2026'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'Ryan.Prouty@icloud.com'],
            [
                'name' => 'Ryan',
                'password' => Hash::make('ChangeMe-Ryan-2026'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
