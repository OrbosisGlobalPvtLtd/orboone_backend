<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeDocumentsNewTable extends Migration
{
    public function up()
    {
        Schema::create('employee_documents_new', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('title', 200);
            $table->string('file_path', 255);

            $table->enum('verification_status', ['pending', 'verified', 'rejected'])
                  ->default('pending');

            $table->unsignedBigInteger('verified_by_user_id')->nullable();
            $table->dateTime('uploaded_at')->nullable();

            $table->timestamps();

            $table->foreign('employee_id')
                  ->references('id')
                  ->on('employees_new')
                  ->onDelete('cascade');

            $table->foreign('category_id')
                  ->references('id')
                  ->on('document_categories')
                  ->onDelete('set null');

            $table->foreign('verified_by_user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_documents_new');
    }
}