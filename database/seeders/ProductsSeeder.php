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
        // Drinks
        Product::create([
            'product_name' => 'Coffee Latte',
            'category' => 'Drink',
            'price' => 120,
            'quantity' => 50,
        ]);

        Product::create([
            'product_name' => 'Green Tea',
            'category' => 'Drink',
            'price' => 95,
            'quantity' => 30,
        ]);

        Product::create([
            'product_name' => 'Iced Americano',
            'category' => 'Drink',
            'price' => 110,
            'quantity' => 40,
        ]);

        Product::create([
            'product_name' => 'Mango Smoothie',
            'category' => 'Drink',
            'price' => 150,
            'quantity' => 25,
        ]);

        // Snacks
        Product::create([
            'product_name' => 'Blueberry Muffin',
            'category' => 'Snack',
            'price' => 60,
            'quantity' => 20,
        ]);

        Product::create([
            'product_name' => 'Chocolate Chip Cookie',
            'category' => 'Snack',
            'price' => 45,
            'quantity' => 30,
        ]);

        Product::create([
            'product_name' => 'Mixed Nuts Pack',
            'category' => 'Snack',
            'price' => 80,
            'quantity' => 15,
        ]);

        // Meals
        Product::create([
            'product_name' => 'Chicken Caesar Salad',
            'category' => 'Meal',
            'price' => 200,
            'quantity' => 10,
        ]);

        Product::create([
            'product_name' => 'Grilled Cheese Sandwich',
            'category' => 'Meal',
            'price' => 150,
            'quantity' => 12,
        ]);

        Product::create([
            'product_name' => 'Vegetarian Wrap',
            'category' => 'Meal',
            'price' => 180,
            'quantity' => 8,
        ]);

        // Desserts
        Product::create([
            'product_name' => 'Chocolate Lava Cake',
            'category' => 'Dessert',
            'price' => 120,
            'quantity' => 15,
        ]);

        Product::create([
            'product_name' => 'Cheesecake Slice',
            'category' => 'Dessert',
            'price' => 100,
            'quantity' => 10,
        ]);

        Product::create([
            'product_name' => 'Fruit Tart',
            'category' => 'Dessert',
            'price' => 90,
            'quantity' => 20,
        ]);
    }
}