<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        //populates the database with dummy data
        $this->call([
            //TransactionsTableSeeder::class, //for transaction table
            //SpoilagesTableSeeder::class, //for spoilage table
            SalesTableSeeder::class, //sales tsble
        ]);
    }
}
