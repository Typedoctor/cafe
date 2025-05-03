<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ShelfItem;

class ShelfItemSeeder extends Seeder
{
    public function run()
    {
        $products = Product::all();
        foreach ($products as $product) {
            switch ($product->category) {
                case 'drink':
                    $minPrice = 50;
                    $maxPrice = 150;
                    break;
                case 'snack':
                    $minPrice = 50;
                    $maxPrice = 100;
                    break;
                case 'meal':
                    $minPrice = 100;
                    $maxPrice = 200;
                    break;
                case 'dessert':
                    $minPrice = 50;
                    $maxPrice = 150;
                    break;
                default:
                    $minPrice = 50;
                    $maxPrice = 200;
            }
            $date = fake()->dateTimeBetween('-1 year', 'now');
            ShelfItem::create([
                'product_id' => $product->id,
                'quantity_added' => fake()->numberBetween(1, 50),
                'price' => fake()->numberBetween($minPrice, $maxPrice),
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }
    }
}