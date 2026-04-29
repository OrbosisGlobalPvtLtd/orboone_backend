<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskmanagementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('taskmanagement')) {
            Schema::create('taskmanagement', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->date('due_date');
            $table->enum('status', ['pending', 'progress', 'completed', 'overdue'])->default('pending');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Assigned user
            $table->text('updates')->nullable(); // For employees to add updates
            $table->timestamps();
        });
    }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('taskmanagement');
    }
}
