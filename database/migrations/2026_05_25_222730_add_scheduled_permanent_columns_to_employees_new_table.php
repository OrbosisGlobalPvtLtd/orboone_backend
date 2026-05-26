<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddScheduledPermanentColumnsToEmployeesNewTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employees_new', function (Blueprint $table) {
            $table->date('confirmation_effective_date')->nullable()->after('confirmation_date');
            $table->unsignedBigInteger('permanent_scheduled_by_user_id')->nullable()->after('confirmation_effective_date');
            $table->timestamp('permanent_scheduled_at')->nullable()->after('permanent_scheduled_by_user_id');
            $table->timestamp('permanent_activated_at')->nullable()->after('permanent_scheduled_at');
        });

        if (config('database.default') === 'mysql') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE employees_new MODIFY COLUMN probation_status ENUM('pending', 'ongoing', 'completed', 'confirmed', 'scheduled_permanent', 'confirmation_scheduled') DEFAULT 'pending'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (config('database.default') === 'mysql') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE employees_new MODIFY COLUMN probation_status ENUM('pending', 'ongoing', 'completed', 'confirmed') DEFAULT 'pending'");
        }

        Schema::table('employees_new', function (Blueprint $table) {
            $table->dropColumn([
                'confirmation_effective_date',
                'permanent_scheduled_by_user_id',
                'permanent_scheduled_at',
                'permanent_activated_at'
            ]);
        });
    }
}
