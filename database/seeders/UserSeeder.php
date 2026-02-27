<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Superuser / Admin
        |--------------------------------------------------------------------------
        */
        User::updateOrCreate(
            ['email' => 'ale@ale.com'],
            [
                'name'              => 'alessandro',
                'is_staff'          => true,
                'is_superuser'      => true,
                'email_verified_at' => now(),
                'password'          => Hash::make('Alessandro1*'), // dev only
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Staff (non superuser)
        |--------------------------------------------------------------------------
        */
        User::updateOrCreate(
            ['email' => 'matteo@matteo.com'],
            [
                'name'              => 'matteo',
                'is_staff'          => true,
                'is_superuser'      => false,
                'email_verified_at' => now(),
                'password'          => Hash::make('Matteo1*'), // dev only
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Utente normale
        |--------------------------------------------------------------------------
        */
        User::updateOrCreate(
            ['email' => 'mirco@mirco.com'],
            [
                'name'              => 'mirco',
                'is_staff'          => false,
                'is_superuser'      => false,
                'email_verified_at' => now(),
                'password'          => Hash::make('Mirco1**'), // dev only
            ]
        );
    }
}
