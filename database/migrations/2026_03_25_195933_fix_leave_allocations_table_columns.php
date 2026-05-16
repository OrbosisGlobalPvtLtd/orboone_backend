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
            if (!Schema::hasColumn('leave_allocations', 'paid_allocated')) {
                $table->decimal('paid_allocated', 8, 2)->default(0)->after('year');
            }
            if (!Schema::hasColumn('leave_allocations', 'sick_allocated')) {
                $table->decimal('sick_allocated', 8, 2)->default(0)->after('paid_allocated');
            }
            if (!Schema::hasColumn('leave_allocations', 'lwp_used')) {
                $table->decimal('lwp_used', 8, 2)->default(0)->after('sick_used');
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
