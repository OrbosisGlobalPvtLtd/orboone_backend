<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkReportTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('work_report_templates')) {
            Schema::create('work_report_templates', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('project_id')->nullable()->index();
                $table->string('name', 191);
                $table->string('code', 50)->unique();
                $table->string('employee_role_type', 50)->default('general')->index();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(1)->index();
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
        Schema::dropIfExists('work_report_templates');
    }
}
