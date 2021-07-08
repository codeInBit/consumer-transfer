<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\BankTransferRequest;
use App\Services\WalletService;
use App\Models\BankTransaction;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Auth;
use App\Services\BankTransferService;
use Symfony\Component\HttpFoundation\Response;
use App\Services\ThirdPartyService\Payment\Paystack\Transfer;
use App\Services\ThirdPartyService\Payment\Paystack\IdentityVerification;
use Exception;
use DB;

class TransferController extends Controller
{
    protected $paystackTransfer;
    protected $walletService;
    protected $identityVerification;
    protected $bankTransferService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        Transfer $paystackTransfer,
        WalletService $walletService,
        BankTransferService $bankTransferService,
        IdentityVerification $identityVerification
    ) {
        $this->walletService = $walletService;
        $this->paystackTransfer = $paystackTransfer;
        $this->bankTransferService = $bankTransferService;
        $this->identityVerification = $identityVerification;
    }

    /**
     * Paginated list of a user's transfers.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    protected function getTransfers(Request $request)
    {
        $user = Auth::user();
        /**
         * I need the resulting $query to be an instance of Illuminate\\Database\\Eloquent\\Builder
         * or Illuminate\\Database\\Query\\Builder, that was why I didn't use this.
         *
         * $query = $user->walletTransactions()->with('bankTransaction');
         */
        $query = WalletTransaction::where('wallet_id', $user->wallet->id)
            ->with('bankTransaction', 'bankTransaction.transferRecipient');

        return $this->datatableResponse(
            $query,
            "App\Http\Resources\WalletTransactionResource",
            [
                'search' => function ($query, $searchString) {
                    $query->where('amount', 'like', "%{$searchString}%")
                        ->orwhere('reference', 'like', "%{$searchString}%")
                        ->orwhere('trx_type', 'like', "%{$searchString}%")
                        ->orwhere('purpose', 'like', "%{$searchString}%")
                        ->orwhere('transaction_date', 'like', "%{$searchString}%")
                        ->orwhereHas('bankTransaction', function ($bankTransaction) use ($searchString) {
                            $bankTransaction->where('provider', 'like', "%{$searchString}%");
                        })
                        ->orwhereHas('bankTransaction', function ($bankTransaction) use ($searchString) {
                            $bankTransaction->where('reference', 'like', "%{$searchString}%");
                        })
                        ->orwhereHas('bankTransaction', function ($bankTransaction) use ($searchString) {
                            $bankTransaction->where('transfer_code', 'like', "%{$searchString}%");
                        })
                        ->orwhereHas(
                            'bankTransaction.transferRecipient',
                            function ($bankTransaction) use ($searchString) {
                                $bankTransaction->where('name', 'like', "%{$searchString}%");
                            }
                        )
                        ->orwhereHas(
                            'bankTransaction.transferRecipient',
                            function ($bankTransaction) use ($searchString) {
                                $bankTransaction->where('account_number', 'like', "%{$searchString}%");
                            }
                        );
                }
            ]
        );
    }

    /**
     * Transfer money to a bank account
     * First, verify if account details is correct.
     * Second, Fetch or generate a recipient code from paystack.
     * Third, Perform the transfer proper.
     *
     * @param  BankTransferRequest  $request
     * @return \Illuminate\Http\Response
     */
    protected function transfer(BankTransferRequest $request)
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();
            $accountDetails = $this->identityVerification->resolveAccountNumber(
                $request->account_number,
                $request->bank_code,
            );
            if ($accountDetails['status'] != true) {
                return $this->errorResponse(null, $accountDetails['message']);
            }

            $transferRecipient = $this->bankTransferService->getTransferRecipient(
                $user->id,
                $request->account_number,
                $request->bank_code,
                $request->name,
            );
            if ($transferRecipient['status'] != true) {
                return $this->errorResponse(null, $transferRecipient['message']);
            }

            $walletTransaction = $this->walletService->debit($user->id, $request->amount);
            if ($walletTransaction['status'] != true) {
                return $this->errorResponse(null, $walletTransaction['message']);
            }

            $transfer = $this->paystackTransfer->initiateSingleTransfer(
                $request->amount * 100,
                $transferRecipient['data']['recipient_code'],
                $request->input('narration', 'Transfer'),
            );
            if ($transfer['status'] != true) {
                return $this->errorResponse(null, $transfer['message']);
            }

            $transferRecipient['data']->bankTransactions()->create([
                'wallet_transaction_id' => $walletTransaction['data']['id'],
                'amount' => (float) $transfer['data']['amount'] / 100,
                'provider' => BankTransaction::PROVIDER['paystack'],
                'reference' => $transfer['data']['reference'],
                'transfer_code' => $transfer['data']['transfer_code'],
                'status' => $transfer['data']['status'],
                'narration' => $request->input('narration', 'Transfer'),
            ]);

            DB::commit();

            return $this->successResponse(null, "Transfer successful", Response::HTTP_OK);
        } catch (Exception $e) {
            DB::rollback();
            return $this->fatalErrorResponse($e);
        }
    }
}
