<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use App\Traits\Uuid;

class User extends Authenticatable
{
    use HasApiTokens;
    use SoftDeletes;
    use HasFactory;
    use Notifiable;
    use Uuid;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'password'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function wallet()
    {
        return $this->hasOne('App\Models\Wallet');
    }

    /**
     * Get all of the wallet transactions for the user.
     */
    public function walletTransactions()
    {
        return $this->hasManyThrough('App\Models\WalletTransaction', 'App\Models\Wallet');
    }

    public function transferRecipients()
    {
        return $this->hasMany('App\Models\TransferRecipient');
    }
}
