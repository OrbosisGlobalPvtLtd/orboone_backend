<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectAssignmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('project_assignments')) {
            Schema::create('project_assignments', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('project_id')->index();
                $table->unsignedBigInteger('project_team_id')->nullable()->index();
                $table->unsignedBigInteger('employee_id')->index();
                $table->enum('project_role', ['delivery_head', 'team_lead', 'team_member'])->default('team_member')->index();
                $table->dateTime('assigned_at');
                $table->dateTime('relieved_at')->nullable();
                $table->boolean('is_active')->default(1)->index();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                if (Schema::hasTable('projects')) {
                    try {
                        $table->foreign('project_id')
                            ->references('id')
                            ->on('projects')
                            ->onDelete('cascade');
                    } catch (\Exception $e) {}
                }

                if (Schema::hasTable('project_teams')) {
                    try {
                        $table->foreign('project_team_id')
                            ->references('id')
                            ->on('project_teams')
                            ->onDelete('set null');
                    } catch (\Exception $e) {}
                }

                if (Schema::hasTable('employees_new')) {
                    try {
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
        Schema::dropIfExists('project_assignments');
    }
}
