<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentTemplatesTable extends Migration
{
    public function up()
    {
        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('document_type');
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->longText('html_template');
            $table->longText('editor_content')->nullable();
            $table->string('default_subject')->nullable();
            $table->longText('default_email_body')->nullable();
            $table->string('header_image')->nullable();
            $table->string('footer_image')->nullable();
            $table->string('background_image')->nullable();
            $table->string('paper_size')->default('A4');
            $table->string('orientation')->default('portrait');
            $table->string('margin_top')->nullable();
            $table->string('margin_right')->nullable();
            $table->string('margin_bottom')->nullable();
            $table->string('margin_left')->nullable();
            $table->boolean('is_certificate')->default(false);
            $table->boolean('requires_review')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->unsignedBigInteger('updated_by_user_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('document_templates');
    }
}
