<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('notifications')) {
            return;
        }

        Schema::table('notifications', function (Blueprint $table) {
            if (! Schema::hasColumn('notifications', 'lifecycle_event_key')) {
                $table->string('lifecycle_event_key', 96)->nullable();
            }
            if (! Schema::hasColumn('notifications', 'activation_delivery_status')) {
                $table->string('activation_delivery_status', 20)->nullable();
            }
            if (! Schema::hasColumn('notifications', 'activation_delivery_claimed_at')) {
                $table->timestamp('activation_delivery_claimed_at')->nullable();
            }
            if (! Schema::hasColumn('notifications', 'activation_delivery_attempts')) {
                $table->unsignedInteger('activation_delivery_attempts')->default(0);
            }
            if (! Schema::hasColumn('notifications', 'activation_delivery_error')) {
                $table->text('activation_delivery_error')->nullable();
            }
        });

        Schema::table('notifications', function (Blueprint $table) {
            if (! Schema::hasColumn('notifications', 'lifecycle_event_key')) {
                return;
            }
            $table->unique(['user_id', 'lifecycle_event_key'], 'notifications_user_lifecycle_event_unique');
            $table->index('lifecycle_event_key', 'notifications_lifecycle_event_idx');
            $table->index(['activation_delivery_status', 'activation_delivery_claimed_at'], 'notifications_activation_delivery_idx');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('notifications')) {
            return;
        }

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropUnique('notifications_user_lifecycle_event_unique');
            $table->dropIndex('notifications_lifecycle_event_idx');
            $table->dropIndex('notifications_activation_delivery_idx');
            foreach ([
                'lifecycle_event_key',
                'activation_delivery_status',
                'activation_delivery_claimed_at',
                'activation_delivery_attempts',
                'activation_delivery_error',
            ] as $column) {
                if (Schema::hasColumn('notifications', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
