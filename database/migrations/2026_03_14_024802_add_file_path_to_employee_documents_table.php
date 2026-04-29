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
        Schema::table('employee_documents', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_documents', 'file_path')) {
                $table->string('file_path')->nullable()->after('experiencelatter');
            }
            // Also fix the status enum if needed, or ensure it accepts 'approved'
            // The current enum is ('pending','verified','rejected')
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_documents', function (Blueprint $table) {
            //
        });
    }
}
