<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddArchiveTrackingToEmployeeDocumentsNewTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('employee_documents_new')) {
            Schema::table('employee_documents_new', function (Blueprint $table) {
                if (!Schema::hasColumn('employee_documents_new', 'is_active')) {
                    $table->boolean('is_active')->default(true)->index()->after('is_required');
                }

                if (!Schema::hasColumn('employee_documents_new', 'archived_at')) {
                    $table->timestamp('archived_at')->nullable()->after('is_active');
                }

                if (!Schema::hasColumn('employee_documents_new', 'archived_by')) {
                    $table->unsignedBigInteger('archived_by')->nullable()->after('archived_at');
                }

                if (!Schema::hasColumn('employee_documents_new', 'archive_reason')) {
                    $table->string('archive_reason')->nullable()->after('archived_by');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('employee_documents_new')) {
            Schema::table('employee_documents_new', function (Blueprint $table) {
                foreach (['is_active', 'archived_at', 'archived_by', 'archive_reason'] as $column) {
                    if (Schema::hasColumn('employee_documents_new', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
}
