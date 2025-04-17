<?php

namespace Database\Seeders;
use App\Models\Trash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ThrownItems extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Trash::create([
            'product_name' => 'Test User',
            'category' => 'snack',
            'quantity' => '12',
            'reason' => 'baho',
            'total_loss' => '12',

        ]);
    }
}
