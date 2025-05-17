<?php

namespace Database\Factories;

use App\Models\Sale;
use App\Models\Product;  // Add Product model
use App\Models\User;     // Add User model
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleFactory extends Factory
{
    protected $model = Sale::class;

public function definition(): array
{
    $quantity = $this->faker->numberBetween(1, 10);
    $unitPrice = $this->faker->randomFloat(2, 5, 50);
    $totalPrice = $quantity * $unitPrice;

    $product = Product::inRandomOrder()->first(); // Fetch random product

    return [
        'order_id' => $this->faker->numberBetween(1, 9999),
        'product_id' => $product?->id,
        'product_name' => $product?->product_name ?? 'Unknown Product', // Fallback if null
        'quantity' => $quantity,
        'unit_price' => $unitPrice,
        'total_price' => $totalPrice,
        'user_id' => User::inRandomOrder()->first()?->id,
        'customer_name' => $this->faker->name(), // Already realistic
        'order_type' => $this->faker->randomElement(['Dine-in', 'Takeout']),
        'status' => $this->faker->randomElement(['completed']),
        'change' => $this->faker->randomFloat(2, 0, 10),
        'money_received' => $totalPrice + $this->faker->randomFloat(2, 0, 10),
        'special_instructions' => $this->faker->sentence(),
        'created_at' => $createdAt = $this->faker->dateTimeBetween('2020-01-01 00:00:00', '2025-12-31 23:59:59'),
        'updated_at' => $createdAt,
    ];
}

}
