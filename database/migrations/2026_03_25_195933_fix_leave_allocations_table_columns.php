<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixLeaveAllocationsTableColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leave_allocations', function (Blueprint $table) {
            if (!Schema::hasColumn('leave_allocations', 'total_pl')) {
                $table->decimal('total_pl', 8, 2)->default(0)->after('year');
            }
            if (!Schema::hasColumn('leave_allocations', 'total_sl')) {
                $table->decimal('total_sl', 8, 2)->default(0)->after('total_pl');
            }
            if (!Schema::hasColumn('leave_allocations', 'lwp_days')) {
                $table->decimal('lwp_days', 8, 2)->default(0)->after('used_sl');
            }
        });
    }

    public function down()
    {
        Schema::table('leave_allocations', function (Blueprint $table) {
            // No reverse needed for a fix
        });
    }
}
