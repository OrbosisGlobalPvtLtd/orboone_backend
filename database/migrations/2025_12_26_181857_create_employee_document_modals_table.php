<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeDocumentModalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
     Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('document_type_id')->nullable();

            $table->string('document_type')->nullable();

            // 🔹 Multiple document fields (NULLABLE)
            $table->string('image')->nullable();
            $table->string('aadharcard')->nullable();
            $table->string('pancard')->nullable();
            $table->string('bankproof')->nullable();
            $table->string('experiencelatter')->nullable();

            $table->date('expiry_date')->nullable();

            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();

            $table->unsignedBigInteger('uploaded_by')->nullable();

            $table->timestamps();

            // 🔐 Foreign Keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('document_type_id')->references('id')->on('document_types')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_documents');
    }
};