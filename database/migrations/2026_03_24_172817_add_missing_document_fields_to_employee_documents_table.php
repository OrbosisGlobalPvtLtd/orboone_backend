<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingDocumentFieldsToEmployeeDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */ 
    public function up()
{
    if (!Schema::hasTable('employee_documents')) {
        return;
    }

    Schema::table('employee_documents', function (Blueprint $table) {
        if (!Schema::hasColumn('employee_documents', 'document_type')) {
            $table->string('document_type')->nullable()->after('category');
        }

        if (!Schema::hasColumn('employee_documents', 'passport_photo')) {
            $table->string('passport_photo')->nullable()->after('document_type');
        }

        if (!Schema::hasColumn('employee_documents', 'aadhar_card')) {
            $table->string('aadhar_card')->nullable()->after('passport_photo');
        }

        if (!Schema::hasColumn('employee_documents', 'pan_card')) {
            $table->string('pan_card')->nullable()->after('aadhar_card');
        }

        if (!Schema::hasColumn('employee_documents', 'bank_proof')) {
            $table->string('bank_proof')->nullable()->after('pan_card');
        }

        if (!Schema::hasColumn('employee_documents', 'educational_documents')) {
            $table->string('educational_documents')->nullable()->after('bank_proof');
        }

        if (!Schema::hasColumn('employee_documents', 'offer_letter')) {
            $table->string('offer_letter')->nullable()->after('educational_documents');
        }

        if (!Schema::hasColumn('employee_documents', 'experience_letter')) {
            $table->string('experience_letter')->nullable()->after('offer_letter');
        }

        if (!Schema::hasColumn('employee_documents', 'salary_slip_3_months')) {
            $table->string('salary_slip_3_months')->nullable()->after('experience_letter');
        }

        if (!Schema::hasColumn('employee_documents', 'relieving_letter')) {
            $table->string('relieving_letter')->nullable()->after('salary_slip_3_months');
        }

        if (!Schema::hasColumn('employee_documents', 'nda_agreement_mou')) {
            $table->string('nda_agreement_mou')->nullable()->after('relieving_letter');
        }
    });
}

    public function down()
    {
        Schema::table('employee_documents', function (Blueprint $table) {
            $table->dropColumn([
                'passport_photo',
                'aadhar_card',
                'pan_card',
                'bank_proof',
                'educational_documents',
                'offer_letter',
                'experience_letter',
                'salary_slip_3_months',
                'relieving_letter',
                'nda_agreement_mou'
            ]);
        });
    }
}
