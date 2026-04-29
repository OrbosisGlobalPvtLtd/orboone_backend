<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeProfilesTable extends Migration
{
    public function up()
    {
        Schema::create('employee_profiles', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('employee_id')->unique();

            $table->string('profile_image')->nullable();
            $table->date('date_of_birth')->nullable();

            $table->enum('gender', ['male', 'female', 'other'])->nullable();

            $table->text('address')->nullable();

            $table->string('highest_qualification')->nullable();
            $table->string('cgpa_percentage', 50)->nullable();
            $table->string('total_experience', 100)->nullable();

            $table->string('resume_file')->nullable();

            $table->string('bank_account_no', 100)->nullable();
            $table->string('bank_account_type', 100)->nullable();
            $table->string('bank_holder_name', 150)->nullable();
            $table->string('ifsc_code', 50)->nullable();
            $table->string('bank_branch', 150)->nullable();

            $table->boolean('is_profile_completed')->default(false);
            $table->timestamp('profile_completed_at')->nullable();

            $table->timestamps();

            // 🔗 Foreign Key (IMPORTANT)
            $table->foreign('employee_id')
                  ->references('id')
                  ->on('employees_new') // 👈 agar new table use kar rahe ho
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_profiles');
    }
}