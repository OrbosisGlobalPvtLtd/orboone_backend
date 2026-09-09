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
        if (Schema::hasTable('employee_documents_new')) {
            Schema::table('employee_documents_new', function (Blueprint $table) {
                if (! Schema::hasColumn('employee_documents_new', 'document_type_id')) {
                    $table->unsignedBigInteger('document_type_id')->nullable()->after('category_id');
                }
            });

            if (Schema::hasColumn('employee_documents_new', 'category_id')) {
                DB::statement('UPDATE employee_documents_new SET document_type_id = category_id WHERE document_type_id IS NULL AND category_id IS NOT NULL');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('employee_documents_new') && Schema::hasColumn('employee_documents_new', 'document_type_id')) {
            Schema::table('employee_documents_new', function (Blueprint $table) {
                $table->dropColumn('document_type_id');
            });
        }
    }
};
