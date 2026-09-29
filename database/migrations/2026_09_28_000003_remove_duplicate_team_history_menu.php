<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('menus')) {
            return;
        }

        DB::transaction(function (): void {
            $duplicate = DB::table('menus')->where('id', 377)->first();

            if ($duplicate && (
                $duplicate->name !== 'Team History'
                || $duplicate->route !== 'reporting.history'
                || (int) $duplicate->parent_id !== 370
            )) {
                throw new RuntimeException('Menu ID 377 no longer matches the confirmed Team History duplicate; refusing cleanup.');
            }

            if (! $duplicate) {
                // Also clean up stale relationship rows if a prior/manual removal deleted the menu only.
                if (Schema::hasTable('role_menu_access')) {
                    DB::table('role_menu_access')->where('menu_id', 377)->delete();
                }
                if (Schema::hasTable('accesses')) {
                    DB::table('accesses')->where('menu_id', 377)->delete();
                }
                if (DB::table('menus')->where('parent_id', 377)->exists()) {
                    throw new RuntimeException('Menu ID 377 has child menus; refusing to leave orphaned hierarchy records.');
                }
                $this->assertNoUnexpectedForeignKeyReferences();

                return;
            }

            $canonical = DB::table('menus')->where('id', 360)->first();
            if (! $canonical
                || $canonical->name !== 'Reporting History'
                || $canonical->route !== 'reporting.history'
                || (int) $canonical->parent_id !== 350
                || ! DB::table('menus')->where('id', 350)->exists()) {
                throw new RuntimeException('Canonical menu ID 360 is missing or has unexpected identity/hierarchy; refusing cleanup.');
            }

            if (DB::table('menus')->where('parent_id', 377)->exists()) {
                throw new RuntimeException('Menu ID 377 has child menus; refusing to create orphaned hierarchy records.');
            }

            $now = now();

            if (Schema::hasTable('role_menu_access')) {
                $roleAccess = DB::table('role_menu_access')->where('menu_id', 377)->get();
                foreach ($roleAccess as $access) {
                    $canonicalAccessExists = DB::table('role_menu_access')
                        ->where('role_id', $access->role_id)
                        ->where('menu_id', 360)
                        ->exists();

                    if (! $canonicalAccessExists) {
                        DB::table('role_menu_access')->insert([
                            'role_id' => $access->role_id,
                            'menu_id' => 360,
                            'created_at' => $access->created_at ?? $now,
                            'updated_at' => $now,
                        ]);
                    }
                }
            }

            if (Schema::hasTable('accesses')) {
                $legacyAccess = DB::table('accesses')
                    ->where('menu_id', 377)
                    ->select('role_id', DB::raw('MAX(status) as status'))
                    ->groupBy('role_id')
                    ->get();

                foreach ($legacyAccess as $access) {
                    $canonicalAccess = DB::table('accesses')
                        ->where('menu_id', 360)
                        ->where('role_id', $access->role_id);

                    if ($canonicalAccess->exists()) {
                        $currentStatus = (int) $canonicalAccess->max('status');
                        if ($currentStatus < (int) $access->status) {
                            $canonicalAccess->update([
                                'status' => $access->status,
                                'updated_at' => $now,
                            ]);
                        }
                    } else {
                        DB::table('accesses')->insert([
                            'role_id' => $access->role_id,
                            'menu_id' => 360,
                            'status' => $access->status,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                }
            }

            if (Schema::hasTable('role_menu_access')) {
                DB::table('role_menu_access')->where('menu_id', 377)->delete();
            }
            if (Schema::hasTable('accesses')) {
                DB::table('accesses')->where('menu_id', 377)->delete();
            }

            $this->assertNoUnexpectedForeignKeyReferences();

            DB::table('menus')->where('id', 377)->delete();
        });
    }

    private function assertNoUnexpectedForeignKeyReferences(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        $foreignReferences = DB::select(
            'SELECT TABLE_NAME AS table_name, COLUMN_NAME AS column_name
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND REFERENCED_TABLE_SCHEMA = DATABASE()
               AND REFERENCED_TABLE_NAME = ?
               AND REFERENCED_COLUMN_NAME = ?',
            ['menus', 'id']
        );

        foreach ($foreignReferences as $reference) {
            if (($reference->table_name === 'role_menu_access' && $reference->column_name === 'menu_id')
                || ($reference->table_name === 'accesses' && $reference->column_name === 'menu_id')) {
                continue;
            }

            if (Schema::hasTable($reference->table_name)
                && DB::table($reference->table_name)->where($reference->column_name, 377)->exists()) {
                throw new RuntimeException(sprintf(
                    'Unexpected foreign-key reference to menu 377 from %s.%s; refusing cleanup.',
                    $reference->table_name,
                    $reference->column_name
                ));
            }
        }
    }

    public function down(): void
    {
        // The duplicate menu is permanently retired; rollback must not recreate it or its obsolete mappings.
    }
};
