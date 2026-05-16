<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_app_versions', function (Blueprint $table) {
            $table->id();
            $table->string('app_name')->default('OrboOne HRMS');
            $table->string('platform')->default('android');
            $table->string('version_name');
            $table->unsignedInteger('version_code');
            $table->unsignedInteger('min_supported_version_code')->default(1);
            $table->string('apk_file')->nullable();
            $table->string('apk_original_name')->nullable();
            $table->unsignedBigInteger('apk_size')->nullable();
            $table->string('apk_mime_type')->nullable();
            $table->text('apk_url')->nullable();
            $table->longText('release_notes')->nullable();
            $table->boolean('is_force_update')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('release_date')->nullable();
            $table->unsignedBigInteger('uploaded_by_user_id')->nullable();
            $table->timestamps();

            $table->unique(['platform', 'version_code']);
            $table->index(['platform', 'is_active']);
            $table->index('version_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_app_versions');
    }
};
