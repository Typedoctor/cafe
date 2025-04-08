<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('products', function (Blueprint $table) {
            $table->id(); // Unique primary ID
            $table->string('product_name')->unique(); // Ensures product names are unique
            $table->string('category'); // Product category (e.g., beverages, snacks, etc.)
            $table->decimal('price', 10, 2); // Stores price with two decimal places
            $table->integer('quantity'); // Stores stock count
            $table->timestamps(); // Tracks created_at and updated_at timestamps
        });
    }

    public function down() {
        Schema::dropIfExists('products');
    }
};
