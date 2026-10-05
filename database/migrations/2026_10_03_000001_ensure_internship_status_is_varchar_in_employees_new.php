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
        if (Schema::hasTable('employees_new') && Schema::hasColumn('employees_new', 'internship_status')) {
            if (config('database.default') === 'mysql') {
                DB::statement("ALTER TABLE employees_new MODIFY COLUMN internship_status VARCHAR(50) NULL DEFAULT 'active'");
            } else {
                Schema::table('employees_new', function (Blueprint $table) {
                    $table->string('internship_status', 50)->nullable()->default('active')->change();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('employees_new') && Schema::hasColumn('employees_new', 'internship_status')) {
            if (config('database.default') === 'mysql') {
                DB::statement("ALTER TABLE employees_new MODIFY COLUMN internship_status ENUM('active','extended','completed','converted_to_probation','scheduled_probation','exited') NULL DEFAULT 'active'");
            }
        }
    }
};
