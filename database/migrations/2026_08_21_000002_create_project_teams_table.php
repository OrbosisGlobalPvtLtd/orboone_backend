<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('project_teams')) {
            Schema::create('project_teams', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('project_id')->index();
                $table->string('team_name', 100);
                $table->unsignedBigInteger('team_lead_employee_id')->nullable()->index();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(1)->index();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->unique(['project_id', 'team_name']);

                if (Schema::hasTable('projects')) {
                    try {
                        $table->foreign('project_id')
                            ->references('id')
                            ->on('projects')
                            ->onDelete('cascade');
                    } catch (\Exception $e) {}
                }

                if (Schema::hasTable('employees_new')) {
                    try {
                        $table->foreign('team_lead_employee_id')
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
        Schema::dropIfExists('project_teams');
    }
}
