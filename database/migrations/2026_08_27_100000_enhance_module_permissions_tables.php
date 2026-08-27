<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_module_access')) {
            Schema::table('user_module_access', function (Blueprint $table) {
                if (! Schema::hasColumn('user_module_access', 'permission_key')) {
                    $table->string('permission_key', 150)->nullable()->after('module_key');
                }
                if (! Schema::hasColumn('user_module_access', 'permission_id')) {
                    $table->unsignedBigInteger('permission_id')->nullable()->after('permission_key');
                }
                if (! Schema::hasColumn('user_module_access', 'is_allowed')) {
                    $table->boolean('is_allowed')->default(true)->after('is_enabled');
                }
                $table->index(['user_id', 'permission_key']);
            });
        }

        if (Schema::hasTable('department_module_access')) {
            Schema::table('department_module_access', function (Blueprint $table) {
                if (! Schema::hasColumn('department_module_access', 'permission_key')) {
                    $table->string('permission_key', 150)->nullable()->after('module_key');
                }
                if (! Schema::hasColumn('department_module_access', 'permission_id')) {
                    $table->unsignedBigInteger('permission_id')->nullable()->after('permission_key');
                }
                if (! Schema::hasColumn('department_module_access', 'is_allowed')) {
                    $table->boolean('is_allowed')->default(true)->after('is_enabled');
                }
                $table->index(['department_id', 'permission_key']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('user_module_access')) {
            Schema::table('user_module_access', function (Blueprint $table) {
                if (Schema::hasColumn('user_module_access', 'permission_key')) {
                    $table->dropColumn(['permission_key', 'permission_id', 'is_allowed']);
                }
            });
        }

        if (Schema::hasTable('department_module_access')) {
            Schema::table('department_module_access', function (Blueprint $table) {
                if (Schema::hasColumn('department_module_access', 'permission_key')) {
                    $table->dropColumn(['permission_key', 'permission_id', 'is_allowed']);
                }
            });
        }
    }
};
