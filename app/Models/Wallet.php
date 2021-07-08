<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wallet extends Model
{
    use SoftDeletes;
    use HasFactory;
    use Uuid;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'balance'];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function transactions()
    {
        return $this->hasMany('App\Models\WalletTransaction');
    }

    /**
     * Set wallet's balance.
     *
     * @param float $value
     * @return void
     */
    public function setBalanceAttribute($value): void
    {
        $this->attributes['balance'] = (float) $value * 100;
    }

    /**
     * Get wallet's balance.
     *
     * @param float $value
     * @return double
     */
    public function getBalanceAttribute($value): float
    {
        return (float) $value / 100;
    }
}
