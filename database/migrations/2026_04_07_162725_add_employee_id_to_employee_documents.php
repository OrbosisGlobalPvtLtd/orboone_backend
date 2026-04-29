<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\EmployeeDocumentModal;
use App\Models\Employee;

class AddEmployeeIdToEmployeeDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('employee_documents', 'employee_id')) {
            Schema::table('employee_documents', function (Blueprint $table) {
                $table->unsignedBigInteger('employee_id')->nullable()->after('user_id');
                $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            });
        }

        // Sync existing data
        $documents = EmployeeDocumentModal::all();
        foreach ($documents as $doc) {
            $employee = Employee::where('user_id', $doc->user_id)->first();
            if ($employee) {
                $doc->update(['employee_id' => $employee->id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_documents', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropColumn('employee_id');
        });
    }
}
