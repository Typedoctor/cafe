<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\User;
use App\Models\Transaction;
use Faker\Factory as Faker;

class TransactionSeeder extends Seeder
{
    /**
     * Seed the transactions table with realistic data.
     *
     * @return void
     */
    public function run()
    {
        // Initialize Faker with Philippine locale for realistic customer names
        $faker = Faker::create('en_PH');

        // Fetch products with necessary fields (id, product_name, category, unit_of_measurement)
        $products = Product::select('id', 'product_name', 'category', 'unit_of_measurement')->get();
        if ($products->isEmpty()) {
            echo "No products found. Cannot seed transactions.\n";
            return;
        }

        // Fetch user IDs, allowing for null if no users exist
        $userIds = User::pluck('id')->toArray();

        // Define order types and special instructions
        $orderTypes = ['dine-in', 'take-out'];
        $instructions = [
            'Extra shot', 'Decaf', 'Soy milk', 'Almond milk', 'No sugar', 'Extra sugar',
            'Whipped cream', 'No ice', 'Less ice', 'Hot', 'Iced', 'No onions', 'Extra cheese',
            'Gluten-free', 'Vegan', 'Well done', 'Medium rare', 'Spicy', 'Mild'
        ];

        // Generate 100 transactions
        for ($i = 0; $i < 100; $i++) {
            // Select a random product
            $product = $products->random();
            $category = $product->category;
            $unitOfMeasurement = $product->unit_of_measurement;

            // Set price ranges based on category (in PHP, whole numbers, reflective of 2025 prices)
            switch ($category) {
                case 'drink':
                    $minPrice = 60;  // Slightly adjusted for 2025 inflation
                    $maxPrice = 180;
                    break;
                case 'snack':
                    $minPrice = 60;
                    $maxPrice = 120;
                    break;
                case 'meal':
                    $minPrice = 120;
                    $maxPrice = 250;
                    break;
                case 'dessert':
                    $minPrice = 60;
                    $maxPrice = 180;
                    break;
                default:
                    $minPrice = 60;
                    $maxPrice = 250;
            }

            // Adjust quantity range based on unit of measurement
            $maxQuantity = match ($unitOfMeasurement) {
                'liters', 'kilograms' => $faker->numberBetween(1, 3), // Smaller quantities for bulk units
                'grams' => $faker->numberBetween(1, 10),             // More for smaller units
                'pieces' => $faker->numberBetween(1, 5),             // Standard for pieces
                default => $faker->numberBetween(1, 5),
            };

            // Generate transaction details
            $pricePerUnit = $faker->numberBetween($minPrice, $maxPrice);
            $quantity = $faker->numberBetween(1, $maxQuantity);
            $totalPrice = $quantity * $pricePerUnit;
            $customerName = $faker->name;
            $specialInstructions = $faker->boolean(10) ? $faker->randomElement($instructions) : null;
            $orderType = $faker->randomElement($orderTypes);
            $userId = !empty($userIds) ? $faker->randomElement($userIds) : null;
            $createdAt = $faker->dateTimeBetween('-6 months', 'now');

            // Create the transaction with unit of measurement
            Transaction::create([
                'user_id' => $userId,
                'product_id' => $product->id,
                'product_name' => $product->product_name,
                'unit_of_measurement' => $unitOfMeasurement, // Add unit of measurement
                'customer_name' => $customerName,
                'special_instructions' => $specialInstructions,
                'order_type' => $orderType,
                'status' => 'completed',
                'quantity' => $quantity,
                'total_price' => $totalPrice,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }

        echo "Successfully seeded 100 transactions.\n";
    }
}