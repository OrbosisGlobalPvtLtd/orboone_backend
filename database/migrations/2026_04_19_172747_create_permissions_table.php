<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('module', 100);
            $table->string('submodule', 100)->nullable();
            $table->string('action', 50);
            $table->string('key', 150)->unique();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('module');
            $table->index('submodule');
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};