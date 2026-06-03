<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDocxSupportToDocumentGenerationTables extends Migration
{
    public function up()
    {
        if (Schema::hasTable('document_templates')) {
            Schema::table('document_templates', function (Blueprint $table) {
                if (!Schema::hasColumn('document_templates', 'template_type')) {
                    $table->string('template_type')->default('html')->after('document_type');
                }
                if (!Schema::hasColumn('document_templates', 'docx_file_path')) {
                    $table->string('docx_file_path')->nullable()->after('html_template');
                }
                if (!Schema::hasColumn('document_templates', 'detected_fields')) {
                    $table->json('detected_fields')->nullable()->after('docx_file_path');
                }
                if (!Schema::hasColumn('document_templates', 'version')) {
                    $table->string('version', 50)->default('v1')->after('detected_fields');
                }
                if (!Schema::hasColumn('document_templates', 'is_archived')) {
                    $table->boolean('is_archived')->default(false)->after('is_active');
                }
            });
        }

        if (Schema::hasTable('generated_documents')) {
            Schema::table('generated_documents', function (Blueprint $table) {
                if (!Schema::hasColumn('generated_documents', 'generated_docx_path')) {
                    $table->string('generated_docx_path')->nullable()->after('pdf_path');
                }
                if (!Schema::hasColumn('generated_documents', 'generated_pdf_path')) {
                    $table->string('generated_pdf_path')->nullable()->after('generated_docx_path');
                }
                if (!Schema::hasColumn('generated_documents', 'template_version')) {
                    $table->string('template_version', 50)->nullable()->after('template_id');
                }
                if (!Schema::hasColumn('generated_documents', 'generation_snapshot')) {
                    $table->json('generation_snapshot')->nullable()->after('field_values');
                }
                if (!Schema::hasColumn('generated_documents', 'pdf_status')) {
                    $table->string('pdf_status', 30)->nullable()->after('generated_pdf_path');
                }
                if (!Schema::hasColumn('generated_documents', 'pdf_error_message')) {
                    $table->text('pdf_error_message')->nullable()->after('pdf_status');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('generated_documents')) {
            Schema::table('generated_documents', function (Blueprint $table) {
                if (Schema::hasColumn('generated_documents', 'pdf_error_message')) {
                    $table->dropColumn('pdf_error_message');
                }
                if (Schema::hasColumn('generated_documents', 'pdf_status')) {
                    $table->dropColumn('pdf_status');
                }
                if (Schema::hasColumn('generated_documents', 'generation_snapshot')) {
                    $table->dropColumn('generation_snapshot');
                }
                if (Schema::hasColumn('generated_documents', 'template_version')) {
                    $table->dropColumn('template_version');
                }
                if (Schema::hasColumn('generated_documents', 'generated_pdf_path')) {
                    $table->dropColumn('generated_pdf_path');
                }
                if (Schema::hasColumn('generated_documents', 'generated_docx_path')) {
                    $table->dropColumn('generated_docx_path');
                }
            });
        }

        if (Schema::hasTable('document_templates')) {
            Schema::table('document_templates', function (Blueprint $table) {
                if (Schema::hasColumn('document_templates', 'is_archived')) {
                    $table->dropColumn('is_archived');
                }
                if (Schema::hasColumn('document_templates', 'version')) {
                    $table->dropColumn('version');
                }
                if (Schema::hasColumn('document_templates', 'detected_fields')) {
                    $table->dropColumn('detected_fields');
                }
                if (Schema::hasColumn('document_templates', 'docx_file_path')) {
                    $table->dropColumn('docx_file_path');
                }
                if (Schema::hasColumn('document_templates', 'template_type')) {
                    $table->dropColumn('template_type');
                }
            });
        }
    }
}

