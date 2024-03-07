<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateZenithTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zenith_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('transaction_id')->unique()->index();
            $table->uuid('processing_item_id')->index();

            $table->string('state_code');
            $table->longText('state_code_reason')->nullable();

            $table->string('error_code')->nullable();
            $table->longText('error_code_description')->nullable();

            $table->string('posted_status')->nullable();
            $table->string('reference')->unique();
            $table->string('debit_account');
            $table->string('sender_name');
            $table->string('recipient_account');
            $table->string('recipient_bank_code');
            $table->string('recipient_name');
            $table->char('currency_code', 3);

            $table->unsignedDouble('amount');
            $table->boolean('should_resend');
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
        Schema::dropIfExists('zenith_transactions');
    }
}
