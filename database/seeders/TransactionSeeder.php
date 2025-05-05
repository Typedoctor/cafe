<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\User;
use App\Models\Transaction;
use Faker\Factory as Faker;

class TransactionSeeder extends Seeder
{
    public function run()
    {
        // Initialize Faker with Philippine locale for realistic customer names
        $faker = Faker::create('en_PH');

        // Fetch products with necessary fields
        $products = Product::select('id', 'product_name', 'category')->get();
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
            $product = $products->random();
            $category = $product->category;

            // Set price ranges based on category (in PHP, whole numbers)
            switch ($category) {
                case 'drink':
                    $minPrice = 50;
                    $maxPrice = 150;
                    break;
                case 'snack':
                    $minPrice = 50;
                    $maxPrice = 100;
                    break;
                case 'meal':
                    $minPrice = 100;
                    $maxPrice = 200;
                    break;
                case 'dessert':
                    $minPrice = 50;
                    $maxPrice = 150;
                    break;
                default:
                    $minPrice = 50;
                    $maxPrice = 200;
            }

            // Generate transaction details
            $pricePerUnit = $faker->numberBetween($minPrice, $maxPrice);
            $quantity = $faker->numberBetween(1, 5);
            $totalPrice = $quantity * $pricePerUnit;
            $customerName = $faker->name;
            $specialInstructions = $faker->boolean(10) ? $faker->randomElement($instructions) : null;
            $orderType = $faker->randomElement($orderTypes);
            $userId = !empty($userIds) ? $faker->randomElement($userIds) : null;
            $createdAt = $faker->dateTimeBetween('-6 months', 'now');

            // Create the transaction
            Transaction::create([
                'user_id' => $userId,
                'product_id' => $product->id,
                'product_name' => $product->product_name,
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
    }
}