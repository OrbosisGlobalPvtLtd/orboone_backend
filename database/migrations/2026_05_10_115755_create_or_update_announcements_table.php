<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrUpdateAnnouncementsTable extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('announcements')) {
            Schema::create('announcements', function (Blueprint $table) {
                $table->id();

                $table->string('title');
                $table->longText('description');

                $table->enum('type', [
                    'general',
                    'holiday',
                    'emergency',
                    'policy',
                    'meeting'
                ])->default('general');

                $table->enum('priority', [
                    'low',
                    'normal',
                    'high',
                    'urgent'
                ])->default('normal');

                $table->enum('target_type', [
                    'all',
                    'employee',
                    'admin',
                    'hr'
                ])->default('all');

                $table->foreignId('created_by_user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();

                $table->string('attachment')->nullable();

                $table->boolean('is_active')->default(true);

                $table->timestamps();
            });
        } else {
            try {
                Schema::table('announcements', function (Blueprint $table) {
                    $table->dropForeign(['created_by']);
                });
            } catch (\Exception $e) {}

            Schema::table('announcements', function (Blueprint $table) {
                if (!Schema::hasColumn('announcements', 'type')) {
                    $table->string('type')->default('general')->after('description');
                }
                if (!Schema::hasColumn('announcements', 'priority')) {
                    $table->string('priority')->default('normal')->after('type');
                }
                if (!Schema::hasColumn('announcements', 'target_type')) {
                    $table->string('target_type')->default('all')->after('priority');
                }
                if (!Schema::hasColumn('announcements', 'created_by_user_id')) {
                    $table->unsignedBigInteger('created_by_user_id')->nullable()->after('target_type');
                }
                if (!Schema::hasColumn('announcements', 'start_date')) {
                    $table->date('start_date')->nullable()->after('created_by_user_id');
                }
                if (!Schema::hasColumn('announcements', 'end_date')) {
                    $table->date('end_date')->nullable()->after('start_date');
                }
                if (!Schema::hasColumn('announcements', 'attachment')) {
                    $table->string('attachment')->nullable()->after('end_date');
                }
                if (!Schema::hasColumn('announcements', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('attachment');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
}
