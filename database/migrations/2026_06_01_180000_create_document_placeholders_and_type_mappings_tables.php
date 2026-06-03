<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentPlaceholdersAndTypeMappingsTables extends Migration
{
    public function up()
    {
        // 1. Create document_placeholders table
        if (!Schema::hasTable('document_placeholders')) {
            Schema::create('document_placeholders', function (Blueprint $table) {
                $table->id();
                $table->string('placeholder_key')->unique();
                $table->string('label');
                $table->string('group_name');
                $table->string('source_type')->nullable(); // e.g. employee, company, salary, manual
                $table->string('source_table')->nullable();
                $table->string('source_column')->nullable();
                $table->string('resolver_key')->nullable();
                $table->string('default_value')->nullable();
                $table->string('sample_value')->nullable();
                $table->boolean('is_required')->default(false);
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // 2. Create document_type_placeholders table
        if (!Schema::hasTable('document_type_placeholders')) {
            Schema::create('document_type_placeholders', function (Blueprint $table) {
                $table->id();
                $table->string('document_type'); // offer_letter, appointment_letter, etc.
                $table->unsignedBigInteger('placeholder_id');
                $table->boolean('is_required')->default(false);
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->foreign('placeholder_id')->references('id')->on('document_placeholders')->onDelete('cascade');
            });
        }

        // 3. Extend document_templates table
        if (Schema::hasTable('document_templates')) {
            Schema::table('document_templates', function (Blueprint $table) {
                if (!Schema::hasColumn('document_templates', 'template_file_path')) {
                    $table->string('template_file_path')->nullable()->after('html_template');
                }
                if (!Schema::hasColumn('document_templates', 'original_file_name')) {
                    $table->string('original_file_name')->nullable()->after('template_file_path');
                }
                if (!Schema::hasColumn('document_templates', 'detected_placeholders')) {
                    $table->json('detected_placeholders')->nullable()->after('original_file_name');
                }
                if (!Schema::hasColumn('document_templates', 'missing_required_placeholders')) {
                    $table->json('missing_required_placeholders')->nullable()->after('detected_placeholders');
                }
                if (!Schema::hasColumn('document_templates', 'unknown_placeholders')) {
                    $table->json('unknown_placeholders')->nullable()->after('missing_required_placeholders');
                }
                if (!Schema::hasColumn('document_templates', 'placeholder_mapping')) {
                    $table->json('placeholder_mapping')->nullable()->after('unknown_placeholders');
                }
            });
        }

        // 4. Extend generated_documents table
        if (Schema::hasTable('generated_documents')) {
            Schema::table('generated_documents', function (Blueprint $table) {
                if (!Schema::hasColumn('generated_documents', 'placeholder_values')) {
                    $table->json('placeholder_values')->nullable()->after('field_values');
                }
                if (!Schema::hasColumn('generated_documents', 'generated_by')) {
                    $table->unsignedBigInteger('generated_by')->nullable()->after('generated_by_user_id');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('generated_documents')) {
            Schema::table('generated_documents', function (Blueprint $table) {
                if (Schema::hasColumn('generated_documents', 'generated_by')) {
                    $table->dropColumn('generated_by');
                }
                if (Schema::hasColumn('generated_documents', 'placeholder_values')) {
                    $table->dropColumn('placeholder_values');
                }
            });
        }

        if (Schema::hasTable('document_templates')) {
            Schema::table('document_templates', function (Blueprint $table) {
                if (Schema::hasColumn('document_templates', 'placeholder_mapping')) {
                    $table->dropColumn('placeholder_mapping');
                }
                if (Schema::hasColumn('document_templates', 'unknown_placeholders')) {
                    $table->dropColumn('unknown_placeholders');
                }
                if (Schema::hasColumn('document_templates', 'missing_required_placeholders')) {
                    $table->dropColumn('missing_required_placeholders');
                }
                if (Schema::hasColumn('document_templates', 'detected_placeholders')) {
                    $table->dropColumn('detected_placeholders');
                }
                if (Schema::hasColumn('document_templates', 'original_file_name')) {
                    $table->dropColumn('original_file_name');
                }
                if (Schema::hasColumn('document_templates', 'template_file_path')) {
                    $table->dropColumn('template_file_path');
                }
            });
        }

        Schema::dropIfExists('document_type_placeholders');
        Schema::dropIfExists('document_placeholders');
    }
}
