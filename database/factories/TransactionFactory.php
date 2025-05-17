<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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

        return [
            'transaction_id' => Str::uuid()->toString(), // Generates a unique transaction ID
            'user_id' => User::inRandomOrder()->first()->id, // Randomly select an existing user
            'product_name' => $this->faker->word(), // Generate a random product name
            'customer_name' => $this->faker->name(), // Generate a random customer name
            'special_instructions' => $this->faker->optional()->sentence(), // Random instructions
            'order_type' => $this->faker->randomElement(['pickup', 'delivery']), // Random order type
            'status' => 'completed', // Status is fixed as "completed"
            'quantity' => $this->faker->numberBetween(1, 5), // Random quantity between 1 and 5
            'total_price' => $totalPrice, // Total price is random
            'money_received' => $moneyReceived, // Money received is based on total price
            'change' => $change, // Change is calculated based on money received
            'created_at' => now(), // Current timestamp
            'updated_at' => now(), // Current timestamp
        ];
    }
}
