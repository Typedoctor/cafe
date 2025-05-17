<?php

namespace Database\Factories;
use App\Models\Product;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;


class TransactionFactory extends Factory
{
    // Define the corresponding model
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $totalPrice = $this->faker->randomFloat(2, 10, 100);
        $moneyReceived = $totalPrice + $this->faker->randomFloat(2, 0, 20);
        $change = $moneyReceived - $totalPrice;

        $product = Product::inRandomOrder()->first(); // Get a random product
        return [
            'user_id' => User::inRandomOrder()->first()?->id,
            'product_name' => $product?->product_name ?? 'Unknown Product',
            'customer_name' => $this->faker->name(),
            'special_instructions' => $this->faker->optional()->sentence(),
            'order_type' => $this->faker->randomElement(['Dine-in', 'Takeout']),
            'status' => 'completed',
            'quantity' => $this->faker->numberBetween(1, 5),
            'total_price' => $totalPrice,
            'money_received' => $moneyReceived,
            'change' => $change,
            'created_at' => $createdAt = $this->faker->dateTimeBetween('2020-01-01 00:00:00', '2025-12-31 23:59:59'),
            'updated_at' => $createdAt,
        ];
    }
}
