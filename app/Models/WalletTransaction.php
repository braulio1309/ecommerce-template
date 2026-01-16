<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\PreventDemoModeChanges;

class WalletTransaction extends Model
{
    use PreventDemoModeChanges;

    protected $fillable = [
        'wallet_id',
        'amount',
        'transaction_type',
        'admin_user_id',
        'description'
    ];

    /**
     * Get the wallet that owns the transaction.
     */
    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * Get the admin user who performed the transaction.
     */
    public function adminUser()
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }
}
