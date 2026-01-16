<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\PreventDemoModeChanges;

class Wallet extends Model
{
    use PreventDemoModeChanges;

    protected $fillable = [
        'user_id',
        'amount',
        'payment_method',
        'payment_details'
    ];

    public function user(){
    	return $this->belongsTo(User::class);
    }

    public function transactions(){
    	return $this->hasMany(WalletTransaction::class)->orderBy('created_at', 'desc');
    }
}
