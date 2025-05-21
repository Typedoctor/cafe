<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ShelfItemFactory extends Factory
{
    protected $model = \App\Models\ShelfItem::class;

    public function definition()
    {
        return [
            'product_id' => $this->faker->numberBetween(1, 100),
            'quantity_added' => $this->faker->numberBetween(1, 50),
            'price' => $this->faker->randomFloat(2, 1, 1000),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
