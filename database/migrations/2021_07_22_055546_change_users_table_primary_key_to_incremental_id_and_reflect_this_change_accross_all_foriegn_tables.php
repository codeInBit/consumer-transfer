<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Wallet;
use App\Models\TransferRecipient;

class ChangeUsersTablePrimaryKeyToIncrementalIdAndReflectThisChangeAccrossAllForiegnTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Drop forign keys
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        //Drop primary key contraint on id then rename id field to uuid
        Schema::table('users', function (Blueprint $table) {
            $table->dropPrimary('users_id_primary');
            $table->renameColumn('id', 'uuid');
        });

        //Make uuid field nullable and create increamental id field.
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->nullable()->change();
            $table->bigIncrements('id')->after('uuid');
        });

        //Change the value accross all table where id is a foriegn key 
        $users = User::all();
        foreach ($users as $key => $user) {
            DB::table('oauth_auth_codes')->where('user_id', $user->uuid)->update(['user_id' => $user->id]);
            DB::table('oauth_access_tokens')->where('user_id', $user->uuid)->update(['user_id' => $user->id]);
            DB::table('oauth_clients')->where('user_id', $user->uuid)->update(['user_id' => $user->id]);
            Wallet::where('user_id', $user->uuid)->update([
                'user_id' => $user->id,
            ]);
            TransferRecipient::where('user_id', $user->uuid)->update([
                'user_id' => $user->id,
            ]);
        }

        Schema::table('oauth_auth_codes', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->change();
        });

        Schema::table('oauth_access_tokens', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->change();
        });

        Schema::table('oauth_clients', function (Blueprint $table) {
            $table->string('user_id')->change();
        });

        Schema::table('wallets', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->change();
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
}
