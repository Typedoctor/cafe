<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_type'); // Type of report (e.g., daily sales, inventory, etc.)
            $table->text('details')->nullable(); // Additional information
            $table->foreignId('user_id')->nullable()->constrained(); // Links to users table
            $table->timestamps(); // Created at & updated at timestamps
        });
    }

    public function down() {
        Schema::dropIfExists('reports');
    }
};
