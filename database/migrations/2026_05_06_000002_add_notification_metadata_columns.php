<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNotificationMetadataColumns extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('notifications')) {
            return;
        }

        Schema::table('notifications', function (Blueprint $table) {
            if (! Schema::hasColumn('notifications', 'role_id')) {
                $table->unsignedBigInteger('role_id')->nullable()->after('user_id');
            }

            if (! Schema::hasColumn('notifications', 'type')) {
                $table->string('type', 100)->nullable()->after('message');
            }

            if (! Schema::hasColumn('notifications', 'route_name')) {
                $table->string('route_name')->nullable()->after('type');
            }

            if (! Schema::hasColumn('notifications', 'route_params')) {
                $table->json('route_params')->nullable()->after('route_name');
            }

            if (! Schema::hasColumn('notifications', 'data')) {
                $table->json('data')->nullable()->after('route_params');
            }

            if (! Schema::hasColumn('notifications', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('is_read');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('notifications')) {
            return;
        }

        Schema::table('notifications', function (Blueprint $table) {
            foreach (['role_id', 'type', 'route_name', 'route_params', 'data', 'read_at'] as $column) {
                if (Schema::hasColumn('notifications', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
