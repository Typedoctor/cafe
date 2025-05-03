<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
 

        Schema::create('damaged_products', function (Blueprint $table) {
            $table->id();
            $table->string('product_name', 255)->unique();
            $table->integer('quantity');
            $table->text('reason');
            $table->string('supplier', 255);
            $table->timestamp('reported_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('damaged_products');
    }
};