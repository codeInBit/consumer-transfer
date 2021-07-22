<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeAllFieldThatHoldsMoneyFromDoubleToInt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->integer('balance')->change();
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->integer('amount')->change();
            $table->integer('prev_balance')->change();
            $table->integer('current_balance')->change();
        });

        Schema::table('bank_transactions', function (Blueprint $table) {
            $table->integer('amount')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wallets', function (Blueprint $table) {
            //
        });
    }
}
