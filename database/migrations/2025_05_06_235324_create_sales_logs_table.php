<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->index()->nullable(); // Should be nullable
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('product_name', 255);
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('customer_name', 255);
            $table->string('order_type', 255);
            $table->string('status')->default('completed');
            $table->decimal('change', 10, 2)->nullable();
            $table->decimal('money_received', 10, 2)->nullable();
            $table->text('special_instructions')->nullable();
            $table->timestamps();
        
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sales');
    }
};