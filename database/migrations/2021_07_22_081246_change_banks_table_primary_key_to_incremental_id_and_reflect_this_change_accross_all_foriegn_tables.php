<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeBanksTablePrimaryKeyToIncrementalIdAndReflectThisChangeAccrossAllForiegnTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('banks', function (Blueprint $table) {
            $table->dropPrimary('banks_id_primary');
            $table->renameColumn('id', 'uuid');
        });

        Schema::table('banks', function (Blueprint $table) {
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
        Schema::table('banks', function (Blueprint $table) {
            //
        });
    }
}
