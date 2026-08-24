<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('projects')) {
            Schema::create('projects', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('project_code', 50)->unique();
                $table->string('name', 191);
                $table->string('client_name', 191)->nullable();
                $table->unsignedBigInteger('delivery_head_employee_id')->nullable()->index();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->enum('status', ['planning', 'active', 'on_hold', 'completed', 'archived'])->default('active')->index();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                if (Schema::hasTable('employees_new')) {
                    try {
                        $table->foreign('delivery_head_employee_id')
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
        Schema::dropIfExists('projects');
    }
}
