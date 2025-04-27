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
            'supplier' => 'lebron',
            'quantity' => 50,
        ]);

        Product::create([
            'product_name' => 'Green Tea',
            'category' => 'Drink',
            'supplier' => 'lebron',
            'quantity' => 30,
        ]);

        Product::create([
            'product_name' => 'Iced Americano',
            'category' => 'Drink',
            'supplier' => 'lebron',
            'quantity' => 40,
        ]);

        Product::create([
            'product_name' => 'Mango Smoothie',
            'category' => 'Drink',
            'supplier' => 'lebron',
            'quantity' => 25,
        ]);

        // Snacks
        Product::create([
            'product_name' => 'Blueberry Muffin',
            'category' => 'Snack',
            'supplier' => 'lebron',
            'quantity' => 20,
        ]);

        Product::create([
            'product_name' => 'Chocolate Chip Cookie',
            'category' => 'Snack',
            'supplier' => 'lebron',
            'quantity' => 30,
        ]);

        Product::create([
            'product_name' => 'Mixed Nuts Pack',
            'category' => 'Snack',
            'supplier' => 'lebron',
            'quantity' => 15,
        ]);

        // Meals
        Product::create([
            'product_name' => 'Chicken Caesar Salad',
            'category' => 'Meal',
            'supplier' => 'lebron',
            'quantity' => 10,
        ]);

        Product::create([
            'product_name' => 'Grilled Cheese Sandwich',
            'category' => 'Meal',
            'supplier' => 'lebron',
            'quantity' => 12,
        ]);

        Product::create([
            'product_name' => 'Vegetarian Wrap',
            'category' => 'Meal',
            'supplier' => 'lebron',
            'quantity' => 8,
        ]);

        // Desserts
        Product::create([
            'product_name' => 'Chocolate Lava Cake',
            'category' => 'Dessert',
           'supplier' => 'lebron',
            'quantity' => 15,
        ]);

        Product::create([
            'product_name' => 'Cheesecake Slice',
            'category' => 'Dessert',
            'supplier' => 'lebron',
            'quantity' => 10,
        ]);

        Product::create([
            'product_name' => 'Fruit Tart',
            'category' => 'Dessert',
            'supplier' => 'lebron',
            'quantity' => 20,
        ]);
    }
}