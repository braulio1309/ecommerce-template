# Wallet Recharge Module Documentation

## Overview
The Wallet Recharge module provides comprehensive wallet management functionality for the Laravel ecommerce application, allowing administrators to manage customer wallets, view transaction history, and perform recharges.

## Features

### 1. Database Structure

#### Wallets Table
- `id`: Primary key
- `user_id`: Foreign key to users table
- `amount`: Recharge amount (decimal 20,2)
- `payment_method`: Payment method used
- `payment_details`: Additional payment details
- `timestamps`: Created and updated timestamps

#### Wallet Transactions Table
- `id`: Primary key
- `wallet_id`: Foreign key to wallets table
- `amount`: Transaction amount (decimal 20,2)
- `transaction_type`: Enum ('credit', 'debit')
- `admin_user_id`: Foreign key to users table (admin who performed the transaction)
- `description`: Optional description of the transaction
- `timestamps`: Created and updated timestamps

### 2. Models

#### Wallet Model (`App\Models\Wallet`)
**Relationships:**
- `user()`: BelongsTo User - The customer who owns the wallet
- `transactions()`: HasMany WalletTransaction - Transaction history for this wallet

**Fillable Fields:**
- `user_id`, `amount`, `payment_method`, `payment_details`

#### WalletTransaction Model (`App\Models\WalletTransaction`)
**Relationships:**
- `wallet()`: BelongsTo Wallet - The wallet this transaction belongs to
- `adminUser()`: BelongsTo User - The admin who performed the transaction

**Fillable Fields:**
- `wallet_id`, `amount`, `transaction_type`, `admin_user_id`, `description`

### 3. Controller

#### AdminWalletController (`App\Http\Controllers\AdminWalletController`)

**Methods:**

##### `index(Request $request)`
- Lists all customer wallets with search functionality
- Displays customer name, email, phone, current balance, and total recharges
- Paginated results (15 per page)
- Search by customer name or email

**Route:** `GET /admin/wallets`
**Permission:** `view_wallets`

##### `show($id)`
- Displays detailed wallet information for a specific customer
- Shows customer information (name, email, phone, current balance)
- Lists all wallet transactions with pagination (20 per page)
- Shows complete recharge history

**Route:** `GET /admin/wallets/{id}`
**Permission:** `view_wallets`

##### `recharge(Request $request, $id)`
- Processes wallet recharge for a customer
- Creates wallet entry and transaction record
- Updates customer balance atomically using database transactions
- Validates amount (required, numeric, minimum 0.01)
- Logs the admin user who performed the recharge

**Route:** `POST /admin/wallets/{id}/recharge`
**Permission:** `recharge_wallet`

**Request Parameters:**
- `amount` (required, numeric, min: 0.01): The amount to recharge
- `description` (optional, string, max: 500): Description for the transaction

### 4. Routes

All routes are protected by the `auth` and `admin` middleware and require appropriate permissions.

```php
Route::controller(AdminWalletController::class)->group(function () {
    Route::get('/wallets', 'index')->name('admin.wallets.index');
    Route::get('/wallets/{id}', 'show')->name('admin.wallets.show');
    Route::post('/wallets/{id}/recharge', 'recharge')->name('admin.wallets.recharge');
});
```

### 5. Views

#### Wallet List (`resources/views/backend/wallets/index.blade.php`)
- Table view of all customer wallets
- Search functionality by name or email
- Quick action buttons:
  - Eye icon: View wallet details
  - Plus icon: Recharge wallet
- Recharge modal with amount and description fields

#### Wallet Details (`resources/views/backend/wallets/show.blade.php`)
- Customer information card with:
  - Name, email, phone
  - Current balance (highlighted)
  - Recharge button
- Transaction History table showing:
  - Date and time of transaction
  - Amount (with color coding for credit/debit)
  - Transaction type
  - Payment method
  - Admin who performed the action
  - Description
- Wallet Recharge History table showing:
  - Date and time
  - Amount
  - Payment method
  - Status (Approved/Pending/Rejected/Completed)

### 6. Permissions

The module uses two permissions that should be configured in your permission system:

1. **`view_wallets`**: Required to view wallet list and details
2. **`recharge_wallet`**: Required to perform wallet recharges

Make sure these permissions are assigned to the appropriate admin roles.

## Installation & Setup

### 1. Run Migrations

```bash
php artisan migrate
```

This will create the `wallets` and `wallet_transactions` tables.

### 2. Set Up Permissions

Add the following permissions to your permission system:
- `view_wallets`
- `recharge_wallet`

Assign these permissions to admin roles that should have access to wallet management.

### 3. (Optional) Run Seeder

For testing purposes, you can run the wallet seeder:

```bash
php artisan db:seed --class=WalletSeeder
```

This creates:
- Test customer and admin users (if they don't exist)
- Sample wallet entries
- Sample transactions

## Usage

### For Administrators

1. **View All Wallets:**
   - Navigate to `/admin/wallets`
   - Use the search bar to find specific customers
   - Click the eye icon to view wallet details
   - Click the plus icon to recharge a wallet

2. **Recharge a Wallet:**
   - Click the plus icon next to a customer or use the "Recharge Wallet" button on the details page
   - Enter the recharge amount (minimum 0.01)
   - Optionally add a description
   - Click "Recharge" to process

3. **View Transaction History:**
   - Navigate to `/admin/wallets/{user_id}`
   - View all transactions in chronological order
   - See which admin performed each recharge
   - Track the complete recharge history

## Security Features

1. **Authentication Required:** All routes require admin authentication
2. **Permission-Based Access:** Controlled by Spatie's permission system
3. **Database Transactions:** Atomic updates ensure data consistency
4. **Input Validation:** Amount validation prevents invalid entries
5. **Audit Trail:** All recharges are logged with admin user information

## Technical Notes

### Transaction Atomicity
The recharge process uses database transactions to ensure atomicity:
```php
DB::beginTransaction();
try {
    // Create wallet entry
    // Create transaction record
    // Update user balance
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
}
```

### Performance Optimization
- Eager loading of relationships to prevent N+1 queries
- Pagination for large datasets
- Efficient queries with proper indexing (foreign keys)

## Extending the Module

### Adding New Transaction Types

To add new transaction types (e.g., 'refund', 'purchase'), update the enum in the migration:

```php
$table->enum('transaction_type', ['credit', 'debit', 'refund', 'purchase']);
```

### Custom Payment Methods

The `payment_method` field accepts any string value, allowing for custom payment method tracking.

### Additional Validations

Add custom validation rules in the `recharge()` method as needed:

```php
$request->validate([
    'amount' => 'required|numeric|min:0.01|max:10000',
    'description' => 'required|string|min:10|max:500'
]);
```

## Troubleshooting

### Permission Errors
If you get permission errors, ensure:
1. The permissions exist in your database
2. The admin user has been assigned the appropriate permissions
3. The middleware is properly configured

### Migration Issues
If migrations fail:
1. Check that the `users` table exists
2. Ensure foreign key constraints are supported (InnoDB engine)
3. Verify database connection settings

### View Not Found
If views are not found:
1. Clear the view cache: `php artisan view:clear`
2. Ensure files are in the correct location: `resources/views/backend/wallets/`

## Support

For issues or questions, please refer to the Laravel documentation or contact the development team.
