<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Transaction;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class BudgetSpendingService
{
    /**
     * Preload budget spending totals in one query per period to avoid N+1 sums.
     */
    public function hydrate(Collection $budgets, int $familyId, CarbonInterface $startDate, CarbonInterface $endDate): Collection
    {
        $categoryIds = $budgets
            ->pluck('category_id')
            ->filter()
            ->unique()
            ->values();

        if ($categoryIds->isEmpty()) {
            $budgets->each(fn (Budget $budget) => $budget->setRelation('spent_local_cache', 0.0));

            return $budgets;
        }

        $totals = Transaction::query()
            ->where('family_id', $familyId)
            ->where('type', 'expense')
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->whereIn('category_id', $categoryIds)
            ->select('category_id')
            ->selectRaw('SUM(amount) as total')
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        $budgets->each(function (Budget $budget) use ($totals) {
            $budget->setRelation(
                'spent_local_cache',
                (float) ($totals[$budget->category_id] ?? 0)
            );
        });

        return $budgets;
    }
}
