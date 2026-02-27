<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // (Opzionale) utente di prova
        // User::factory()->create([
        //     'name'  => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Esegui i nostri seeder nell'ordine giusto
        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
            ProductVariantSeeder::class,
            SettingsSeeder::class,
            UserSeeder::class,
        ]);
    }
}
