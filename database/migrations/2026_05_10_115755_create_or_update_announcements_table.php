<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrUpdateAnnouncementsTable extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->longText('description');

            $table->enum('type', [
                'general',
                'holiday',
                'emergency',
                'policy',
                'meeting'
            ])->default('general');

            $table->enum('priority', [
                'low',
                'normal',
                'high',
                'urgent'
            ])->default('normal');

            $table->enum('target_type', [
                'all',
                'employee',
                'admin',
                'hr'
            ])->default('all');

            $table->foreignId('created_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->string('attachment')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
}
