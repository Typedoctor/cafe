<?php

namespace Database\Factories;

use App\Models\Spoilage;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpoilageFactory extends Factory
{
    protected $model = Spoilage::class;

    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 10);
        $pricePerItem = $this->faker->randomFloat(2, 5, 50);
        $totalLoss = $quantity * $pricePerItem;

        return [
            'product_name' => $this->faker->word(),
            'category' => $this->faker->randomElement(['snack', 'drink', 'meal', 'dessert']),
            'quantity' => $quantity,
            'reason' => $this->faker->randomElement(['expired', 'damaged', 'spilled']),
            'total_loss' => $totalLoss,
        ];
    }
}
