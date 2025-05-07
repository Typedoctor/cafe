<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Drop the existing transactions table
        Schema::dropIfExists('transactions');

        // Create the new transactions table with transaction_id and money_received
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique(); // Unique transaction ID column
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('product_name', 255);
            $table->string('customer_name', 255);
            $table->text('special_instructions')->nullable();
            $table->string('order_type', 255);
            $table->string('status')->default('completed');
            $table->integer('quantity');
            $table->decimal('total_price', 10, 2);
            $table->decimal('money_received', 10, 2)->nullable(); // Money received, nullable for flexibility
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
