<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('menus')) {
            Schema::create('menus', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('route')->nullable();
                $table->string('icon')->nullable();
                $table->string('module_key')->nullable();
                $table->string('permission_key')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        } else {
            Schema::table('menus', function (Blueprint $table) {
                if (!Schema::hasColumn('menus', 'route')) $table->string('route')->nullable();
                if (!Schema::hasColumn('menus', 'icon')) $table->string('icon')->nullable();
                if (!Schema::hasColumn('menus', 'module_key')) $table->string('module_key')->nullable();
                if (!Schema::hasColumn('menus', 'permission_key')) $table->string('permission_key')->nullable();
                if (!Schema::hasColumn('menus', 'parent_id')) $table->unsignedBigInteger('parent_id')->nullable();
                if (!Schema::hasColumn('menus', 'sort_order')) $table->integer('sort_order')->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('menus');
    }
}
