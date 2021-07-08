<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransferRecipient extends Model
{
    use SoftDeletes;
    use HasFactory;
    use Uuid;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['recipient_code', 'account_number', 'type', 'bank_code', 'name', 'user_id'];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function bankTransactions()
    {
        return $this->hasMany('App\Models\BankTransaction');
    }
}
