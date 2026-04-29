<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyDocumentModalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_documents', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->string('category');
    $table->string('file_path');
    $table->json('visible_to')->nullable(); // role / department
    $table->boolean('download_allowed')->default(true);
    $table->foreignId('uploaded_by')->constrained('users');
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('company_document_modals');
    }
}
