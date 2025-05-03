<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('event');  // created, updated, deleted, etc.
            $table->string('auditable_type');  // Model class name
            $table->unsignedBigInteger('auditable_id');  // Model ID
            $table->json('old_values')->nullable();  // Previous values before change
            $table->json('new_values')->nullable();  // New values after change
            $table->string('url')->nullable();  // URL where the action occurred
            $table->string('ip_address')->nullable();  // IP address of the user
            $table->string('user_agent')->nullable();  // Browser/client info
            $table->timestamps();

            $table->index(['auditable_type', 'auditable_id']);
        });
    }

    public function down() {
        Schema::dropIfExists('audit_logs');
    }
};