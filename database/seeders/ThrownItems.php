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
            'product_name' => 'potasts',
            'category' => 'snack',
            'quantity' => '13',
            'reason' => 'expired',
            'total_loss' => '999',

        ]);
    }
}
