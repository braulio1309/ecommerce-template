<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AdminWalletController extends Controller
{
    public function __construct()
    {
        // Staff Permission Check - you can customize this based on your permission system
        // $this->middleware(['permission:view_wallets'])->only('index', 'show');
        // $this->middleware(['permission:recharge_wallet'])->only('recharge');
    }

    /**
     * Display a listing of all user wallets.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sort_search = null;
        $users = User::where('user_type', 'customer')
            ->with(['wallets' => function($query) {
                $query->latest()->limit(1);
            }])
            ->orderBy('created_at', 'desc');

        if ($request->has('search')) {
            $sort_search = $request->search;
            $users->where(function ($q) use ($sort_search) {
                $q->where('name', 'like', '%' . $sort_search . '%')
                    ->orWhere('email', 'like', '%' . $sort_search . '%');
            });
        }

        $users = $users->paginate(15);

        // Calculate total balance for each user
        foreach ($users as $user) {
            $user->total_balance = $user->balance ?? 0;
        }

        return view('backend.wallets.index', compact('users', 'sort_search'));
    }

    /**
     * Display the specified wallet details with transaction history.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::with(['wallets' => function($query) {
            $query->orderBy('created_at', 'desc');
        }])->findOrFail($id);

        // Get all wallet IDs for this user
        $walletIds = $user->wallets->pluck('id');

        // Get all transactions for these wallets
        $transactions = WalletTransaction::whereIn('wallet_id', $walletIds)
            ->with(['wallet', 'adminUser'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('backend.wallets.show', compact('user', 'transactions'));
    }

    /**
     * Recharge a user's wallet.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function recharge(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500'
        ]);

        $user = User::findOrFail($id);
        $amount = $request->amount;

        try {
            DB::beginTransaction();

            // Create wallet entry
            $wallet = new Wallet();
            $wallet->user_id = $user->id;
            $wallet->amount = $amount;
            $wallet->payment_method = 'admin_recharge';
            $wallet->payment_details = 'Recharged by admin';
            $wallet->save();

            // Create transaction record
            $transaction = new WalletTransaction();
            $transaction->wallet_id = $wallet->id;
            $transaction->amount = $amount;
            $transaction->transaction_type = 'credit';
            $transaction->admin_user_id = Auth::id();
            $transaction->description = $request->description ?? 'Wallet recharged by admin';
            $transaction->save();

            // Update user balance
            $user->balance = ($user->balance ?? 0) + $amount;
            $user->save();

            DB::commit();

            flash(translate('Wallet recharged successfully'))->success();
            return redirect()->route('admin.wallets.show', $id);

        } catch (\Exception $e) {
            DB::rollBack();
            flash(translate('An error occurred while recharging wallet'))->error();
            return back();
        }
    }
}
