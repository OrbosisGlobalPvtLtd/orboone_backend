<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('employee_exit_clearances')) {
            Schema::create('employee_exit_clearances', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('exit_process_id')->index();
                $table->string('department_key', 50);
                $table->string('status', 30)->default('pending');
                $table->text('remarks')->nullable();
                $table->json('checklist')->nullable();
                $table->unsignedBigInteger('approved_by_user_id')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();

                $table->unique(['exit_process_id', 'department_key'], 'exit_dept_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_exit_clearances');
    }
};
