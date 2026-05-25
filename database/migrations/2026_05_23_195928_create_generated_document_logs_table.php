<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeneratedDocumentLogsTable extends Migration
{
    public function up()
    {
        Schema::create('generated_document_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('generated_document_id');
            $table->string('action');
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('actor_user_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->foreign('generated_document_id')->references('id')->on('generated_documents')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('generated_document_logs');
    }
}
