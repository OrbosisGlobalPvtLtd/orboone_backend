<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('designation_module_access')) {
            Schema::create('designation_module_access', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('designation_id')->index();
                $table->string('module_key', 64)->nullable()->index();
                $table->string('permission_key', 128)->nullable()->index();
                $table->unsignedBigInteger('permission_id')->nullable()->index();
                $table->boolean('is_enabled')->default(true);
                $table->boolean('is_allowed')->default(true);
                $table->timestamps();

                $table->unique(['designation_id', 'permission_key'], 'designation_permission_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('designation_module_access');
    }
};
