<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankTransaction extends Model
{
    use SoftDeletes;
    use HasFactory;
    use Uuid;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['wallet_transaction_id', 'transfer_recipient_id', 'amount', 'provider', 'reference',
        'transfer_code', 'status'
    ];

    /**
     * Provider Values
     *
     * @var array
     */
    public const PROVIDER = [
        'paystack' => 'paystack',
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

    public function walletTransaction()
    {
        return $this->belongsTo('App\Models\WalletTransaction');
    }

    public function transferRecipient()
    {
        return $this->belongsTo('App\Models\TransferRecipient');
    }

    /**
     * Set bank transaction's amount.
     *
     * @param float $value
     * @return void
     */
    public function setAmountAttribute($value): void
    {
        $this->attributes['amount'] = (float) $value * 100;
    }

    /**
     * Get bank transaction's amount.
     *
     * @param float $value
     * @return double
     */
    public function getAmountAttribute($value): float
    {
        return (float) $value / 100;
    }
}
