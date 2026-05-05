<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanySettingsTable extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('company_settings')) {
            Schema::create('company_settings', function (Blueprint $table) {
                $table->id();
                $table->string('company_name')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->text('address')->nullable();
                $table->string('website')->nullable();
                $table->string('logo')->nullable();
                $table->string('seal')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('company_settings');
    }
}
