<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddIndexesToGeneratedDocumentsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('generated_documents')) {
            Schema::table('generated_documents', function (Blueprint $table) {
                $indexKeys = [];
                try {
                    $indexes = DB::select("SHOW INDEXES FROM generated_documents");
                    foreach ($indexes as $index) {
                        $indexKeys[] = $index->Key_name;
                    }
                } catch (\Throwable $e) {
                    // Fallback for tests or other DB drivers (e.g. SQLite)
                }

                if (Schema::hasColumn('generated_documents', 'employee_id') && !in_array('generated_documents_employee_id_index', $indexKeys)) {
                    $table->index('employee_id');
                }
                if (Schema::hasColumn('generated_documents', 'user_id') && !in_array('generated_documents_user_id_index', $indexKeys)) {
                    $table->index('user_id');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('generated_documents')) {
            Schema::table('generated_documents', function (Blueprint $table) {
                $indexKeys = [];
                try {
                    $indexes = DB::select("SHOW INDEXES FROM generated_documents");
                    foreach ($indexes as $index) {
                        $indexKeys[] = $index->Key_name;
                    }
                } catch (\Throwable $e) {
                    // Fallback
                }

                if (Schema::hasColumn('generated_documents', 'employee_id') && in_array('generated_documents_employee_id_index', $indexKeys)) {
                    $table->dropIndex(['employee_id']);
                }
                if (Schema::hasColumn('generated_documents', 'user_id') && in_array('generated_documents_user_id_index', $indexKeys)) {
                    $table->dropIndex(['user_id']);
                }
            });
        }
    }
}
