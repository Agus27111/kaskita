<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi KasKita - {{ $familyName }}</title>
    <style>
        @page {
            margin: 1.2cm 1.2cm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        
        /* Header Section */
        .header-container {
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-title {
            font-size: 22px;
            font-weight: bold;
            color: #1e3a8a;
            margin: 0;
            letter-spacing: -0.5px;
        }
        .header-subtitle {
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
            margin-bottom: 0;
        }
        .header-meta {
            text-align: right;
            font-size: 10px;
            color: #64748b;
        }
        .header-meta .date-highlight {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
        }

        /* Stats Section */
        .stats-grid {
            width: 100%;
            margin-bottom: 20px;
        }
        .stats-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px 0;
            margin: 0 -10px;
        }
        .stat-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
        }
        .stat-card-title {
            font-size: 9px;
            text-transform: uppercase;
            font-weight: bold;
            color: #64748b;
            margin-bottom: 4px;
        }
        .stat-card-value {
            font-size: 16px;
            font-weight: bold;
        }
        .stat-income {
            border-left: 4px solid #10b981;
        }
        .stat-income .stat-card-value {
            color: #10b981;
        }
        .stat-expense {
            border-left: 4px solid #ef4444;
        }
        .stat-expense .stat-card-value {
            color: #ef4444;
        }
        .stat-net {
            border-left: 4px solid #3b82f6;
        }
        .stat-net-positive .stat-card-value {
            color: #3b82f6;
        }
        .stat-net-negative .stat-card-value {
            color: #ef4444;
        }

        /* Section Titles */
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 20px;
            margin-bottom: 8px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Table Styles */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th {
            background-color: #f1f5f9;
            color: #475569;
            font-weight: bold;
            text-align: left;
            padding: 7px 10px;
            font-size: 10px;
            border-bottom: 1px solid #cbd5e1;
        }
        .data-table td {
            padding: 7px 10px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 10px;
            vertical-align: middle;
        }
        .data-table tr:nth-child(even) td {
            background-color: #fafafa;
        }
        
        /* Badges & Accents */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-income {
            background-color: #ecfdf5;
            color: #065f46;
        }
        .badge-expense {
            background-color: #fef2f2;
            color: #991b1b;
        }
        .badge-transfer {
            background-color: #eff6ff;
            color: #1e40af;
        }
        
        .role-pill {
            display: inline-block;
            background-color: #f1f5f9;
            color: #334155;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 8px;
        }

        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-bold {
            font-weight: bold;
        }
        .text-income {
            color: #10b981;
        }
        .text-expense {
            color: #ef4444;
        }

        /* Breakdown Grids (side by side) */
        .breakdown-container {
            width: 100%;
            margin-bottom: 20px;
        }
        .breakdown-table {
            width: 100%;
            border-collapse: collapse;
        }
        .breakdown-col {
            width: 50%;
            vertical-align: top;
        }
        .breakdown-card {
            background-color: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
            margin-right: 5px;
        }
        .breakdown-card-left {
            margin-right: 5px;
        }
        .breakdown-card-right {
            margin-left: 5px;
        }
        .breakdown-item {
            display: table;
            width: 100%;
            margin-bottom: 6px;
            font-size: 10px;
        }
        .breakdown-item-name {
            display: table-cell;
            text-align: left;
        }
        .breakdown-item-value {
            display: table-cell;
            text-align: right;
            font-weight: bold;
        }

        /* AI Monospace block */
        .ai-section {
            page-break-before: always;
            background-color: #0f172a;
            color: #38bdf8;
            border-radius: 8px;
            padding: 15px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 8px;
            line-height: 1.3;
            border: 1px solid #1e293b;
        }
        .ai-header {
            color: #f43f5e;
            font-weight: bold;
            font-size: 11px;
            border-bottom: 1px dashed #334155;
            padding-bottom: 6px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .ai-body {
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</head>
<body>

    <!-- Main Report Page -->
    <div class="header-container">
        <table class="header-table">
            <tr>
                <td>
                    <h1 class="header-title">KasKita</h1>
                    <p class="header-subtitle">Laporan Transaksi Keuangan Keluarga</p>
                </td>
                <td class="header-meta">
                    <div>Periode Laporan:</div>
                    <div class="date-highlight">{{ $monthName }} {{ $year }}</div>
                    <div style="margin-top: 4px;">Keluarga: <strong>{{ $familyName }}</strong></div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Summary Stats -->
    <div class="stats-grid">
        <table class="stats-table">
            <tr>
                <td style="width: 33.3%;">
                    <div class="stat-card stat-income">
                        <div class="stat-card-title">Total Pemasukan</div>
                        <div class="stat-card-value">Rp {{ number_format($totalIncome, 0, ',', '.') }}</div>
                    </div>
                </td>
                <td style="width: 33.3%;">
                    <div class="stat-card stat-expense">
                        <div class="stat-card-title">Total Pengeluaran</div>
                        <div class="stat-card-value">Rp {{ number_format($totalExpense, 0, ',', '.') }}</div>
                    </div>
                </td>
                <td style="width: 33.3%;">
                    <div class="stat-card stat-net {{ $netSavings >= 0 ? 'stat-net-positive' : 'stat-net-negative' }}">
                        <div class="stat-card-title">Arus Kas Bersih</div>
                        <div class="stat-card-value">
                            {{ $netSavings >= 0 ? '+' : '' }}Rp {{ number_format($netSavings, 0, ',', '.') }}
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Comparative Cash Flow Bar Chart -->
    @php
        $totalTurnover = $totalIncome + $totalExpense;
        $incomePercentage = $totalTurnover > 0 ? ($totalIncome / $totalTurnover) * 100 : 0;
        $expensePercentage = $totalTurnover > 0 ? ($totalExpense / $totalTurnover) * 100 : 0;
    @endphp
    @if($totalTurnover > 0)
    <div style="margin-top: -10px; margin-bottom: 20px; padding: 0 10px;">
        <div style="font-size: 8px; font-weight: bold; text-transform: uppercase; color: #64748b; margin-bottom: 4px;">Proporsi Aliran Kas Masuk vs Keluar (Turnover)</div>
        <table style="width: 100%; border-collapse: collapse; height: 16px; border-radius: 4px; overflow: hidden; table-layout: fixed;">
            <tr>
                @if($totalIncome > 0)
                <td style="width: {{ $incomePercentage }}%; background-color: #10b981; text-align: center; color: white; font-size: 8px; font-weight: bold; padding: 2px 0;">
                    {{ round($incomePercentage) }}% Pemasukan
                </td>
                @endif
                @if($totalExpense > 0)
                <td style="width: {{ $expensePercentage }}%; background-color: #ef4444; text-align: center; color: white; font-size: 8px; font-weight: bold; padding: 2px 0;">
                    {{ round($expensePercentage) }}% Pengeluaran
                </td>
                @endif
            </tr>
        </table>
    </div>
    @endif

    <!-- Breakdown Analytics -->
    <div class="breakdown-container">
        <table class="breakdown-table">
            <tr>
                <td class="breakdown-col">
                    <div class="breakdown-card breakdown-card-left">
                        <div style="font-weight: bold; border-bottom: 1px solid #cbd5e1; padding-bottom: 4px; margin-bottom: 8px; font-size: 10px; color: #1e3a8a;">
                            ANALISIS ALOKASI KATEGORI
                        </div>
                        @forelse($categoryBreakdown as $cat)
                            @php
                                $divider = $cat['type'] === 'income' ? ($totalIncome > 0 ? $totalIncome : 1) : ($totalExpense > 0 ? $totalExpense : 1);
                                $catPercentage = ($cat['total'] / $divider) * 100;
                            @endphp
                            <div class="breakdown-item" style="margin-bottom: 2px;">
                                <span class="breakdown-item-name">
                                    <span style="color: {{ $cat['color'] ?? '#64748b' }};">●</span> {{ $cat['name'] }}
                                    <span style="font-size: 8px; color: #94a3b8;">({{ $cat['count'] }}x)</span>
                                </span>
                                <span class="breakdown-item-value {{ $cat['type'] === 'income' ? 'text-income' : 'text-expense' }}">
                                    {{ $cat['type'] === 'income' ? '+' : '-' }}Rp {{ number_format($cat['total'], 0, ',', '.') }}
                                    <span style="font-size: 8px; color: #94a3b8; font-weight: normal;">({{ round($catPercentage) }}%)</span>
                                </span>
                            </div>
                            <div style="background-color: #f1f5f9; border-radius: 3px; height: 4px; width: 100%; margin-top: 2px; margin-bottom: 10px; overflow: hidden;">
                                <div style="background-color: {{ $cat['color'] ?? '#64748b' }}; height: 100%; width: {{ $catPercentage }}%;"></div>
                            </div>
                        @empty
                            <div style="color: #94a3b8; font-size: 9px; text-align: center; padding: 10px 0;">Tidak ada aktivitas kategori</div>
                        @endforelse
                    </div>
                </td>
                <td class="breakdown-col">
                    <div class="breakdown-card breakdown-card-right">
                        <div style="font-weight: bold; border-bottom: 1px solid #cbd5e1; padding-bottom: 4px; margin-bottom: 8px; font-size: 10px; color: #1e3a8a;">
                            KONTRIBUSI ANGGOTA
                        </div>
                        @forelse($userBreakdown as $usr)
                            <div class="breakdown-item">
                                <span class="breakdown-item-name">
                                    {{ $usr['name'] }} <span class="role-pill">{{ $usr['role'] ? ucfirst($usr['role']) : 'Anggota' }}</span>
                                </span>
                                <span class="breakdown-item-value">
                                    @if($usr['income'] > 0)
                                        <span class="text-income" style="font-size: 9px; margin-right: 4px;">+{{ number_format($usr['income'], 0, ',', '.') }}</span>
                                    @endif
                                    @if($usr['expense'] > 0)
                                        <span class="text-expense" style="font-size: 9px;">-{{ number_format($usr['expense'], 0, ',', '.') }}</span>
                                    @endif
                                </span>
                            </div>
                        @empty
                            <div style="color: #94a3b8; font-size: 9px; text-align: center; padding: 10px 0;">Tidak ada kontribusi anggota</div>
                        @endforelse
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Monitoring Anggaran Bulanan (Budgeting) -->
    @if(count($budgets) > 0)
    <div class="section-title">Monitoring Anggaran Bulanan (Budgeting)</div>
    <div style="width: 100%; margin-bottom: 20px;">
        <table style="width: 100%; border-collapse: collapse; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background-color: #ffffff; table-layout: fixed;">
            <thead>
                <tr style="background-color: #f8fafc;">
                    <th style="padding: 6px 10px; font-size: 9px; font-weight: bold; color: #475569; text-align: left; border-bottom: 1px solid #cbd5e1; width: 25%;">Kategori</th>
                    <th style="padding: 6px 10px; font-size: 9px; font-weight: bold; color: #475569; text-align: right; border-bottom: 1px solid #cbd5e1; width: 20%;">Batas Anggaran</th>
                    <th style="padding: 6px 10px; font-size: 9px; font-weight: bold; color: #475569; text-align: right; border-bottom: 1px solid #cbd5e1; width: 20%;">Realisasi Terpakai</th>
                    <th style="padding: 6px 10px; font-size: 9px; font-weight: bold; color: #475569; text-align: right; border-bottom: 1px solid #cbd5e1; width: 20%;">Sisa Anggaran</th>
                    <th style="padding: 6px 10px; font-size: 9px; font-weight: bold; color: #475569; text-align: center; border-bottom: 1px solid #cbd5e1; width: 15%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($budgets as $b)
                    @php
                        $pct = $b->percentage;
                        $barColor = '#10b981'; // default safe (green)
                        $badgeColor = '#ecfdf5';
                        $badgeText = '#065f46';
                        
                        if ($b->status === 'over') {
                            $barColor = '#ef4444'; // danger/over (red)
                            $badgeColor = '#fef2f2';
                            $badgeText = '#991b1b';
                        } elseif ($b->status === 'danger') {
                            $barColor = '#f97316'; // orange
                            $badgeColor = '#fff7ed';
                            $badgeText = '#c2410c';
                        } elseif ($b->status === 'warning') {
                            $barColor = '#eab308'; // yellow
                            $badgeColor = '#fef9c3';
                            $badgeText = '#854d0e';
                        }
                    @endphp
                    <tr>
                        <td style="padding: 6px 10px; border-bottom: 1px solid #e2e8f0; font-size: 9px; vertical-align: middle;">
                            <span style="font-weight: bold; color: #0f172a;">{{ $b->category?->name ?? 'Lainnya' }}</span>
                        </td>
                        <td style="padding: 6px 10px; border-bottom: 1px solid #e2e8f0; font-size: 9px; text-align: right; vertical-align: middle; font-weight: bold; color: #475569;">
                            Rp {{ number_format((float)$b->amount, 0, ',', '.') }}
                        </td>
                        <td style="padding: 6px 10px; border-bottom: 1px solid #e2e8f0; font-size: 9px; text-align: right; vertical-align: middle; font-weight: bold; color: {{ $b->status === 'over' ? '#ef4444' : '#1e293b' }};">
                            Rp {{ number_format((float)$b->spent, 0, ',', '.') }}
                        </td>
                        <td style="padding: 6px 10px; border-bottom: 1px solid #e2e8f0; font-size: 9px; text-align: right; vertical-align: middle; font-weight: bold; color: {{ $b->remaining < 0 ? '#ef4444' : '#10b981' }};">
                            {{ $b->remaining < 0 ? '-' : '' }}Rp {{ number_format(abs((float)$b->remaining), 0, ',', '.') }}
                        </td>
                        <td style="padding: 6px 10px; border-bottom: 1px solid #e2e8f0; font-size: 9px; text-align: center; vertical-align: middle;">
                            <span style="background-color: {{ $badgeColor }}; color: {{ $badgeText }}; padding: 1px 4px; border-radius: 3px; font-size: 7px; font-weight: bold; text-transform: uppercase;">
                                {{ $b->status === 'over' ? 'OVER LIMIT' : $b->status }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5" style="padding: 2px 10px 8px 10px; border-bottom: 1px solid #e2e8f0; font-size: 9px; vertical-align: middle;">
                            <div style="font-size: 7px; color: #64748b; margin-bottom: 1px; text-align: right;">Terpakai {{ $pct }}%</div>
                            <div style="background-color: #f1f5f9; border-radius: 2px; height: 3px; width: 100%; overflow: hidden;">
                                <div style="background-color: {{ $barColor }}; height: 100%; width: {{ min($pct, 100) }}%;"></div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Transaction Table -->
    <div class="section-title">Daftar Transaksi Rinci</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 12%;">Tanggal</th>
                <th style="width: 35%;">Deskripsi / Catatan</th>
                <th style="width: 15%;">Kategori</th>
                <th style="width: 13%;">Dompet</th>
                <th style="width: 10%;">Anggota</th>
                <th style="width: 15%; text-align: right;">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $t)
                <tr>
                    <td>{{ $t->date->format('d/m/Y') }}</td>
                    <td>
                        <span class="font-bold">{{ $t->note ?? $t->category?->name ?? 'Transaksi Tanpa Catatan' }}</span>
                    </td>
                    <td>
                        <span style="color: {{ $t->category?->color ?? '#64748b' }};">●</span> 
                        {{ $t->category?->name ?? 'Lainnya' }}
                    </td>
                    <td>{{ $t->wallet?->name ?? '-' }}</td>
                    <td>
                        <span class="role-pill">{{ $t->user?->role ? ($t->user?->role === 'ayah' ? 'Ayah' : 'Ibu') : substr($t->user?->name ?? 'Sistem', 0, 8) }}</span>
                    </td>
                    <td class="text-right font-bold {{ $t->type === 'income' ? 'text-income' : 'text-expense' }}">
                        {{ $t->type === 'income' ? '+' : '-' }} {{ number_format($t->amount, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="color: #94a3b8; padding: 20px;">Tidak ada transaksi pada bulan ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 30px; border-top: 1px dashed #cbd5e1; padding-top: 10px; font-size: 8px; color: #94a3b8; text-align: center;">
        Laporan ini digenerate secara otomatis oleh sistem KasKita pada {{ now()->format('d/m/Y H:i:s') }}. Halaman berikutnya berisi data terstruktur (JSON) yang optimal dibaca oleh asisten AI Anda.
    </div>

    <!-- AI Raw Ingestion Block Page -->
    <div class="ai-section">
        <div class="ai-header">--- DATA ANALISIS AI (SALIN BLOK JSON DI BAWAH INI UNTUK DIKIRIM KE AI) ---</div>
        <div class="ai-body">{{ $aiJson }}</div>
    </div>

</body>
</html>
