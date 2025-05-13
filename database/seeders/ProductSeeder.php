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
            // Determine unit of measurement based on category and product type
            $unitOfMeasurement = match ($product['category']) {
                'drink' => in_array($product['name'], ['Espresso', 'Americano', 'Latte', 'Cappuccino', 'Mocha', 'Flat White', 'Macchiato', 'Hot Chocolate']) ? 'grams' : 'liters',
                'snack', 'dessert' => 'pieces',
                'meal' => in_array($product['name'], ['Tomato Soup', 'Chicken Noodle Soup']) ? 'liters' : 'grams',
                default => 'pieces',
            };

            // Adjust quantity range based on unit of measurement
            $minQuantity = match ($unitOfMeasurement) {
                'liters' => 1,
                'grams' => 50,
                'pieces' => 10,
                default => 10,
            };
            $maxQuantity = match ($unitOfMeasurement) {
                'liters' => 5,
                'grams' => 200,
                'pieces' => 100,
                default => 100,
            };
            $quantity = rand($minQuantity, $maxQuantity);

            Product::create([
                'product_name' => $product['name'],
                'category' => $product['category'],
                'supplier' => Arr::random($suppliers),
                'quantity' => $quantity,
                'unit_of_measurement' => $unitOfMeasurement, // Add unit of measurement
            ]);
        }

        echo "Successfully seeded " . count($products) . " products.\n";
    }
}