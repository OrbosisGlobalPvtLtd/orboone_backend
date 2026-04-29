<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentTypeModalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::create('document_types', function (Blueprint $table) {
    $table->id();
    $table->string('name'); // Aadhaar, PAN, Passport
    $table->enum('scope', ['employee', 'policy']);
    $table->boolean('is_mandatory')->default(false);
    $table->boolean('has_expiry')->default(false);
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
        Schema::dropIfExists('document_type_modals');
    }
}
