<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeaveManagementTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. National Holidays Table
        if (!Schema::hasTable('national_holidays')) {
            Schema::create('national_holidays', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->date('holiday_date')->unique();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        // 2. Leave Allocations Table
        if (!Schema::hasTable('leave_allocations')) {
            Schema::create('leave_allocations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained()->onDelete('cascade');
                $table->integer('year');
                $table->decimal('total_allocated', 8, 2)->default(0);
                $table->decimal('paid_allocated', 8, 2)->default(0);
                $table->decimal('sick_allocated', 8, 2)->default(0);
                $table->decimal('comp_off_allocated', 8, 2)->default(0);
                $table->decimal('total_used', 8, 2)->default(0);
                $table->decimal('paid_used', 8, 2)->default(0);
                $table->decimal('sick_used', 8, 2)->default(0);
                $table->decimal('comp_off_used', 8, 2)->default(0);
                $table->decimal('lwp_used', 8, 2)->default(0);
                $table->decimal('total_remaining', 8, 2)->default(0);
                $table->decimal('paid_remaining', 8, 2)->default(0);
                $table->decimal('sick_remaining', 8, 2)->default(0);
                $table->decimal('comp_off_remaining', 8, 2)->default(0);
                $table->timestamps();
                
                $table->unique(['employee_id', 'year']);
            });
        }

        // 3. Leave Applications Table (Status Tracking)
        if (!Schema::hasTable('leave_applications')) {
            Schema::create('leave_applications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained()->onDelete('cascade');
                $table->enum('leave_type', ['PL', 'SL', 'LWP']);
                $table->date('start_date');
                $table->date('end_date');
                $table->decimal('total_days', 8, 2);
                $table->text('reason')->nullable();
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->foreignId('approved_by')->nullable()->constrained('users');
                $table->text('admin_remark')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('leave_applications');
        Schema::dropIfExists('leave_allocations');
        Schema::dropIfExists('national_holidays');
    }
}
