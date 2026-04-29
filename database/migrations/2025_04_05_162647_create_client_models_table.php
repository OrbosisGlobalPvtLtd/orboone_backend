<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientModelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {

            $table->id();

            // BASIC INFO
            $table->string('cleint_name');
            $table->string('mobile_no', 10);
            $table->string('email_id')->unique();

            // BANK DETAILS
            $table->string('bank_account_no'); // ❌ was wrongly set to date
            $table->string('ifce_code');
            $table->string('bank_name_branch');

            // IDENTIFICATION
            $table->string('gst_in', 15);
            $table->string('pan', 10);
            $table->string('aadhar', 12);
            $table->string('firm_name');

            // LOGIN CREDENTIALS
            $table->string('gst_login_id');
            $table->string('gst_login_password');
            $table->string('income_tax_login_id');
            $table->string('income_tax_login_password');
            $table->string('e_way_bill_id');
            $table->string('e_way_bill_password');
            $table->string('e_invoice_id');
            $table->string('e_invoice_password');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clients');
    }
}