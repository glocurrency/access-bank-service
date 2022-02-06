<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccessDebitAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('access_debit_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('account_number');
            $table->string('name');
            $table->char('country_code', 3);
            $table->char('currency_code', 3);
            $table->timestamps(6);
            $table->softDeletes('deleted_at', 6);

            $table->unique(['country_code', 'currency_code']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('access_debit_accounts');
    }
}
