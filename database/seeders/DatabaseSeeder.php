<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Siempre ejecutar el seeder del admin primero
        $this->call([
            AdminUserSeeder::class,
            // Otros seeders aquí...
        ]);
    }
}