<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ShelfItem;

class ShelfItemSeeder extends Seeder
{
    public function run()
    {
      ShelfItem::factory()->count(50)->create();
    }
}