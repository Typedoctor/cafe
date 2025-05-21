<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $product = Product::inRandomOrder()->first();
        $quantity = $this->faker->numberBetween(1, 5);
        $price = $product?->price ?? $this->faker->randomFloat(2, 10, 100); // fallback price

        return [
            'order_id' => Order::inRandomOrder()->first()?->id,
            'product_id' => $product?->id,
            'quantity' => $quantity,
            'price' => $price,
            'created_at' => $createdAt = $this->faker->dateTimeBetween('-2 years', 'now'),
            'updated_at' => $createdAt,
        ];
    }
}
