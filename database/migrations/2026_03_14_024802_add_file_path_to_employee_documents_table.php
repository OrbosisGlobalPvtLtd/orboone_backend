<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFilePathToEmployeeDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('employee_documents', 'file_path')) {
            Schema::table('employee_documents', function (Blueprint $table) {
                $table->string('file_path')->nullable();
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
        if (Schema::hasColumn('employee_documents', 'file_path')) {
            Schema::table('employee_documents', function (Blueprint $table) {
                $table->dropColumn('file_path');
            });
        }
    }
}