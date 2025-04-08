<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('password');
            $table->enum('privilege', ['manager', 'cashier'])->default('cashier'); // Determines dashboard access
            $table->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists('users');
    }
};
