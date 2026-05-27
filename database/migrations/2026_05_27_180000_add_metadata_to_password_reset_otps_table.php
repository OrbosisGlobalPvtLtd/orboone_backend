<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('password_reset_otps')) {
            return;
        }

        Schema::table('password_reset_otps', function (Blueprint $table) {
            if (! Schema::hasColumn('password_reset_otps', 'last_sent_at')) {
                $table->timestamp('last_sent_at')->nullable()->after('attempts');
            }
            if (! Schema::hasColumn('password_reset_otps', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('last_sent_at');
            }
            if (! Schema::hasColumn('password_reset_otps', 'user_agent')) {
                $table->string('user_agent', 255)->nullable()->after('ip_address');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('password_reset_otps')) {
            return;
        }

        Schema::table('password_reset_otps', function (Blueprint $table) {
            if (Schema::hasColumn('password_reset_otps', 'user_agent')) {
                $table->dropColumn('user_agent');
            }
            if (Schema::hasColumn('password_reset_otps', 'ip_address')) {
                $table->dropColumn('ip_address');
            }
            if (Schema::hasColumn('password_reset_otps', 'last_sent_at')) {
                $table->dropColumn('last_sent_at');
            }
        });
    }
};
