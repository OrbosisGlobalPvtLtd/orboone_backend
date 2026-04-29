<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatutorySettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
   public function up()
{
    Schema::create('statutory_settings', function (Blueprint $table) {
        $table->id();

        // PF 12% example
        $table->decimal('pf_percent', 5, 2)->nullable();

        // ESI 0.75% example
        $table->decimal('esi_percent', 5, 2)->nullable();

        // Professional Tax (state-wise)
        $table->decimal('pt_percent', 5, 2)->nullable();

        // JSON example:
        // [{ "min": 0, "max": 250000, "percent": 0 }, ...]
        $table->json('tds_slabs')->nullable();

        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('statutory_settings');
}

}
