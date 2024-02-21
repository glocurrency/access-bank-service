<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccessTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('access_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('transaction_id')->unique()->index();
            $table->uuid('processing_item_id')->index();

            $table->string('state_code');
            $table->longText('state_code_reason')->nullable();

            $table->string('error_code')->nullable();
            $table->longText('error_code_description')->nullable();
            $table->string('status_code')->nullable();
            $table->longText('status_code_description')->nullable();

            $table->string('reference')->unique();
            $table->string('debit_account');
            $table->string('recipient_account');
            $table->string('recipient_name');
            $table->char('sender_country_code', 3);
            $table->string('sender_name');
            $table->string('bank_code');
            $table->char('currency_code', 3);
            $table->double('amount');
            $table->text('description')->nullable();

            $table->timestamps(6);
            $table->softDeletes('deleted_at', 6);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('access_transactions');
    }
}
