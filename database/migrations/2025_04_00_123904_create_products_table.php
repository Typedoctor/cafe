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
            $table->string('supplier'); // String for supplier name (letters and spaces)
            $table->integer('quantity'); // Integer for positive quantities
            $table->timestamps(); // Created_at and updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
};