<?php

namespace Database\Seeders;

use App\Models\Trash;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ThrownItems extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Product entries with quantity 0 and calculated price
       

        Product::create([
            'product_name' => 'Burger',
            'category' => 'snack',
            'quantity' => 0,
            'price' => 100.00, // total_loss (500.00) / quantity (5) from Trash
        ]);

        Product::create([
            'product_name' => 'King',
            'category' => 'meal',
            'quantity' => 0,
            'price' => 250.00, // total_loss (500.00) / quantity (2) from Trash
        ]);

        Product::create([
            'product_name' => 'Cake',
            'category' => 'dessert',
            'quantity' => 0,
            'price' => 150.00, // total_loss (1200.00) / quantity (8) from Trash
        ]);

        // Daily (today)
        Trash::create([
            'product_name' => 'Coffee',
            'category' => 'drink',
            'quantity' => 10,
            'reason' => 'Expired',
            'total_loss' => 1000.00,
            'created_at' => Carbon::today(),
        ]);

        // Note: The second Coffee entry was changed to Burger to match the Product entry
        Trash::create([
            'product_name' => 'Burger',
            'category' => 'snack',
            'quantity' => 5,
            'reason' => 'Spoiled',
            'total_loss' => 500.00,
            'created_at' => Carbon::today()->startOfYear(),
        ]);

        // Monthly (this month, but not today)
        Trash::create([
            'product_name' => 'King',
            'category' => 'meal',
            'quantity' => 2,
            'reason' => 'Mold',
            'total_loss' => 500.00,
            'created_at' => Carbon::now()->startOfMonth(),
        ]);

        // Yearly (this year, but not this month)
        Trash::create([
            'product_name' => 'Cake',
            'category' => 'dessert',
            'quantity' => 8,
            'reason' => 'Expired',
            'total_loss' => 1200.00,
            'created_at' => Carbon::now()->startOfYear(),
        ]);
    }
}