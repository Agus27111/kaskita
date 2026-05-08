<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BudgetController extends Controller
{
    /**
     * Show the budgeting page.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $family = $user->family;

        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);
        $week = (int) $request->get('week', now()->isoWeek());

        // Get monthly budgets with spent calculations
        $monthlyBudgets = Budget::with('category')
            ->where('family_id', $family->id)
            ->where('period', 'monthly')
            ->where('month', $month)
            ->where('year', $year)
            ->get()
            ->map(fn ($b) => $this->formatBudget($b));

        // Get weekly budgets
        $weeklyBudgets = Budget::with('category')
            ->where('family_id', $family->id)
            ->where('period', 'weekly')
            ->where('week', $week)
            ->where('year', $year)
            ->get()
            ->map(fn ($b) => $this->formatBudget($b));

        // Categories for creating new budgets (only expense type)
        $categories = Category::where('family_id', $family->id)
            ->where('type', 'expense')
            ->get();

        // Total monthly budget vs total spent
        $totalMonthlyBudget = $monthlyBudgets->sum('amount');
        $totalMonthlySpent = $monthlyBudgets->sum('spent');

        // Monthly income for context
        $monthlyIncome = Transaction::where('family_id', $family->id)
            ->where('type', 'income')
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
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
        }

        // Build budget data
        $data = [
            'family_id' => $family->id,
            'category_id' => $categoryId,
            'amount' => $validated['amount'],
            'period' => $validated['period'],
            'note' => $validated['note'] ?? null,
            'is_recurring' => true,
        ];

        if ($validated['period'] === 'monthly') {
            $data['month'] = $now->month;
            $data['year'] = $now->year;
        } else {
            $data['week'] = $now->isoWeek();
            $data['year'] = $now->year;
        }

        // Check if budget already exists for this period
        $existing = Budget::where('family_id', $family->id)
            ->where('category_id', $validated['category_id'])
            ->where('period', $validated['period'])
            ->when($validated['period'] === 'monthly', function ($q) use ($data) {
                $q->where('month', $data['month'])->where('year', $data['year']);
            })
            ->when($validated['period'] === 'weekly', function ($q) use ($data) {
                $q->where('week', $data['week'])->where('year', $data['year']);
            })
            ->first();

        if ($existing) {
            $existing->update(['amount' => $validated['amount'], 'note' => $data['note']]);
        } else {
            Budget::create($data);
        }

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
