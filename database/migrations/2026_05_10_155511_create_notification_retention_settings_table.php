<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 1. Create Retention Settings Table
        Schema::create('notification_retention_settings', function (Blueprint $table) {
            $table->id();
            $table->string('notification_type')->unique();
            $table->string('display_name');
            $table->integer('retention_days')->default(45);
            $table->boolean('delete_only_read')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->unsignedBigInteger('updated_by_user_id')->nullable();
            $table->timestamps();
        });

        // 2. Add Indexes to Notifications Table
        Schema::table('notifications', function (Blueprint $table) {
            if (Schema::hasTable('notifications')) {
                $table->index(['user_id', 'is_read', 'created_at'], 'notifications_user_read_at_idx');
                $table->index(['type', 'created_at'], 'notifications_type_at_idx');
            }
        });

        // 3. Seed Default Records
        $defaults = [
            ['notification_type' => 'announcement', 'display_name' => 'Announcement', 'retention_days' => 90],
            ['notification_type' => 'attendance', 'display_name' => 'Attendance', 'retention_days' => 30],
            ['notification_type' => 'document', 'display_name' => 'Document', 'retention_days' => 60],
            ['notification_type' => 'leave', 'display_name' => 'Leave', 'retention_days' => 60],
            ['notification_type' => 'payroll', 'display_name' => 'Payroll', 'retention_days' => 180],
            ['notification_type' => 'general', 'display_name' => 'General', 'retention_days' => 45],
        ];

        foreach ($defaults as $data) {
            DB::table('notification_retention_settings')->insert(array_merge($data, [
                'delete_only_read' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down()
    {
        Schema::dropIfExists('notification_retention_settings');
        
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_user_read_at_idx');
            $table->dropIndex('notifications_type_at_idx');
        });
    }
};
