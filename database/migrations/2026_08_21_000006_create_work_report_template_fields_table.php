<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkReportTemplateFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('work_report_template_fields')) {
            Schema::create('work_report_template_fields', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('template_id')->index();
                $table->string('field_key', 50);
                $table->string('field_label', 100);
                $table->string('field_type', 30)->default('text');
                $table->string('placeholder', 191)->nullable();
                $table->json('options_json')->nullable();
                $table->json('validation_json')->nullable();
                $table->boolean('is_required')->default(0);
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(1)->index();
                $table->timestamps();

                if (Schema::hasTable('work_report_templates')) {
                    try {
                        $table->foreign('template_id')
                            ->references('id')
                            ->on('work_report_templates')
                            ->onDelete('cascade');
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
        Schema::dropIfExists('work_report_template_fields');
    }
}
