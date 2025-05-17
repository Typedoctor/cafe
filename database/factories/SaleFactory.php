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
        // Generating random data for sale records
        $quantity = $this->faker->numberBetween(1, 10);
        $unitPrice = $this->faker->randomFloat(2, 5, 50);  // Random price between 5 and 50
        $totalPrice = $quantity * $unitPrice;

        return [
            'order_id' => $this->faker->uuid,  // Random UUID for order_id
            'product_id' => Product::inRandomOrder()->first()->id,  // Random product
            'product_name' => $this->faker->word(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
            'user_id' => User::inRandomOrder()->first()?->id,  // Random user if exists, can be null
            'customer_name' => $this->faker->name(),
            'order_type' => $this->faker->randomElement(['online', 'in-store', 'pickup']),
            'status' => $this->faker->randomElement(['completed', 'pending', 'cancelled']),
            'change' => $this->faker->randomFloat(2, 0, 10),  // Random change
            'money_received' => $totalPrice + $this->faker->randomFloat(2, 0, 10),  // Money received (could be more than total price)
            'special_instructions' => $this->faker->sentence(),
        ];
    }
}
