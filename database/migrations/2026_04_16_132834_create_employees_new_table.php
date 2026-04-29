<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesNewTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('employees_new')) {
            Schema::create('employees_new', function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->unsignedBigInteger('user_id');
                $table->string('employee_code', 50)->unique();

                $table->unsignedBigInteger('system_role_id')->nullable();
                $table->unsignedBigInteger('department_id')->nullable();
                $table->unsignedBigInteger('designation_id')->nullable();
                $table->unsignedBigInteger('reporting_manager_employee_id')->nullable();

                $table->enum('employment_type', ['full_time', 'intern', 'freelancer', 'contract']);
                $table->enum('work_mode', ['wfo', 'wfh']);

                $table->date('joining_date')->nullable();
                $table->date('relieving_date')->nullable();

                $table->enum('employment_status', ['active', 'resigned', 'terminated'])->default('active');

                $table->integer('probation_months')->default(3);
                $table->date('probation_start_date')->nullable();
                $table->date('probation_end_date')->nullable();

                $table->enum('probation_status', ['pending', 'ongoing', 'completed', 'confirmed'])->default('pending');

                $table->date('internship_start_date')->nullable();
                $table->date('internship_end_date')->nullable();
                $table->date('internship_extended_to')->nullable();

                $table->boolean('is_paid_intern')->nullable();
                $table->decimal('actual_salary', 12, 2)->nullable();

                $table->boolean('is_active')->default(1);

                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();

                $table->timestamps();
            });
        }

        // Foreign keys alag se conditionally add kar rahe hain
        // taaki missing tables ki wajah se migration fail na ho

        if (Schema::hasTable('employees_new')) {
            Schema::table('employees_new', function (Blueprint $table) {
                // users
                if (Schema::hasTable('users')) {
                    try {
                        $table->foreign('user_id')
                            ->references('id')
                            ->on('users')
                            ->onDelete('cascade');
                    } catch (\Exception $e) {}

                    try {
                        $table->foreign('created_by')
                            ->references('id')
                            ->on('users')
                            ->onDelete('set null');
                    } catch (\Exception $e) {}

                    try {
                        $table->foreign('updated_by')
                            ->references('id')
                            ->on('users')
                            ->onDelete('set null');
                    } catch (\Exception $e) {}
                }

                // roles
                if (Schema::hasTable('roles')) {
                    try {
                        $table->foreign('system_role_id')
                            ->references('id')
                            ->on('roles')
                            ->onDelete('set null');
                    } catch (\Exception $e) {}
                }

                // departments
                if (Schema::hasTable('departments')) {
                    try {
                        $table->foreign('department_id')
                            ->references('id')
                            ->on('departments')
                            ->onDelete('set null');
                    } catch (\Exception $e) {}
                }

                // designations
                if (Schema::hasTable('designations')) {
                    try {
                        $table->foreign('designation_id')
                            ->references('id')
                            ->on('designations')
                            ->onDelete('set null');
                    } catch (\Exception $e) {}
                }

                // self reference
                try {
                    $table->foreign('reporting_manager_employee_id')
                        ->references('id')
                        ->on('employees_new')
                        ->onDelete('set null');
                } catch (\Exception $e) {}
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('employees_new')) {
            Schema::table('employees_new', function (Blueprint $table) {
                try { $table->dropForeign(['user_id']); } catch (\Exception $e) {}
                try { $table->dropForeign(['system_role_id']); } catch (\Exception $e) {}
                try { $table->dropForeign(['department_id']); } catch (\Exception $e) {}
                try { $table->dropForeign(['designation_id']); } catch (\Exception $e) {}
                try { $table->dropForeign(['reporting_manager_employee_id']); } catch (\Exception $e) {}
                try { $table->dropForeign(['created_by']); } catch (\Exception $e) {}
                try { $table->dropForeign(['updated_by']); } catch (\Exception $e) {}
            });
        }

        Schema::dropIfExists('employees_new');
    }
}