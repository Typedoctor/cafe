<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Spoilage;

class SpoilagesTableSeeder extends Seeder
{
    public function run(): void
    {
        Spoilage::factory()->count(150)->create(); // Creates 50 fake spoilage records
    }
}
