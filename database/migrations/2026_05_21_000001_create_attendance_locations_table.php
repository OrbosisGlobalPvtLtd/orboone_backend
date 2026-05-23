<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceLocationsTable extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('attendance_locations')) {
            Schema::create('attendance_locations', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->decimal('latitude', 10, 7);
                $table->decimal('longitude', 10, 7);
                $table->unsignedInteger('radius_meters')->default(100);
                $table->boolean('is_default')->default(false)->index();
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });

            return;
        }

        Schema::table('attendance_locations', function (Blueprint $table) {
            if (! Schema::hasColumn('attendance_locations', 'name')) {
                $table->string('name')->after('id');
            }
            if (! Schema::hasColumn('attendance_locations', 'code')) {
                $table->string('code')->unique()->after('name');
            }
            if (! Schema::hasColumn('attendance_locations', 'latitude')) {
                $table->decimal('latitude', 10, 7)->after('code');
            }
            if (! Schema::hasColumn('attendance_locations', 'longitude')) {
                $table->decimal('longitude', 10, 7)->after('latitude');
            }
            if (! Schema::hasColumn('attendance_locations', 'radius_meters')) {
                $table->unsignedInteger('radius_meters')->default(100)->after('longitude');
            }
            if (! Schema::hasColumn('attendance_locations', 'is_default')) {
                $table->boolean('is_default')->default(false)->index()->after('radius_meters');
            }
            if (! Schema::hasColumn('attendance_locations', 'is_active')) {
                $table->boolean('is_active')->default(true)->index()->after('is_default');
            }
            if (! Schema::hasColumn('attendance_locations', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_locations');
    }
}
