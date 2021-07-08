<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBankTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('wallet_transaction_id')->constrained('wallet_transactions')->cascadeOnDelete();
            $table->foreignUuid('transfer_recipient_id')->constrained('transfer_recipients')->cascadeOnDelete();
            $table->double('amount', 30, 2)->index();
            $table->string('provider');
            $table->string('reference');
            $table->string('transfer_code');
            $table->string('status');
            $table->softDeletes();
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
        Schema::dropIfExists('bank_transactions');
    }
}
