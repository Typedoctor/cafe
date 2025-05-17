<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id(); // Unique primary ID
            $table->string('product_name')->unique(); // String, unique, for letters and spaces
            $table->enum('category', ['snack', 'drink', 'meal', 'dessert']);
            $table->string('supplier'); // String for supplier name
            $table->integer('quantity'); // Integer for positive quantities
            $table->enum('unit_of_measurement', ['pieces', 'liters', 'kilograms', 'grams'])->default('pieces'); // Unit of measurement
            $table->decimal('purchase_cost', 10, 2)->default(0); // Add purchase cost
            $table->timestamps(); // Created_at and updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
};