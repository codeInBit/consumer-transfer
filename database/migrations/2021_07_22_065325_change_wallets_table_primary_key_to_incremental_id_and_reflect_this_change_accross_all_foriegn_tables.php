<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Wallet;
use App\Models\WalletTransaction;

class ChangeWalletsTablePrimaryKeyToIncrementalIdAndReflectThisChangeAccrossAllForiegnTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Drop forign keys
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropForeign(['wallet_id']);
        });

        //Drop primary key contraint on id then rename id field to uuid
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropPrimary('wallets_id_primary');
            $table->renameColumn('id', 'uuid');
        });

        //Make uuid field nullable and create increamental id field.
        Schema::table('wallets', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->nullable()->change();
            $table->bigIncrements('id')->after('uuid');
        });

        //Change the value accross all table where id is a foriegn key 
        $wallets = Wallet::all();
        foreach ($wallets as $key => $wallet) {
            WalletTransaction::where('wallet_id', $wallet->uuid)->update([
                'wallet_id' => $wallet->id,
            ]);
        }

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('wallet_id')->change();
            $table->foreign('wallet_id')->references('id')->on('wallets');
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
