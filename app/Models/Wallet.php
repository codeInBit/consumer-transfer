<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wallet extends Model
{
    use SoftDeletes;
    use HasFactory;

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
     * @param int $value
     * @return void
     */
    public function setBalanceAttribute($value): void
    {
        $this->attributes['balance'] = (int) $value * 100;
    }

    /**
     * Get wallet's balance.
     *
     * @param int $value
     * @return int
     */
    public function getBalanceAttribute($value): int
    {
        return (int) $value / 100;
    }
}
