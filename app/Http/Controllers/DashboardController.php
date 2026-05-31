<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\WalletType;
use App\Services\BudgetSpendingService;
use App\Services\FamilyService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, BudgetSpendingService $budgetSpending): Response
    {
        $user = $request->user();
        $family = $user->family;

        if (! $family) {
            return redirect()->route('family.setup');
        }

        $now = now();
        $monthStart = $now->startOfMonth();
        $monthEnd = $now->endOfMonth();

        $totalBalance = Wallet::where('family_id', $family->id)
            ->where('is_active', true)
            ->sum('balance');

        $monthlyTotals = Transaction::query()
            ->where('family_id', $family->id)
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as income")
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as expense")
            ->first();

        $recentTransactions = Transaction::query()
            ->with([
                'category:id,name,icon,color',
                'wallet:id,name',
                'user:id,name,role,avatar',
            ])
            ->select([
                'id',
                'family_id',
                'category_id',
                'wallet_id',
                'user_id',
                'type',
                'amount',
                'note',
                'date',
                'created_at',
            ])
            ->where('family_id', $family->id)
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $wallets = Wallet::where('family_id', $family->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'family_id', 'name', 'type', 'balance', 'color', 'icon', 'is_active']);

        $budgets = Budget::with('category:id,name,color')
            ->select(['id', 'family_id', 'category_id', 'amount', 'period', 'month', 'year', 'week'])
            ->where('family_id', $family->id)
            ->where('period', 'monthly')
            ->where('month', $now->month)
            ->where('year', $now->year)
            ->orderByDesc('amount')
            ->limit(4)
            ->get();

        $budgetSpending->hydrate($budgets, $family->id, $monthStart, $monthEnd);

        $budgets = $budgets
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

        $categories = Category::where('family_id', $family->id)
            ->orderBy('type')
            ->orderBy('name')
            ->get(['id', 'family_id', 'name', 'type', 'icon', 'color']);

        $walletTypes = $this->walletTypesForFamily($family->id);

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_balance' => (float) $totalBalance,
                'monthly_income' => (float) ($monthlyTotals->income ?? 0),
                'monthly_expense' => (float) ($monthlyTotals->expense ?? 0),
            ],
            'recentTransactions' => $recentTransactions,
            'wallets' => $wallets,
            'budgets' => $budgets,
            'categories' => $categories,
            'walletTypes' => $walletTypes,
        ]);
    }

    private function walletTypesForFamily(int $familyId)
    {
        $walletTypes = WalletType::where('family_id', $familyId)
            ->orderBy('id')
            ->get(['id', 'family_id', 'name', 'icon']);

        if ($walletTypes->isNotEmpty()) {
            return $walletTypes;
        }

        foreach (FamilyService::defaultWalletTypes() as $walletType) {
            WalletType::firstOrCreate(
                ['family_id' => $familyId, 'name' => $walletType['name']],
                ['icon' => $walletType['icon']]
            );
        }

        return WalletType::where('family_id', $familyId)
            ->orderBy('id')
            ->get(['id', 'family_id', 'name', 'icon']);
    }
}
