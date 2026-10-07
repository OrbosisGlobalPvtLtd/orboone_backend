<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Indexes on menus table
        if (Schema::hasTable('menus')) {
            $existingIndexes = collect(Schema::getIndexes('menus'))->pluck('name')->all();

            Schema::table('menus', function (Blueprint $table) use ($existingIndexes) {
                if (! in_array('menus_route_is_active_index', $existingIndexes, true)) {
                    $table->index(['route', 'is_active'], 'menus_route_is_active_index');
                }

                if (! in_array('menus_name_index', $existingIndexes, true)) {
                    $table->index(['name'], 'menus_name_index');
                }
            });
        }

        // 2. Unique index on role_menu_access table
        if (Schema::hasTable('role_menu_access')) {
            $existingIndexes = collect(Schema::getIndexes('role_menu_access'))->pluck('name')->all();

            if (! in_array('role_menu_access_role_menu_unique', $existingIndexes, true)) {
                // Ensure no duplicate role_id, menu_id rows exist before adding unique index
                $duplicates = DB::table('role_menu_access')
                    ->select('role_id', 'menu_id', DB::raw('MIN(id) as keep_id'), DB::raw('COUNT(*) as count'))
                    ->groupBy('role_id', 'menu_id')
                    ->having('count', '>', 1)
                    ->get();

                $deletedCount = 0;
                foreach ($duplicates as $dup) {
                    $deleted = DB::table('role_menu_access')
                        ->where('role_id', $dup->role_id)
                        ->where('menu_id', $dup->menu_id)
                        ->where('id', '<>', $dup->keep_id)
                        ->delete();
                    $deletedCount += $deleted;
                }

                \Illuminate\Support\Facades\Log::info("role_menu_access unique index preparation: removed {$deletedCount} duplicate rows.");

                Schema::table('role_menu_access', function (Blueprint $table) {
                    $table->unique(['role_id', 'menu_id'], 'role_menu_access_role_menu_unique');
                });
            }
        }

        // 3. Composite index on user_module_access table
        if (Schema::hasTable('user_module_access')) {
            $existingIndexes = collect(Schema::getIndexes('user_module_access'))->pluck('name')->all();

            if (! in_array('user_module_access_user_id_permission_key_index', $existingIndexes, true)) {
                Schema::table('user_module_access', function (Blueprint $table) {
                    $table->index(['user_id', 'permission_key'], 'user_module_access_user_id_permission_key_index');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('menus')) {
            $existingIndexes = collect(Schema::getIndexes('menus'))->pluck('name')->all();

            Schema::table('menus', function (Blueprint $table) use ($existingIndexes) {
                if (in_array('menus_route_is_active_index', $existingIndexes, true)) {
                    $table->dropIndex('menus_route_is_active_index');
                }

                if (in_array('menus_name_index', $existingIndexes, true)) {
                    $table->dropIndex('menus_name_index');
                }
            });
        }

        if (Schema::hasTable('role_menu_access')) {
            $existingIndexes = collect(Schema::getIndexes('role_menu_access'))->pluck('name')->all();

            if (in_array('role_menu_access_role_menu_unique', $existingIndexes, true)) {
                Schema::table('role_menu_access', function (Blueprint $table) {
                    $table->dropUnique('role_menu_access_role_menu_unique');
                });
            }
        }

        if (Schema::hasTable('user_module_access')) {
            $existingIndexes = collect(Schema::getIndexes('user_module_access'))->pluck('name')->all();

            if (in_array('user_module_access_user_id_permission_key_index', $existingIndexes, true)) {
                Schema::table('user_module_access', function (Blueprint $table) {
                    $table->dropIndex('user_module_access_user_id_permission_key_index');
                });
            }
        }
    }
};
