<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('project_tasks')) {
            Schema::create('project_tasks', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('project_id')->index();
                $table->unsignedBigInteger('project_team_id')->nullable()->index();
                $table->unsignedBigInteger('assigned_employee_id')->nullable()->index();
                $table->string('title', 255);
                $table->text('description')->nullable();
                $table->string('task_type', 50)->default('feature');
                $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium')->index();
                $table->enum('status', ['todo', 'in_progress', 'blocked', 'completed', 'cancelled'])->default('todo')->index();
                $table->integer('progress_percentage')->default(0);
                $table->date('start_date')->nullable();
                $table->date('due_date')->nullable();
                $table->dateTime('completed_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
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
                        $table->foreign('assigned_employee_id')
                            ->references('id')
                            ->on('employees_new')
                            ->onDelete('set null');
                    } catch (\Exception $e) {}
                }

                if (Schema::hasTable('users')) {
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
        Schema::dropIfExists('project_tasks');
    }
}
