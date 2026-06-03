<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInvalidPlaceholdersToDocumentTemplates extends Migration
{
    public function up()
    {
        if (Schema::hasTable('document_templates')) {
            Schema::table('document_templates', function (Blueprint $table) {
                if (!Schema::hasColumn('document_templates', 'invalid_placeholders')) {
                    $table->json('invalid_placeholders')->nullable()->after('detected_placeholders');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('document_templates')) {
            Schema::table('document_templates', function (Blueprint $table) {
                if (Schema::hasColumn('document_templates', 'invalid_placeholders')) {
                    $table->dropColumn('invalid_placeholders');
                }
            });
        }
    }
}
