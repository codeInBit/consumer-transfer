<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\TransferRecipient;
use App\Models\BankTransaction;

class ChangeTransferRecipientsTablePrimaryKeyToIncrementalIdAndReflectThisChangeAccrossAllForiegnTables extends Migration
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
            $table->dropForeign(['transfer_recipient_id']);
        });

        //Drop primary key contraint on id then rename id field to uuid
        Schema::table('transfer_recipients', function (Blueprint $table) {
            $table->dropPrimary('transfer_recipients_id_primary');
            $table->renameColumn('id', 'uuid');
        });

        //Make uuid field nullable and create increamental id field.
        Schema::table('transfer_recipients', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->nullable()->change();
            $table->bigIncrements('id')->after('uuid');
        });

        //Change the value accross all table where id is a foriegn key 
        $transferRecipients = TransferRecipient::all();
        foreach ($transferRecipients as $key => $transferRecipient) {
            BankTransaction::where('transfer_recipient_id', $transferRecipient->uuid)->update([
                'transfer_recipient_id' => $transferRecipient->id,
            ]);
        }

        Schema::table('bank_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('transfer_recipient_id')->change();
            $table->foreign('transfer_recipient_id')->references('id')->on('transfer_recipients');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transfer_recipients', function (Blueprint $table) {
            //
        });
    }
}
