<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalaryStructuresTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('salary_structures', function (Blueprint $table) {
            $table->id(); // primary id

            $table->string('name')->nullable(); 

            // Store salary components JSON
            $table->json('components')->nullable();

            // Optional
            $table->text('description')->nullable();
            $table->boolean('status')->default(1);

            // for who created this
            $table->unsignedBigInteger('created_by')->nullable();

            // default timestamps
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('salary_structures');
    }
}
