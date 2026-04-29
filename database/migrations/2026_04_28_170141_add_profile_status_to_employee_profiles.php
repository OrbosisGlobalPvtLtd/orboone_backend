<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProfileStatusToEmployeeProfiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::table('employee_profiles', function (Blueprint $table) {
        $table->enum('profile_status', ['pending','submitted','approved','rejected'])
              ->default('pending')
              ->after('employee_id');

        $table->text('rejection_reason')->nullable();
    });
}

public function down()
{
    Schema::table('employee_profiles', function (Blueprint $table) {
        $table->dropColumn([
            'profile_status',
            'rejection_reason'
        ]);
    });
}
}
