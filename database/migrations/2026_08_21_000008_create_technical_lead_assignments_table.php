<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTechnicalLeadAssignmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('technical_lead_assignments')) {
            Schema::create('technical_lead_assignments', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('technical_lead_employee_id')->index();
                $table->unsignedBigInteger('employee_id')->index();
                $table->dateTime('assigned_at');
                $table->dateTime('relieved_at')->nullable();
                $table->boolean('is_active')->default(1)->index();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                // Composite index for fast scoped lookups
                $table->index(['technical_lead_employee_id', 'employee_id', 'is_active'], 'tl_emp_active_idx');

                if (Schema::hasTable('employees_new')) {
                    try {
                        $table->foreign('technical_lead_employee_id')
                            ->references('id')
                            ->on('employees_new')
                            ->onDelete('cascade');

                        $table->foreign('employee_id')
                            ->references('id')
                            ->on('employees_new')
                            ->onDelete('cascade');
                    } catch (\Exception $e) {}
                }

                if (Schema::hasTable('users')) {
                    try {
                        $table->foreign('created_by')
                            ->references('id')
                            ->on('users')
                            ->onDelete('set null');

                        $table->foreign('updated_by')
                            ->references('id')
                            ->on('users')
                            ->onDelete('set null');
                    } catch (\Exception $e) {}
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('technical_lead_assignments');
    }
}
