<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_name' => $this->faker->unique()->words(3, true), // e.g. "Tasty Apple Chips"
            'category' => $this->faker->randomElement(['snack', 'drink', 'meal', 'dessert']),
            'supplier' => $this->faker->company,
            'quantity' => $this->faker->numberBetween(1, 500),
            'unit_of_measurement' => $this->faker->randomElement(['pieces', 'liters', 'kilograms', 'grams']),
            'purchase_cost' => $this->faker->randomFloat(2, 0.5, 100.00),
        ];
    }
}
