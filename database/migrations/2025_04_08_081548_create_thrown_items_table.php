<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('thrown_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_name')->constrained();
            $table->enum('category', ['snack', 'drink', 'meal', 'dessert']);
            $table->integer('quantity');
            $table->string('reason');
            $table->decimal('total_loss', 10, 2);
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('thrown_items');
    }
};
