<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Illuminate\Support\Arr;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $suppliers = [
            'Fresh Bake Co.',
            'Beverage World',
            'Dairy Delights',
            'Meat Masters',
            'Veggie Ventures',
            'Sweet Treats Inc.',
            'Cafe Supplies Ltd.',
        ];

        $products = [
            // Drinks
            ['name' => 'Espresso', 'category' => 'drink'],
            ['name' => 'Americano', 'category' => 'drink'],
            ['name' => 'Latte', 'category' => 'drink'],
            ['name' => 'Cappuccino', 'category' => 'drink'],
            ['name' => 'Mocha', 'category' => 'drink'],
            ['name' => 'Flat White', 'category' => 'drink'],
            ['name' => 'Macchiato', 'category' => 'drink'],
            ['name' => 'Hot Chocolate', 'category' => 'drink'],
            ['name' => 'Black Tea', 'category' => 'drink'],
            ['name' => 'Green Tea', 'category' => 'drink'],
            ['name' => 'Herbal Tea', 'category' => 'drink'],
            ['name' => 'Iced Coffee', 'category' => 'drink'],
            ['name' => 'Iced Tea', 'category' => 'drink'],

            // Snacks
            ['name' => 'Plain Croissant', 'category' => 'snack'],
            ['name' => 'Chocolate Croissant', 'category' => 'snack'],
            ['name' => 'Almond Croissant', 'category' => 'snack'],
            ['name' => 'Plain Bagel', 'category' => 'snack'],
            ['name' => 'Everything Bagel', 'category' => 'snack'],
            ['name' => 'Blueberry Muffin', 'category' => 'snack'],
            ['name' => 'Chocolate Chip Muffin', 'category' => 'snack'],
            ['name' => 'Banana Nut Muffin', 'category' => 'snack'],
            ['name' => 'Plain Scone', 'category' => 'snack'],
            ['name' => 'Cranberry Scone', 'category' => 'snack'],
            ['name' => 'Cheese Danish', 'category' => 'snack'],
            ['name' => 'Apple Danish', 'category' => 'snack'],
            ['name' => 'Cinnamon Roll', 'category' => 'snack'],

            // Meals
            ['name' => 'Ham and Cheese Sandwich', 'category' => 'meal'],
            ['name' => 'Turkey Club Sandwich', 'category' => 'meal'],
            ['name' => 'Veggie Wrap', 'category' => 'meal'],
            ['name' => 'Chicken Caesar Salad', 'category' => 'meal'],
            ['name' => 'Greek Salad', 'category' => 'meal'],
            ['name' => 'Quiche Lorraine', 'category' => 'meal'],
            ['name' => 'Spinach Quiche', 'category' => 'meal'],
            ['name' => 'Tomato Soup', 'category' => 'meal'],
            ['name' => 'Chicken Noodle Soup', 'category' => 'meal'],
            ['name' => 'Breakfast Burrito', 'category' => 'meal'],
            ['name' => 'Avocado Toast', 'category' => 'meal'],
            ['name' => 'Pancakes', 'category' => 'meal'],

            // Desserts
            ['name' => 'Chocolate Cake', 'category' => 'dessert'],
            ['name' => 'Vanilla Cake', 'category' => 'dessert'],
            ['name' => 'Red Velvet Cake', 'category' => 'dessert'],
            ['name' => 'New York Cheesecake', 'category' => 'dessert'],
            ['name' => 'Strawberry Cheesecake', 'category' => 'dessert'],
            ['name' => 'Tiramisu', 'category' => 'dessert'],
            ['name' => 'Apple Pie', 'category' => 'dessert'],
            ['name' => 'Pecan Pie', 'category' => 'dessert'],
            ['name' => 'Fudge Brownie', 'category' => 'dessert'],
            ['name' => 'Chocolate Chip Cookie', 'category' => 'dessert'],
            ['name' => 'Oatmeal Raisin Cookie', 'category' => 'dessert'],
            ['name' => 'Vanilla Cupcake', 'category' => 'dessert'],
        ];

        foreach ($products as $product) {
            Product::create([
                'product_name' => $product['name'],
                'category' => $product['category'],
                'supplier' => Arr::random($suppliers),
                'quantity' => rand(10, 100),
            ]);
        }
    }
}