<?php 
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NodalaSeeder extends Seeder
{
    public function run(): void 
    {
        DB::table('Nodala')->insert([
            [
             'tips' => 'akademiska',
             'created_at' => now(),
             'updated_at' => now()
            ],
            [
             'tips' => 'izglitojosa',
             'created_at' => now(),
             'updated_at' => now()
            ],
        ]);
    }
}