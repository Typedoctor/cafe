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
            $table->string('product_name', 255);
            $table->integer('quantity');
            $table->decimal('price_per_item', 8, 2); // Price per item, e.g., 10.99
            $table->text('reason');
            $table->string('supplier', 255);
            $table->timestamp('reported_at')->useCurrent();
            $table->enum('status', ['Pending', 'Successfully Returned', 'Marked as Loss'])->default('Pending');
            $table->timestamp('return_date')->nullable();
            $table->decimal('total_loss', 10, 2)->default(0.00);
            $table->decimal('total_saved', 10, 2)->default(0.00);
            $table->text('return_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('damaged_products');
    }
};