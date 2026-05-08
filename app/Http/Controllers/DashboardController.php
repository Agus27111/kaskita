<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $family = $user->family;

        if (!$family) {
            return redirect()->route('family.setup');
        }

        // Get total balance
        $totalBalance = Wallet::where('family_id', $family->id)
            ->where('is_active', true)
            ->sum('balance');

        // Get monthly income & expense
        $monthlyIncome = Transaction::where('family_id', $family->id)
            ->where('type', 'income')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');

        $monthlyExpense = Transaction::where('family_id', $family->id)
            ->where('type', 'expense')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');

        // Recent transactions
        $recentTransactions = Transaction::with(['category', 'wallet', 'user'])
            ->where('family_id', $family->id)
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Wallets
        $wallets = Wallet::where('family_id', $family->id)
            ->where('is_active', true)
            ->get();

        // Budget summary - top 4 budgets for current month
        $budgets = Budget::with('category')
            ->where('family_id', $family->id)
            ->where('period', 'monthly')
            ->where('month', now()->month)
            ->where('year', now()->year)
            ->limit(4)
            ->get()
            ->map(function ($b) {
                return [
                    'id' => $b->id,
                    'category_name' => $b->category?->name ?? 'Lainnya',
                    'category_color' => $b->category?->color ?? '#71717a',
                    'amount' => (float) $b->amount,
                    'spent' => $b->spent,
                    'percentage' => $b->percentage,
                    'status' => $b->status,
                ];
            });

        // Categories
        $categories = \App\Models\Category::where('family_id', $family->id)->get();

        // Wallet Types
        $walletTypes = \App\Models\WalletType::where('family_id', $family->id)->get();
        if ($walletTypes->isEmpty()) {
            $defaults = [
                ['name' => 'Rekening Bank', 'icon' => '🏦'],
                ['name' => 'Uang Tunai (Cash)', 'icon' => '💵'],
                ['name' => 'E-Wallet', 'icon' => '📱'],
                ['name' => 'Investasi', 'icon' => '📈'],
            ];
            foreach ($defaults as $d) {
                \App\Models\WalletType::create([
                    'family_id' => $family->id,
                    'name' => $d['name'],
                    'icon' => $d['icon'],
                ]);
            }
            $walletTypes = \App\Models\WalletType::where('family_id', $family->id)->get();
        }

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_balance' => (float) $totalBalance,
                'monthly_income' => (float) $monthlyIncome,
                'monthly_expense' => (float) $monthlyExpense,
            ],
            'recentTransactions' => $recentTransactions,
            'wallets' => $wallets,
            'budgets' => $budgets,
            'categories' => $categories,
            'walletTypes' => $walletTypes,
        ]);
    }
}
