<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\DamagedProduct;
use App\Models\Product;
class DamagedProductFactory extends Factory
{
    protected $model = DamagedProduct::class;

    public function definition()
    {
        $statusOptions = ['Successfully Returned and Replaced', 'Marked as Loss'];
        $status = $this->faker->randomElement($statusOptions);
        
        $quantity = $this->faker->numberBetween(1, 20);
        $pricePerItem = $this->faker->randomFloat(2, 1, 500);
        
        $totalLoss = $status === 'Marked as Loss' ? $quantity * $pricePerItem : $this->faker->randomFloat(2, 0, $quantity * $pricePerItem);
        $totalSaved = $status === 'Successfully Returned and Replaced' ? ($quantity * $pricePerItem) - $totalLoss : 0;
        $product = Product::inRandomOrder()->first(); 
        return [
            'product_name' => $product?->product_name ?? 'Unknown Product',
            'quantity' => $quantity,
            'price_per_item' => $pricePerItem,
            'reason' => $this->faker->sentence(),
            'supplier' => $this->faker->company(),
            'reported_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'status' => $status,
            'return_date' => $status === 'Successfully Returned and Replaced' ? $this->faker->dateTimeBetween('-6 months', 'now') : null,
            'total_loss' => $totalLoss,
            'total_saved' => $totalSaved,
            'return_notes' => $status === 'Successfully Returned and Replaced' ? $this->faker->paragraph() : null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
