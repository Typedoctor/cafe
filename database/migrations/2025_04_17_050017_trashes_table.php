<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('trashes', function (Blueprint $table) {
            $table->id();
            $table->string('product_name')->unique(); 
            $table->enum('category', ['snack', 'drink', 'meal', 'dessert']);
            $table->integer('quantity');
            $table->string('reason');
            $table->decimal('total_loss', 10, 2);
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('trashes');
    }
};
