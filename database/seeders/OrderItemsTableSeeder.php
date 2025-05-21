<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OrderItem;

class OrderItemsTableSeeder extends Seeder
{
    public function run(): void
    {
        OrderItem::factory()->count(50)->create();
    }
}
