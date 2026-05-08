<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Barryvdh\DomPDF\Facade\Pdf;

class ActivityController extends Controller
{
    /**
     * Display the activity history page.
     */
    public function index(Request $request): Response
    {
        $family = $request->user()->family;

        $type = $request->get('type');
        $categoryId = $request->get('category_id');
        $userId = $request->get('user_id');
        $month = $request->get('month', '');
        $year = $request->get('year', '');

        $transactions = Transaction::with(['category', 'wallet', 'user'])
            ->where('family_id', $family->id)
            ->when($type, fn ($q) => $q->where('type', $type))
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when($month, fn ($q) => $q->whereMonth('date', $month))
            ->when($year, fn ($q) => $q->whereYear('date', $year))
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Format transactions for frontend
        $transactions->getCollection()->transform(function ($t) {
            return [
                'id' => $t->id,
                'type' => $t->type,
                'amount' => (float) $t->amount,
                'note' => $t->note,
                'date' => $t->date->format('Y-m-d'),
                'receipt_photo' => $t->receipt_photo,
                'category' => $t->category ? [
                    'id' => $t->category->id,
                    'name' => $t->category->name,
                    'icon' => $t->category->icon,
                    'color' => $t->category->color,
                ] : null,
                'wallet' => $t->wallet ? [
                    'id' => $t->wallet->id,
                    'name' => $t->wallet->name,
                ] : null,
                'user' => $t->user ? [
                    'id' => $t->user->id,
                    'name' => $t->user->name,
                    'role' => $t->user->role,
                    'avatar' => $t->user->avatar,
                ] : null,
            ];
        });

        $members = $family->users()->get(['id', 'name', 'role', 'avatar']);
        $categories = Category::where('family_id', $family->id)->get(['id', 'name', 'type', 'color']);

        // Get unique years of transactions for the filter dropdown (database-agnostic)
        $driver = \DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            $availableYears = Transaction::where('family_id', $family->id)
                ->whereNotNull('date')
                ->selectRaw("strftime('%Y', date) as year")
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year')
                ->map(fn($y) => (int)$y)
                ->filter()
                ->toArray();
        } else {
            $availableYears = Transaction::where('family_id', $family->id)
                ->whereNotNull('date')
                ->selectRaw('YEAR(date) as year')
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year')
                ->map(fn($y) => (int)$y)
                ->filter()
                ->toArray();
        }

        if (empty($availableYears)) {
            $availableYears = [now()->year];
        }

        return Inertia::render('Activity/Index', [
            'transactions' => $transactions,
            'members' => $members,
            'categories' => $categories,
            'availableYears' => array_values($availableYears),
            'filters' => [
                'type' => $type ?? '',
                'category_id' => $categoryId ?? '',
                'user_id' => $userId ?? '',
                'month' => $month ?? '',
                'year' => $year ?? '',
            ]
        ]);
    }

    /**
     * Download transactions for a given month and year as a PDF.
     */
    public function downloadPdf(Request $request)
    {
        $family = $request->user()->family;
        if (!$family) {
            return redirect()->route('family.setup');
        }

        $month = $request->get('month');
        $year = $request->get('year');
        $type = $request->get('type');
        $categoryId = $request->get('category_id');
        $userId = $request->get('user_id');

        // Query transactions - fully dynamic filtering
        $query = Transaction::with(['category', 'wallet', 'user'])
            ->where('family_id', $family->id)
            ->when($month, fn ($q) => $q->whereMonth('date', $month))
            ->when($year, fn ($q) => $q->whereYear('date', $year))
            ->when($type, fn ($q) => $q->where('type', $type))
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->orderBy('date', 'asc')
            ->orderBy('id', 'asc');

        $transactions = $query->get();

        // Calculate summary stats
        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpense = $transactions->where('type', 'expense')->sum('amount');
        $netSavings = $totalIncome - $totalExpense;

        // Group by category for breakdown
        $categoryBreakdown = [];
        foreach ($transactions as $t) {
            $catName = $t->category?->name ?? 'Lainnya';
            $catType = $t->type; // expense / income / transfer
            
            if (!isset($categoryBreakdown[$catName])) {
                $categoryBreakdown[$catName] = [
                    'name' => $catName,
                    'type' => $catType,
                    'color' => $t->category?->color ?? '#64748b',
                    'total' => 0,
                    'count' => 0
                ];
            }
            $categoryBreakdown[$catName]['total'] += (float) $t->amount;
            $categoryBreakdown[$catName]['count']++;
        }

        // Group by user for breakdown
        $userBreakdown = [];
        foreach ($transactions as $t) {
            $uName = $t->user?->name ?? 'Sistem';
            $uRole = $t->user?->role ?? 'Anggota';
            if (!isset($userBreakdown[$uName])) {
                $userBreakdown[$uName] = [
                    'name' => $uName,
                    'role' => $uRole,
                    'income' => 0,
                    'expense' => 0
                ];
            }
            if ($t->type === 'income') {
                $userBreakdown[$uName]['income'] += (float) $t->amount;
            } else if ($t->type === 'expense') {
                $userBreakdown[$uName]['expense'] += (float) $t->amount;
            }
        }

        // Fetch monthly budgets for this period
        $budgets = Budget::with('category')
            ->where('family_id', $family->id)
            ->where('period', 'monthly')
            ->when($month, fn ($q) => $q->where('month', $month))
            ->when($year, fn ($q) => $q->where('year', $year))
            ->get();

        // Format month name in Indonesian
        $monthName = 'Semua Bulan';
        if (!empty($month)) {
            $monthsId = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            $monthName = $monthsId[(int)$month] ?? date('F', mktime(0, 0, 0, $month, 10));
        }

        $yearLabel = !empty($year) ? $year : 'Semua Tahun';

        // Format data for JSON AI block
        $aiData = [
            'report_info' => [
                'family_name' => $family->name,
                'period' => $monthName . ($yearLabel !== 'Semua Tahun' ? " $yearLabel" : ""),
                'generated_at' => now()->toIso8601String(),
            ],
            'financial_summary' => [
                'total_income' => (float)$totalIncome,
                'total_expense' => (float)$totalExpense,
                'net_savings' => (float)$netSavings,
            ],
            'category_breakdown' => array_values($categoryBreakdown),
            'user_breakdown' => array_values($userBreakdown),
            'budgets' => $budgets->map(function ($b) {
                return [
                    'category' => $b->category?->name ?? 'Lainnya',
                    'limit_amount' => (float)$b->amount,
                    'spent_amount' => (float)$b->spent,
                    'remaining_amount' => (float)$b->remaining,
                    'percentage_used' => (float)$b->percentage,
                    'status' => $b->status,
                ];
            })->all(),
            'transactions' => $transactions->map(function ($t) {
                return [
                    'date' => $t->date->format('Y-m-d'),
                    'note' => $t->note ?? $t->category?->name ?? 'Transaksi',
                    'category' => $t->category?->name ?? 'Lainnya',
                    'wallet' => $t->wallet?->name ?? '-',
                    'user' => $t->user?->name ?? 'Sistem',
                    'role' => $t->user?->role ?? '-',
                    'type' => $t->type,
                    'amount' => (float)$t->amount,
                ];
            })->all()
        ];

        $pdf = Pdf::loadView('pdf.transactions', [
            'familyName' => $family->name,
            'monthName' => $monthName,
            'year' => $yearLabel,
            'transactions' => $transactions,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'netSavings' => $netSavings,
            'categoryBreakdown' => $categoryBreakdown,
            'userBreakdown' => $userBreakdown,
            'budgets' => $budgets,
            'aiJson' => json_encode($aiData, JSON_PRETTY_PRINT),
        ]);

        $safeFamilyName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $family->name));
        $periodFileStr = strtolower(str_replace(' ', '_', $monthName)) . "_" . strtolower(str_replace(' ', '_', $yearLabel));
        $fileName = "kaskita_{$safeFamilyName}_transaksi_{$periodFileStr}.pdf";

        return $pdf->download($fileName);
    }
}

