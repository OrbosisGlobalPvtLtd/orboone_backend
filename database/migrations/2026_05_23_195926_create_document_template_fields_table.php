<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentTemplateFieldsTable extends Migration
{
    public function up()
    {
        Schema::create('document_template_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('template_id');
            $table->string('field_key');
            $table->string('field_label');
            $table->string('field_type');
            $table->string('default_value')->nullable();
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('template_id')->references('id')->on('document_templates')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('document_template_fields');
    }
}
