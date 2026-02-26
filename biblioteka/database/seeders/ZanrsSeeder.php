<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ZanrsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('Zanrs')->insert([
            // akademiskas gramatas (Nodala_ID = 1)
            [
                'Zanra_ID' => 1,
                'nosaukums' => 'Matemātika',
                'gramatu_skaits' => 0,
                'Nodala' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'Zanra_ID' => 2,
                'nosaukums' => 'Ekonomika',
                'gramatu_skaits' => 0,
                'Nodala' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'Zanra_ID' => 3,
                'nosaukums' => 'Programmēšana',
                'gramatu_skaits' => 0,
                'Nodala' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'Zanra_ID' => 4,
                'nosaukums' => 'Datu zinātne',
                'gramatu_skaits' => 0,
                'Nodala' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            
            // atputas garamata (Nodala_ID = 2)
            [
                'Zanra_ID' => 5,
                'nosaukums' => 'Romāns',
                'gramatu_skaits' => 0,
                'Nodala' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'Zanra_ID' => 6,
                'nosaukums' => 'Pasakas',
                'gramatu_skaits' => 0,
                'Nodala' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}