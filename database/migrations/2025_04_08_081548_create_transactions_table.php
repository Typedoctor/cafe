<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(); // Links to 'users' table
            $table->foreignId('product_id')->constrained(); // Links to 'products' table
            $table->integer('quantity'); // Number of products bought
            $table->decimal('total_price', 10, 2);
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('transactions');
    }
};
