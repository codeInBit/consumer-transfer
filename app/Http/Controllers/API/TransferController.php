<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\BankTransferRequest;
use App\Models\BankTransaction;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Auth;
use App\Services\BankTransferService;
use Symfony\Component\HttpFoundation\Response;
use Exception;
use DB;

class TransferController extends Controller
{
    protected $bankTransferService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        BankTransferService $bankTransferService
    ) {
        $this->bankTransferService = $bankTransferService;
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

            $transferRecipient = $this->bankTransferService->transferFromWalletToBankAccount(
                $user->id,
                $request->account_number,
                $request->bank_code,
                $request->name,
                $request->amount,
                $request->input('narration', 'Bank Transfer'),
            );
            if ($transferRecipient['status'] != true) {
                return $this->errorResponse(null, $transferRecipient['message']);
            }

            DB::commit();

            return $this->successResponse(null, "Transfer successful", Response::HTTP_OK);
        } catch (Exception $e) {
            DB::rollback();
            return $this->fatalErrorResponse($e);
        }
    }
}
