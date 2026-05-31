<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Services\BudgetSpendingService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BudgetController extends Controller
{
    public function __construct(private BudgetSpendingService $budgetSpending) {}

    /**
     * Show the budgeting page.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $family = $user->family;

        $filters = $request->validate([
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
            'week' => ['nullable', 'integer', 'between:1,53'],
        ]);

        $month = (int) ($filters['month'] ?? now()->month);
        $year = (int) ($filters['year'] ?? now()->year);
        $week = (int) ($filters['week'] ?? now()->isoWeek());
        $monthStart = now()->setDate($year, $month, 1)->startOfMonth();
        $monthEnd = $monthStart->endOfMonth();
        $weekStart = now()->setISODate($year, $week)->startOfWeek();
        $weekEnd = $weekStart->endOfWeek();

        $monthlyBudgets = Budget::with('category:id,name,icon,color')
            ->where('family_id', $family->id)
            ->where('period', 'monthly')
            ->where('month', $month)
            ->where('year', $year)
            ->orderByDesc('amount')
            ->get();

        $this->budgetSpending->hydrate($monthlyBudgets, $family->id, $monthStart, $monthEnd);

        $monthlyBudgets = $monthlyBudgets
            ->map(fn ($b) => $this->formatBudget($b));

        $weeklyBudgets = Budget::with('category:id,name,icon,color')
            ->where('family_id', $family->id)
            ->where('period', 'weekly')
            ->where('week', $week)
            ->where('year', $year)
            ->orderByDesc('amount')
            ->get();

        $this->budgetSpending->hydrate($weeklyBudgets, $family->id, $weekStart, $weekEnd);

        $weeklyBudgets = $weeklyBudgets
            ->map(fn ($b) => $this->formatBudget($b));

        $categories = Category::where('family_id', $family->id)
            ->where('type', 'expense')
            ->orderBy('name')
            ->get(['id', 'family_id', 'name', 'type', 'icon', 'color']);

        $totalMonthlyBudget = $monthlyBudgets->sum('amount');
        $totalMonthlySpent = $monthlyBudgets->sum('spent');

        $monthlyIncome = Transaction::where('family_id', $family->id)
            ->where('type', 'income')
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->sum('amount');

        return Inertia::render('Budget/Index', [
            'monthlyBudgets' => $monthlyBudgets,
            'weeklyBudgets' => $weeklyBudgets,
            'categories' => $categories,
            'filters' => [
                'month' => $month,
                'year' => $year,
                'week' => $week,
            ],
            'summary' => [
                'total_budget' => (float) $totalMonthlyBudget,
                'total_spent' => (float) $totalMonthlySpent,
                'total_remaining' => (float) ($totalMonthlyBudget - $totalMonthlySpent),
                'monthly_income' => (float) $monthlyIncome,
                'budget_vs_income' => $monthlyIncome > 0
                    ? round(($totalMonthlyBudget / $monthlyIncome) * 100, 1)
                    : 0,
            ],
        ]);
    }

    /**
     * Store a new budget.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required',
            'new_category_name' => 'nullable|string|max:100',
            'amount' => 'required|numeric|min:1',
            'period' => 'required|in:monthly,weekly',
            'note' => 'nullable|string|max:255',
        ]);

        $family = $request->user()->family;
        $now = now();
        $categoryId = $validated['category_id'];

        if ($categoryId === 'new') {
            $request->validate([
                'new_category_name' => 'required|string|max:100',
            ]);

            $pastelColors = ['#3b82f6', '#f59e0b', '#ec4899', '#8b5cf6', '#ef4444', '#06b6d4', '#f97316', '#10b981'];
            $randomColor = $pastelColors[array_rand($pastelColors)];

            $category = Category::create([
                'family_id' => $family->id,
                'name' => $validated['new_category_name'],
                'type' => 'expense',
                'icon' => 'piggy-bank',
                'color' => $randomColor,
            ]);

            $categoryId = $category->id;
        } else {
            $request->validate([
                'category_id' => [
                    'required',
                    Rule::exists('categories', 'id')->where(fn ($query) => $query
                        ->where('family_id', $family->id)
                        ->where('type', 'expense')),
                ],
            ]);
        }

        if ($validated['period'] === 'monthly') {
            $lookup = [
                'family_id' => $family->id,
                'category_id' => $categoryId,
                'period' => 'monthly',
                'month' => $now->month,
                'year' => $now->year,
            ];

            $periodData = ['week' => null];
        } else {
            $lookup = [
                'family_id' => $family->id,
                'category_id' => $categoryId,
                'period' => 'weekly',
                'week' => $now->isoWeek(),
                'year' => $now->year,
            ];

            $periodData = ['month' => null];
        }

        Budget::updateOrCreate($lookup, [
            ...$periodData,
            'amount' => $validated['amount'],
            'note' => $validated['note'] ?? null,
            'is_recurring' => true,
        ]);

        return back()->with('success', 'Budget berhasil disimpan!');
    }

    /**
     * Update an existing budget.
     */
    public function update(Request $request, Budget $budget)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'note' => 'nullable|string|max:255',
        ]);

        $budget->update($validated);

        return back()->with('success', 'Budget berhasil diperbarui!');
    }

    /**
     * Delete a budget.
     */
    public function destroy(Budget $budget)
    {
        $budget->delete();

        return back()->with('success', 'Budget berhasil dihapus!');
    }

    /**
     * Format budget for frontend.
     */
    private function formatBudget(Budget $budget): array
    {
        return [
            'id' => $budget->id,
            'category_id' => $budget->category_id,
            'category' => $budget->category ? [
                'id' => $budget->category->id,
                'name' => $budget->category->name,
                'icon' => $budget->category->icon,
                'color' => $budget->category->color,
            ] : null,
            'amount' => (float) $budget->amount,
            'spent' => $budget->spent,
            'remaining' => $budget->remaining,
            'percentage' => $budget->percentage,
            'status' => $budget->status,
            'is_over_budget' => $budget->is_over_budget,
            'period' => $budget->period,
            'month' => $budget->month,
            'year' => $budget->year,
            'week' => $budget->week,
            'note' => $budget->note,
            'is_recurring' => $budget->is_recurring,
        ];
    }
}
