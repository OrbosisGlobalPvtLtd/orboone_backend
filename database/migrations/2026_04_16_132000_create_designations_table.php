<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDesignationsTable extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('designations')) {
            Schema::create('designations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('department_id')->nullable()->index();
                $table->string('name', 150);
                $table->string('code', 50)->nullable()->unique();
                $table->text('description')->nullable();
                $table->tinyInteger('is_active')->default(1);
                $table->timestamps();

                if (Schema::hasTable('departments')) {
                    $table->foreign('department_id', 'fk_designations_department')
                          ->references('id')
                          ->on('departments')
                          ->onDelete('set null');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('designations');
    }
}
