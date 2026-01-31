<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GramatSeeders extends Seeder
{
    public function run(): void{
        DB::table('Gramata')->insert([
            // Akademiskas gramatas
            [
             'ISBN' => '12345623',
             'nosaukums' => 'Algebra and Trigonometry 2e.',
             'autors' => 'Jay Abramson',
             'Nodala_ID' => 1,
             'vaku_attels' => 'uploids/cover/12345623.jpg',
             'created_at' => now(),
             'updated_at' => now()
            ],
            [
             'ISBN' => '12345723',
             'nosaukums' => 'Principles of Economics 3e',
             'autors' => 'Jay Abramson',
             'Nodala_ID' => 1,
             'vaku_attels' => 'uploids/cover/12345723.jpg',
             'created_at' => now(),
             'updated_at' => now()
            ],
            [
             'ISBN' => '12345823',
             'nosaukums' => 'Introduction to Python Programming',
             'autors' => 'Jay Abramson',
             'Nodala_ID'=> 1,
             'vaku_attels' => 'uploids/cover/12345823.jpg',
             'created_at' => now(),
             'updated_at' => now()
            ],
            [
             'ISBN' => '12345923',
             'nosaukums' => 'Principles of Data Science',
             'autors' => 'Jay Abramson',
             'Nodala_ID' => 1,
             'vaku_attels' => 'uploids/cover/12345923.jpg',
             'created_at' => now(),
             'updated_at' => now()
            ],
            // Atputas gramatas
            [
             'ISBN' => '19364225',
             'nosaukums' => 'Valmieras puikas',
             'autors' => 'Pāvils Rozītis',
             'Nodala_ID' => 2,
             'vaku_attels' => 'uplods/cover/19364225.jpg',
             'created_at' => now(),
             'updated_at' => now()
            ],
            [
             'ISBN' => '19404325',
             'nosaukums' => 'Sūni ciema ziema',
             'autors' => 'Andrejs Upīts',
             'Nodala_ID' => 2,
             'vaku_attels' => 'uploids/cover/19404325.jpg',
             'created_at' => now(),
             'updated_at' => now()
            ],
            [
             'ISBN' => '19734525',
             'nosaukums' => 'Krāsainas pasakas',
             'autors' => 'Imants Ziedonis',
             'Nodala_ID' => 2,
             'vaku_attels' => 'uploids/cover/19734525.jpg',
             'created_at' => now(),
             'updated_at' => now()
            ],
            [
             'ISBN' => '19804425',
             'nosaukums' => 'Mērnieku laiki',
             'autors' => 'Reinis Kaudzīte un Matīss Kaudzīte',
             'Nodala_ID' => 2,
             'vaku_attels' => 'uploids/cover/19804425.jpg',
             'created_at' => now(),
             'updated_at' => now()
            ],
        ]);
    }
    
}