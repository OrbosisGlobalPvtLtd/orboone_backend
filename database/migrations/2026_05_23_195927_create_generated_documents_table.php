<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeneratedDocumentsTable extends Migration
{
    public function up()
    {
        Schema::create('generated_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->string('document_type');
            $table->string('document_number')->unique();
            $table->string('document_title');
            $table->json('field_values')->nullable();
            $table->longText('generated_html')->nullable();
            $table->string('pdf_path')->nullable();
            $table->enum('status', ['draft', 'generated', 'reviewed', 'sent', 'cancelled'])->default('draft');
            $table->text('review_note')->nullable();
            $table->unsignedBigInteger('reviewed_by_user_id')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('generated_by_user_id')->nullable();
            $table->unsignedBigInteger('sent_by_user_id')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('email_to')->nullable();
            $table->string('email_subject')->nullable();
            $table->longText('email_body')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Note: Foreign keys optional depending on if we want strict integrity, but typically good for templates
            $table->foreign('template_id')->references('id')->on('document_templates')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('generated_documents');
    }
}
