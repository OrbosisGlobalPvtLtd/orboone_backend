<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeAttendanceTimesToString extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Using raw SQL because Doctrine DBAL might not be installed
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE attendances MODIFY clock_in VARCHAR(255) NULL');
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE attendances MODIFY clock_out VARCHAR(255) NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendances', function (Blueprint $table) {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE attendances MODIFY clock_in TIME NULL');
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE attendances MODIFY clock_out TIME NULL');
        });
    }
}
