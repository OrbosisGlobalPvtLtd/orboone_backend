<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeePasswordSetupTokensTable extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('employee_password_setup_tokens')) {
            Schema::create('employee_password_setup_tokens', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('token_hash')->unique();
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('used_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('employee_password_setup_tokens');
    }
}
