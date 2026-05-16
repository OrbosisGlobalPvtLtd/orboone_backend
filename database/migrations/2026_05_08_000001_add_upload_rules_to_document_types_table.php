<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_types', function (Blueprint $table) {
            if (! Schema::hasColumn('document_types', 'allowed_extensions')) {
                $table->json('allowed_extensions')->nullable()->after('has_expiry');
            }

            if (! Schema::hasColumn('document_types', 'max_file_size_mb')) {
                $table->unsignedInteger('max_file_size_mb')->default(5)->after('allowed_extensions');
            }

            if (! Schema::hasColumn('document_types', 'allow_multiple')) {
                $table->boolean('allow_multiple')->default(false)->after('max_file_size_mb');
            }
        });
    }

    public function down(): void
    {
        Schema::table('document_types', function (Blueprint $table) {
            if (Schema::hasColumn('document_types', 'allow_multiple')) {
                $table->dropColumn('allow_multiple');
            }

            if (Schema::hasColumn('document_types', 'max_file_size_mb')) {
                $table->dropColumn('max_file_size_mb');
            }

            if (Schema::hasColumn('document_types', 'allowed_extensions')) {
                $table->dropColumn('allowed_extensions');
            }
        });
    }
};