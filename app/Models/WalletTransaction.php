<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WalletTransaction extends Model
{
    use SoftDeletes;
    use HasFactory;
    use Uuid;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['wallet_id', 'amount', 'prev_balance', 'current_balance', 'reference',
        'trx_type', 'purpose', 'transaction_date'
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
     * @param float $value
     * @return void
     */
    public function setAmountAttribute($value): void
    {
        $this->attributes['amount'] = (float) $value * 100;
    }

    /**
     * Get transaction's amount.
     *
     * @param float $value
     * @return double
     */
    public function getAmountAttribute($value): float
    {
        return (float) $value / 100;
    }

    /**
     * Set transaction's pre_balance.
     *
     * @param float $value
     * @return void
     */
    public function setPrevBalanceAttribute($value): void
    {
        $this->attributes['prev_balance'] = (float) $value * 100;
    }

    /**
     * Get transaction's pre_balance.
     *
     * @param float $value
     * @return double
     */
    public function getPrevBalanceAttribute($value): float
    {
        return (float) $value / 100;
    }

    /**
     * Set transaction's current_balance.
     *
     * @param float $value
     * @return void
     */
    public function setCurrentBalanceAttribute($value): void
    {
        $this->attributes['current_balance'] = (float) $value * 100;
    }

    /**
     * Get transaction's current_balance.
     *
     * @param float $value
     * @return double
     */
    public function getCurrentBalanceAttribute($value): float
    {
        return (float) $value / 100;
    }
}
