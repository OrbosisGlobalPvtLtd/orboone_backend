<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('department_module_access', function (Blueprint $table) {
            $table->id();

            $table->foreignId('department_id')
                ->constrained('departments')
                ->cascadeOnDelete();

            $table->string('module_key', 100);
            $table->boolean('is_enabled')->default(true);

            $table->timestamps();

            $table->unique(['department_id', 'module_key']);
            $table->index('module_key');
            $table->index('is_enabled');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_module_access');
    }
};