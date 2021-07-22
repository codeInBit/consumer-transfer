<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WalletTransaction extends Model
{
    use SoftDeletes;
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['wallet_id', 'amount', 'prev_balance', 'current_balance', 'reference',
        'trx_type', 'purpose', 'transaction_date', 'status',
    ];

    /**
     * Transaction Type Values
     *
     * @var array
     */
    public const TRX_TYPE = [
        'credit' => 'credit',
        'debit' => 'debit',
    ];

    /**
     * Purpose Values
     *
     * @var array
     */
    public const PURPOSE = [
        'deposit' => 'deposit',
        'transfer' => 'transfer',
        'reversal' => 'reversal',
    ];

    /**
     * Status Values
     *
     * @var array
     */
    public const STATUS = [
        'pending' => 'pending',
        'success' => 'success',
        'failed' => 'failed',
    ];

    public function wallet()
    {
        return $this->belongsTo('App\Models\Wallet');
    }

    public function bankTransaction()
    {
        return $this->hasOne('App\Models\BankTransaction');
    }

    /**
     * Set transaction's amount.
     *
     * @param int $value
     * @return void
     */
    public function setAmountAttribute($value): void
    {
        $this->attributes['amount'] = (int) $value * 100;
    }

    /**
     * Get transaction's amount.
     *
     * @param int $value
     * @return int
     */
    public function getAmountAttribute($value): int
    {
        return (int) $value / 100;
    }

    /**
     * Set transaction's pre_balance.
     *
     * @param int $value
     * @return void
     */
    public function setPrevBalanceAttribute($value): void
    {
        $this->attributes['prev_balance'] = (int) $value * 100;
    }

    /**
     * Get transaction's pre_balance.
     *
     * @param int $value
     * @return int
     */
    public function getPrevBalanceAttribute($value): int
    {
        return (int) $value / 100;
    }

    /**
     * Set transaction's current_balance.
     *
     * @param int $value
     * @return void
     */
    public function setCurrentBalanceAttribute($value): void
    {
        $this->attributes['current_balance'] = (int) $value * 100;
    }

    /**
     * Get transaction's current_balance.
     *
     * @param int $value
     * @return int
     */
    public function getCurrentBalanceAttribute($value): int
    {
        return (int) $value / 100;
    }
}
