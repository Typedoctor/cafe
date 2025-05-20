<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $totalPrice = $this->faker->randomFloat(2, 100, 1000);
        $moneyReceived = $totalPrice + $this->faker->randomFloat(2, 0, 200);
        $change = $moneyReceived - $totalPrice;

        return [
            'user_id' => User::inRandomOrder()->first()?->id, // nullable foreign key
            'customer_name' => $this->faker->name(),
            'special_instructions' => $this->faker->optional()->sentence(),
            'status' => $this->faker->randomElement(['completed', 'pending', 'cancelled']),
            'order_type' => $this->faker->randomElement(['delivery', 'pickup']),
            'total_price' => $totalPrice,
            'money_received' => $moneyReceived,
            'change' => $change,
            'created_at' => $createdAt = $this->faker->dateTimeBetween('-2 years', 'now'),
            'updated_at' => $createdAt,
        ];
    }
}
