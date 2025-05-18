<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sale;

class SalesTableSeeder extends Seeder
{
    public function run(): void
    {

          //add this line didto ha Databaseseeder just below han other shis
          //SalesTableSeeder::class,

          
        // Create 100 sale records 
        Sale::factory()->count(50)->create();
    }
}
