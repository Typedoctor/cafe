<?php

namespace Database\Seeders;

use App\Models\Trash;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ThrownItems extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Products from ProductSeeder
        $products = [
            ['name' => 'Espresso', 'category' => 'drink', 'price' => 3.50],
            ['name' => 'Americano', 'category' => 'drink', 'price' => 3.00],
            ['name' => 'Latte', 'category' => 'drink', 'price' => 4.00],
            ['name' => 'Hot Chocolate', 'category' => 'drink', 'price' => 3.50],
            ['name' => 'Iced Coffee', 'category' => 'drink', 'price' => 4.00],
            ['name' => 'Plain Croissant', 'category' => 'snack', 'price' => 2.50],
            ['name' => 'Chocolate Croissant', 'category' => 'snack', 'price' => 3.00],
            ['name' => 'Blueberry Muffin', 'category' => 'snack', 'price' => 2.50],
            ['name' => 'Cheese Danish', 'category' => 'snack', 'price' => 3.00],
            ['name' => 'Cinnamon Roll', 'category' => 'snack', 'price' => 3.50],
            ['name' => 'Ham and Cheese Sandwich', 'category' => 'meal', 'price' => 6.00],
            ['name' => 'Veggie Wrap', 'category' => 'meal', 'price' => 5.50],
            ['name' => 'Chicken Caesar Salad', 'category' => 'meal', 'price' => 7.00],
            ['name' => 'Quiche Lorraine', 'category' => 'meal', 'price' => 6.50],
            ['name' => 'Avocado Toast', 'category' => 'meal', 'price' => 5.00],
            ['name' => 'Chocolate Cake', 'category' => 'dessert', 'price' => 4.00],
            ['name' => 'Tiramisu', 'category' => 'dessert', 'price' => 4.50],
            ['name' => 'Apple Pie', 'category' => 'dessert', 'price' => 4.00],
            ['name' => 'Fudge Brownie', 'category' => 'dessert', 'price' => 3.00],
            ['name' => 'Chocolate Chip Cookie', 'category' => 'dessert', 'price' => 2.00],
        ];

        // Reasons for throwing items
        $reasons = ['Expired', 'Spoiled', 'Mold', 'Damaged', 'Overstock'];

        // Date range: January 1, 2025, to May 6, 2025
        $startDate = Carbon::create(2025, 1, 1);
        $endDate = Carbon::create(2025, 5, 6);
        $daysDiff = $startDate->diffInDays($endDate);

        // Shuffle products to randomize selection
        shuffle($products);

        // Use each product only once
        foreach ($products as $product) {
            // Random quantity (1 to 10)
            $quantity = rand(1, 10);
            
            // Calculate total loss
            $totalLoss = $quantity * $product['price'];
            
            // Random reason
            $reason = $reasons[array_rand($reasons)];
            
            // Random date between Jan 1 and May 6
            $randomDays = rand(0, $daysDiff);
            $randomDate = $startDate->copy()->addDays($randomDays);
            
            // Create Trash entry
            Trash::create([
                'product_name' => $product['name'],
                'category' => $product['category'],
                'quantity' => $quantity,
                'reason' => $reason,
                'total_loss' => $totalLoss,
                'created_at' => $randomDate,
            ]);
        }
    }
}