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
        if (!Schema::hasTable('reporting_assignments')) {
            Schema::create('reporting_assignments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('supervisor_employee_id');
                $table->unsignedBigInteger('employee_id');
                $table->unsignedBigInteger('project_id')->nullable();
                $table->unsignedBigInteger('team_id')->nullable();
                $table->timestamp('start_date')->useCurrent();
                $table->timestamp('end_date')->nullable();
                $table->tinyInteger('status')->default(1)->comment('1: Active, 0: Relieved/Transferred');
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->foreign('supervisor_employee_id')->references('id')->on('employees_new')->onDelete('cascade');
                $table->foreign('employee_id')->references('id')->on('employees_new')->onDelete('cascade');
                $table->foreign('project_id')->references('id')->on('projects')->onDelete('set null');
                $table->foreign('team_id')->references('id')->on('project_teams')->onDelete('set null');

                $table->index(['supervisor_employee_id', 'employee_id', 'is_active'], 'reporting_active_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reporting_assignments');
    }
};
