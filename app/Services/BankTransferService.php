<?php

namespace App\Services;

use App\Models\BankTransaction;
use App\Models\TransferRecipient;
use App\Services\WalletService;
use App\Services\ThirdPartyService\Payment\Paystack\Transfer;
use App\Services\ThirdPartyService\Payment\Paystack\IdentityVerification;

class BankTransferService
{
    protected $walletService;
    protected $paystackTransfer;
    protected $identityVerification;

    /**
     * Create a new class instance.
     *
     * @return void
     */
    public function __construct(
        WalletService $walletService,
        Transfer $paystackTransfer,
        IdentityVerification $identityVerification
    ) {
        $this->walletService = $walletService;
        $this->paystackTransfer = $paystackTransfer;
        $this->identityVerification = $identityVerification;
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
            $accountDetails = $this->identityVerification->resolveAccountNumber(
                $accountNumber,
                $bankCode,
            );
            if ($accountDetails['status'] != true) {
                return [
                    'status' => false,
                    'message' => $accountDetails['message'],
                ];
            }

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

    public function transferFromWalletToBankAccount($userId, $accountNumber, $bankCode, $name, $amount, $narration)
    {
        $transferRecipient = $this->getTransferRecipient($userId, $accountNumber, $bankCode, $name);

        $walletTransaction = $this->walletService->debit($userId, $amount);
        if ($walletTransaction['status'] != true) {
            return [
                'status' => false,
                'message' => $walletTransaction['message'],
            ];
        }

        $transfer = $this->paystackTransfer->initiateSingleTransfer(
            $amount * 100,
            $transferRecipient['data']['recipient_code'],
            $narration,
        );
        if ($transfer['status'] != true) {
            return [
                'status' => false,
                'message' => $transfer['message'],
            ];
        }

        $transferRecipient['data']->bankTransactions()->create([
            'wallet_transaction_id' => $walletTransaction['data']['id'],
            'amount' => (int) $transfer['data']['amount'] / 100,
            'provider' => BankTransaction::PROVIDER['paystack'],
            'reference' => $transfer['data']['reference'],
            'transfer_code' => $transfer['data']['transfer_code'],
            'status' => $transfer['data']['status'],
            'narration' => $narration,
        ]);

        return [
            'status' => true,
            'message' => 'Transfer successful',
        ];
    }
}
