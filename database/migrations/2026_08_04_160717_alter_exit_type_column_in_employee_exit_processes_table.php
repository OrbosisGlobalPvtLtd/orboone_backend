<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AlterExitTypeColumnInEmployeeExitProcessesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE employee_exit_processes MODIFY COLUMN exit_type VARCHAR(100) NULL DEFAULT 'resignation'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE employee_exit_processes MODIFY COLUMN exit_type ENUM('resignation','termination','internship_completed_exit') NULL DEFAULT 'resignation'");
    }
}
