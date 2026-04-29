<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_module_access', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('module_key', 100);
            $table->boolean('is_enabled')->default(true);

            $table->timestamps();

            $table->unique(['user_id', 'module_key']);
            $table->index('module_key');
            $table->index('is_enabled');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_module_access');
    }
};