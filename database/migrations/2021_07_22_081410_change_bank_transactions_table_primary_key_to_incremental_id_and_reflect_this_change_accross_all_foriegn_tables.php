<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeBankTransactionsTablePrimaryKeyToIncrementalIdAndReflectThisChangeAccrossAllForiegnTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bank_transactions', function (Blueprint $table) {
            $table->dropPrimary('bank_transactions_id_primary');
            $table->renameColumn('id', 'uuid');
        });
        
        Schema::table('bank_transactions', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->nullable()->change();
            $table->bigIncrements('id')->after('uuid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bank_transactions', function (Blueprint $table) {
            //
        });
    }
}
