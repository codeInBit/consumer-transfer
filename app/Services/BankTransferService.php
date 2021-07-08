<?php

namespace App\Services;

use App\Models\TransferRecipient;
use App\Services\ThirdPartyService\Payment\Paystack\Transfer;

class BankTransferService
{
    protected $paystackTransfer;

    /**
     * Create a new class instance.
     *
     * @return void
     */
    public function __construct(
        Transfer $paystackTransfer
    ) {
        $this->paystackTransfer = $paystackTransfer;
    }

    /**
     * Fetch paystack transfer recipient from DB or create if it doesnt eixst
     * based on provided account number and bank code
     */
    public function getTransferRecipient($userId, $accountNumber, $bankCode, $name)
    {
        $transferRecipient = TransferRecipient::where('user_id', $userId)
            ->where('account_number', $accountNumber)
            ->where('bank_code', $bankCode)
            ->first();

        if (!$transferRecipient) {
            $transferRecipient = $this->paystackTransfer->createTransferRecipient(
                $name,
                $accountNumber,
                $bankCode,
            );

            if ($transferRecipient['status'] != true) {
                return [
                    'status' => false,
                    'message' => $transferRecipient['message'],
                ];
            }

            $transferRecipient = TransferRecipient::create([
                'recipient_code' => $transferRecipient['data']['recipient_code'],
                'account_number' => $transferRecipient['data']['details']['account_number'],
                'type' => $transferRecipient['data']['type'],
                'bank_code' => $transferRecipient['data']['details']['bank_code'],
                'name' => $name,
                'user_id' => $userId,
            ]);
        }

        return [
            'status' => true,
            'message' => 'Transfer recipient',
            'data' => $transferRecipient,
        ];
    }
}
