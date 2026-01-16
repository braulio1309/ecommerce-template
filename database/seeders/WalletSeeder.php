<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class WalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Find or create a test customer
        $customer = User::where('user_type', 'customer')->first();
        
        if (!$customer) {
            $customer = User::create([
                'name' => 'Test Customer',
                'email' => 'customer@test.com',
                'password' => bcrypt('password'),
                'user_type' => 'customer',
                'email_verified_at' => now(),
            ]);
        }

        // Find or create a test admin
        $admin = User::where('user_type', 'admin')->first();
        
        if (!$admin) {
            $admin = User::create([
                'name' => 'Test Admin',
                'email' => 'admin@test.com',
                'password' => bcrypt('password'),
                'user_type' => 'admin',
                'email_verified_at' => now(),
            ]);
        }

        // Create sample wallet entries
        $wallet1 = Wallet::create([
            'user_id' => $customer->id,
            'amount' => 100.00,
            'payment_method' => 'admin_recharge',
            'payment_details' => 'Test recharge 1',
        ]);

        // Create corresponding transaction
        WalletTransaction::create([
            'wallet_id' => $wallet1->id,
            'amount' => 100.00,
            'transaction_type' => 'credit',
            'admin_user_id' => $admin->id,
            'description' => 'Initial test recharge',
        ]);

        $wallet2 = Wallet::create([
            'user_id' => $customer->id,
            'amount' => 50.00,
            'payment_method' => 'admin_recharge',
            'payment_details' => 'Test recharge 2',
        ]);

        WalletTransaction::create([
            'wallet_id' => $wallet2->id,
            'amount' => 50.00,
            'transaction_type' => 'credit',
            'admin_user_id' => $admin->id,
            'description' => 'Second test recharge',
        ]);

        // Update customer balance
        $customer->balance = 150.00;
        $customer->save();

        $this->command->info('Wallet seeder completed successfully!');
    }
}
