<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
   public function up()
{
    Schema::create('attendances', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id');

        $table->date('date')->default(now());
        $table->time('clock_in')->nullable();
        $table->time('clock_out')->nullable();
        $table->string('status')->default('Present');
        $table->string('note')->nullable();
        $table->string('work_type')->nullable(); // WFH or WFO
        $table->string('latitude')->nullable();
        $table->string('longitude')->nullable();


        $table->timestamps();

        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    });
}

    public function down()
    {
        Schema::dropIfExists('attendances');
    }
}
