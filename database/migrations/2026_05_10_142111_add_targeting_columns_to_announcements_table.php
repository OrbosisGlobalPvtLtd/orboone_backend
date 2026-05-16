<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTargetingColumnsToAnnouncementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('announcements', function (Blueprint $blueprint) {
            $blueprint->unsignedBigInteger('target_role_id')->nullable()->after('target_type');
            $blueprint->unsignedBigInteger('target_department_id')->nullable()->after('target_role_id');
            $blueprint->unsignedBigInteger('target_user_id')->nullable()->after('target_department_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('announcements', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['target_role_id', 'target_department_id', 'target_user_id']);
        });
    }
}
