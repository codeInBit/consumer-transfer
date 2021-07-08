<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Helpers\RandomGenerator;
use DB;

class WalletService
{

    /**
     * Debit authenticated user wallet of a specified amount.
     */
    public function debit($userId, $amount)
    {
        $wallet = Wallet::where('user_id', $userId)->first();

        if (!$wallet) {
            return [
                'status' => false,
                'message' => 'Account does not exist'
            ];
        }

        $prevBalance = $wallet->balance;
        if ($prevBalance < $amount) {
            return [
                'status' => false,
                'message' => 'Insufficient balance'
            ];
        }

        $walletTransaction = null;

        DB::transaction(function () use ($wallet, $prevBalance, $amount, &$walletTransaction) {
            $currentBalance = $prevBalance - $amount;
            $wallet->update([
                'balance' => $currentBalance
            ]);

            $walletTransaction = $wallet->transactions()->create([
                'amount' => $amount,
                'prev_balance' => $prevBalance,
                'current_balance' => $currentBalance,
                'reference' => RandomGenerator::alphaNumericCode(8),
                'trx_type' => WalletTransaction::TRX_TYPE['debit'],
                'purpose' => WalletTransaction::PURPOSE['transfer'],
                'transaction_date' => now(),
                'status' => WalletTransaction::STATUS['success'],
            ]);
        });

        return [
            'status' => true,
            'message' => 'Transfer successful',
            'data' => $walletTransaction,
        ];
    }
}
