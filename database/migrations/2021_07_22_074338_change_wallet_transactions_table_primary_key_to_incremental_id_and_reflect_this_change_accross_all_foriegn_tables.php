<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\WalletTransaction;
use App\Models\BankTransaction;

class ChangeWalletTransactionsTablePrimaryKeyToIncrementalIdAndReflectThisChangeAccrossAllForiegnTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Drop forign keys
        Schema::table('bank_transactions', function (Blueprint $table) {
            $table->dropForeign(['wallet_transaction_id']);
        });

        //Drop primary key contraint on id then rename id field to uuid
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropPrimary('wallet_transactions_id_primary');
            $table->renameColumn('id', 'uuid');
        });

        //Make uuid field nullable and create increamental id field.
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->nullable()->change();
            $table->bigIncrements('id')->after('uuid');
        });

        //Change the value accross all table where id is a foriegn key 
        $walletTransactions = WalletTransaction::all();
        foreach ($walletTransactions as $key => $walletTransaction) {
            BankTransaction::where('wallet_transaction_id', $walletTransaction->uuid)->update([
                'wallet_transaction_id' => $walletTransaction->id,
            ]);
        }

        Schema::table('bank_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('wallet_transaction_id')->change();
            $table->foreign('wallet_transaction_id')->references('id')->on('wallet_transactions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            //
        });
    }
}
