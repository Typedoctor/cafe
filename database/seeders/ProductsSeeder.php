<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product; // Ensure the Product model is imported

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run()
    {
        Product::create([
            'product_name' => 'Coffee Latte',
            'category' => 'Drink',
            'price' => 120,
            'quantity' => 50,
            'sales' => 10, // Default sales count
        ]);

        Product::create([
            'product_name' => 'Green Tea',
            'category' => 'Drink',
            'price' => 95,
            'quantity' => 30,
            'sales' => 5,
        ]);

        Product::create([
            'product_name' => 'Blueberry Muffin',
            'category' => 'Snack',
            'price' => 60,
            'quantity' => 20,
            'sales' => 8,
        ]);
    }
}


