<?php

namespace Database\Seeders;
use App\Models\Trash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
class ThrownItems extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    { // Daily (today)
        Trash::create([
            'product_name' => 'Coffee',
            'category' => 'drink',
            'quantity' => 10,
            'reason' => 'Expired',
            'total_loss' => 1000.00,
            'created_at' => Carbon::today(),
        ]);
        Trash::create([
            'product_name' => 'Burger',
            'category' => 'snack',
            'quantity' => 5,
            'reason' => 'Spoiled',
            'total_loss' => 500.00,
            'created_at' => Carbon::today->startOfYear(),
        ]);

        // Monthly (this month, but not today)
        Trash::create([
            'product_name' => 'Pasta',
            'category' => 'meal',
            'quantity' => 2,
            'reason' => 'Mold',
            'total_loss' => 1956.00,
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
