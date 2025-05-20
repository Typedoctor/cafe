<?php

namespace Database\Factories;
use App\Models\Product;
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

    $product = Product::inRandomOrder()->first(); // Get a random product

    return [
        'product_name' => $product?->product_name ?? 'Unknown Product', // Use product name or fallback

        'category' => $this->faker->randomElement(['snack', 'drink', 'meal', 'dessert']),
        'quantity' => $quantity,
        'reason' => $this->faker->randomElement(['Expired', 'Damaged', 'Spoiled']),
        'total_loss' => $totalLoss,
        'created_at' => $createdAt = $this->faker->dateTimeBetween('2020-01-01 00:00:00', '2025-12-31 23:59:59'),
        'updated_at' => $createdAt,
    ];
}

}
