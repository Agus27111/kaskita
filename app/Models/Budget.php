<?php

namespace App\Models;

use App\Traits\BelongsToFamily;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budget extends Model
{
    use BelongsToFamily, HasFactory;

    protected $fillable = [
        'family_id',
        'category_id',
        'amount',
        'period',
        'month',
        'year',
        'week',
        'is_recurring',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_recurring' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the total spent amount for this budget's period (optimized with in-memory caching).
     */
    public function getSpentAttribute(): float
    {
        if (! array_key_exists('spent_local_cache', $this->relations)) {
            $query = Transaction::where('family_id', $this->family_id)
                ->where('category_id', $this->category_id)
                ->where('type', 'expense');

            if ($this->period === 'weekly' && $this->week && $this->year) {
                $startOfWeek = now()->setISODate($this->year, $this->week)->startOfWeek();
                $endOfWeek = now()->setISODate($this->year, $this->week)->endOfWeek();
                $query->whereBetween('date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()]);
            } elseif ($this->month && $this->year) {
                $startOfMonth = now()->setDate($this->year, $this->month, 1)->startOfMonth();
                $query->whereBetween('date', [$startOfMonth->toDateString(), $startOfMonth->endOfMonth()->toDateString()]);
            } else {
                $query->whereRaw('1 = 0');
            }

            $this->setRelation('spent_local_cache', (float) $query->sum('amount'));
        }

        return $this->getRelation('spent_local_cache');
    }

    /**
     * Get remaining budget.
     */
    public function getRemainingAttribute(): float
    {
        return (float) $this->amount - $this->spent;
    }

    /**
     * Get usage percentage.
     */
    public function getPercentageAttribute(): float
    {
        if ((float) $this->amount === 0.0) {
            return 0;
        }

        return round(($this->spent / (float) $this->amount) * 100, 1);
    }

    /**
     * Check if budget is over limit.
     */
    public function getIsOverBudgetAttribute(): bool
    {
        return $this->spent > (float) $this->amount;
    }

    /**
     * Get budget status: safe, warning, danger, over.
     */
    public function getStatusAttribute(): string
    {
        $pct = $this->percentage;

        if ($pct > 100) {
            return 'over';
        }
        if ($pct >= 80) {
            return 'danger';
        }
        if ($pct >= 60) {
            return 'warning';
        }

        return 'safe';
    }

    /**
     * Scope: current month budgets.
     */
    public function scopeCurrentMonth($query)
    {
        return $query->where('period', 'monthly')
            ->where('month', now()->month)
            ->where('year', now()->year);
    }

    /**
     * Scope: current week budgets.
     */
    public function scopeCurrentWeek($query)
    {
        return $query->where('period', 'weekly')
            ->where('week', now()->isoWeek())
            ->where('year', now()->year);
    }

    /**
     * Scope: all active budgets (current month + current week).
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->where(function ($q2) {
                $q2->where('period', 'monthly')
                    ->where('month', now()->month)
                    ->where('year', now()->year);
            })->orWhere(function ($q2) {
                $q2->where('period', 'weekly')
                    ->where('week', now()->isoWeek())
                    ->where('year', now()->year);
            });
        });
    }
}
