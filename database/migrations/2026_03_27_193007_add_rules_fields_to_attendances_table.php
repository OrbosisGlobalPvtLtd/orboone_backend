<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRulesFieldsToAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->boolean('is_late')->default(false)->after('clock_in');
            $table->boolean('is_early_out')->default(false)->after('clock_out');
            $table->decimal('working_hours', 8, 2)->default(0)->after('is_early_out');
            $table->boolean('is_blocked')->default(false)->after('status');
            $table->integer('total_break_time')->default(0)->after('working_hours'); // in minutes
            $table->string('leave_marking')->nullable()->after('status'); // Full Day, Half Day, LWP
            $table->unsignedBigInteger('manual_unlock_by')->nullable()->after('is_blocked');
            $table->text('punch_in_note')->nullable()->after('note');
            $table->text('punch_out_note')->nullable()->after('punch_in_note');
            
            $table->foreign('manual_unlock_by')->references('id')->on('users')->onDelete('set null');
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
            $table->dropForeign(['manual_unlock_by']);
            $table->dropColumn([
                'is_late', 'is_early_out', 'working_hours', 'is_blocked', 
                'total_break_time', 'leave_marking', 'manual_unlock_by', 
                'punch_in_note', 'punch_out_note'
            ]);
        });
    }
}
