<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHolidaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('holidays')) {
            Schema::create('holidays', function (Blueprint $table) {
                $table->id();
                $table->string('title')->nullable();
                $table->string('name')->nullable();
                $table->date('holiday_date')->nullable();
                $table->date('date')->nullable();
                $table->string('holiday_type')->default('company');
                $table->boolean('is_national')->default(false);
                $table->boolean('is_optional')->default(false);
                $table->boolean('is_working_day_override')->default(false);
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('created_by_user_id')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('holidays', function (Blueprint $table) {
                if (!Schema::hasColumn('holidays', 'title')) $table->string('title')->nullable();
                if (!Schema::hasColumn('holidays', 'name')) $table->string('name')->nullable();
                if (!Schema::hasColumn('holidays', 'holiday_date')) $table->date('holiday_date')->nullable();
                if (!Schema::hasColumn('holidays', 'date')) $table->date('date')->nullable();
                if (!Schema::hasColumn('holidays', 'holiday_type')) $table->string('holiday_type')->default('company');
                if (!Schema::hasColumn('holidays', 'is_national')) $table->boolean('is_national')->default(false);
                if (!Schema::hasColumn('holidays', 'is_optional')) $table->boolean('is_optional')->default(false);
                if (!Schema::hasColumn('holidays', 'is_working_day_override')) $table->boolean('is_working_day_override')->default(false);
                if (!Schema::hasColumn('holidays', 'is_active')) $table->boolean('is_active')->default(true);
                if (!Schema::hasColumn('holidays', 'created_by_user_id')) $table->unsignedBigInteger('created_by_user_id')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('holidays');
    }
}
