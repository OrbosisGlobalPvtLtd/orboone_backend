<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHtmlFieldsToGeneratedDocumentsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('generated_documents')) {
            Schema::table('generated_documents', function (Blueprint $table) {
                if (!Schema::hasColumn('generated_documents', 'candidate_name')) {
                    $table->string('candidate_name')->nullable()->after('employee_id');
                }
                if (!Schema::hasColumn('generated_documents', 'template_key')) {
                    $table->string('template_key', 100)->nullable()->after('template_id');
                }
                if (!Schema::hasColumn('generated_documents', 'template_type')) {
                    $table->string('template_type', 30)->default('html')->after('template_key');
                }
                if (!Schema::hasColumn('generated_documents', 'form_data')) {
                    $table->json('form_data')->nullable()->after('field_values');
                }
                if (!Schema::hasColumn('generated_documents', 'generated_by')) {
                    $table->unsignedBigInteger('generated_by')->nullable()->after('generated_by_user_id');
                }
                if (!Schema::hasColumn('generated_documents', 'generated_at')) {
                    $table->timestamp('generated_at')->nullable()->after('created_at');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('generated_documents')) {
            Schema::table('generated_documents', function (Blueprint $table) {
                if (Schema::hasColumn('generated_documents', 'generated_at')) {
                    $table->dropColumn('generated_at');
                }
                if (Schema::hasColumn('generated_documents', 'generated_by')) {
                    $table->dropColumn('generated_by');
                }
                if (Schema::hasColumn('generated_documents', 'form_data')) {
                    $table->dropColumn('form_data');
                }
                if (Schema::hasColumn('generated_documents', 'template_type')) {
                    $table->dropColumn('template_type');
                }
                if (Schema::hasColumn('generated_documents', 'template_key')) {
                    $table->dropColumn('template_key');
                }
                if (Schema::hasColumn('generated_documents', 'candidate_name')) {
                    $table->dropColumn('candidate_name');
                }
            });
        }
    }
}
