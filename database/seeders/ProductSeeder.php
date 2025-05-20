<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Illuminate\Support\Arr;

class ProductSeeder extends Seeder
{
    /**
     * Seed the products table with diverse items and realistic units.
     *
     * @return void
     */
    public function run()
    {
        Product::factory()->count(50)->create(); //products
    }
}