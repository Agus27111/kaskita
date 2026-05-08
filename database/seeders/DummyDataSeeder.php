<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Family;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'test@example.com')->first();

        if (!$user) {
            return;
        }

        DB::transaction(function () use ($user) {
            // 1. Ensure Family exists
            $family = $user->family;
            if (!$family) {
                $family = Family::create(['name' => 'Keluarga Agus']);
                $user->update([
                    'family_id' => $family->id,
                    'role' => 'admin_keluarga'
                ]);
            }

            // 2. Create Wallets
            $wallets = [
                [
                    'name' => 'Dompet Tunai',
                    'type' => 'cash',
                    'balance' => 500000,
                    'color' => '#10b981',
                    'icon' => 'wallet',
                ],
                [
                    'name' => 'Bank BCA',
                    'type' => 'bank',
                    'balance' => 12500000,
                    'color' => '#3b82f6',
                    'icon' => 'credit-card',
                ],
                [
                    'name' => 'Gopay / OVO',
                    'type' => 'ewallet',
                    'balance' => 750000,
                    'color' => '#8b5cf6',
                    'icon' => 'smartphone',
                ],
            ];

            $createdWallets = [];
            foreach ($wallets as $w) {
                $createdWallets[] = Wallet::updateOrCreate(
                    ['family_id' => $family->id, 'name' => $w['name']],
                    $w
                );
            }

            // Set default wallet
            $user->update(['default_wallet_id' => $createdWallets[1]->id]);

            // 3. Create Transactions
            $categories = Category::all();
            $incomeCat = $categories->where('type', 'income')->first();
            $foodCat = $categories->where('name', 'Makan & Minum')->first();
            $transportCat = $categories->where('name', 'Transport')->first();
            $billCat = $categories->where('name', 'Tagihan')->first();

            $transactions = [
                [
                    'type' => 'income',
                    'amount' => 15000000,
                    'category_id' => $incomeCat->id,
                    'wallet_id' => $createdWallets[1]->id,
                    'note' => 'Gaji Bulanan',
                    'date' => now()->startOfMonth(),
                ],
                [
                    'type' => 'expense',
                    'amount' => 50000,
                    'category_id' => $foodCat->id,
                    'wallet_id' => $createdWallets[0]->id,
                    'note' => 'Nasi Padang',
                    'date' => now()->subDays(2),
                ],
                [
                    'type' => 'expense',
                    'amount' => 150000,
                    'category_id' => $transportCat->id,
                    'wallet_id' => $createdWallets[2]->id,
                    'note' => 'Isi Bensin & Tol',
                    'date' => now()->subDays(1),
                ],
                [
                    'type' => 'expense',
                    'amount' => 850000,
                    'category_id' => $billCat->id,
                    'wallet_id' => $createdWallets[1]->id,
                    'note' => 'Listrik & Internet',
                    'date' => now()->subHours(5),
                ],
                [
                    'type' => 'expense',
                    'amount' => 35000,
                    'category_id' => $foodCat->id,
                    'wallet_id' => $createdWallets[2]->id,
                    'note' => 'Kopi Sore',
                    'date' => now(),
                ],
            ];

            foreach ($transactions as $t) {
                Transaction::create(array_merge($t, [
                    'family_id' => $family->id,
                    'user_id' => $user->id,
                ]));
            }
        });
    }
}
