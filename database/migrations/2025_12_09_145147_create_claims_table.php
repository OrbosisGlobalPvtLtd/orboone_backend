<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClaimsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
   public function up()
{
    Schema::create('claims', function (Blueprint $table) {
        $table->id();

        $table->unsignedBigInteger('employee_id');

        $table->string('category');
        $table->decimal('amount', 10, 2);

        $table->string('file')->nullable();

        $table->enum('status', ['pending', 'approved', 'rejected'])
              ->default('pending');

        $table->timestamps();

        $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
    });
}

public function down()
{
    Schema::dropIfExists('claims');
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
   
}
