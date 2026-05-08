<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import {
    Wallet as WalletIcon,
    ArrowUpRight,
    ArrowDownRight,
    Plus,
    History,
    TrendingUp,
    CreditCard,
    ChevronRight,
    Eye,
    EyeOff,
    Sparkles,
    Coins,
    X,
    Check,
    Trash2,
} from 'lucide-vue-next';
import { ref, computed, onMounted } from 'vue';

interface Wallet {
    id: number;
    name: string;
    type: string;
    balance: number;
    color: string;
    icon: string;
}

interface Category {
    id: number;
    name: string;
    icon: string;
    color: string;
}

interface Transaction {
    id: number;
    amount: string;
    type: 'income' | 'expense' | 'transfer';
    note: string;
    date: string;
    category?: Category;
    wallet: Wallet;
}

interface BudgetSummary {
    id: number;
    category_name: string;
    category_color: string;
    amount: number;
    spent: number;
    percentage: number;
    status: 'safe' | 'warning' | 'danger' | 'over';
}

interface WalletType {
    id: number;
    name: string;
    icon: string | null;
}

const props = defineProps<{
    stats: {
        total_balance: number;
        monthly_income: number;
        monthly_expense: number;
    };
    recentTransactions: Transaction[];
    wallets: Wallet[];
    budgets: BudgetSummary[];
    categories: Category[];
    walletTypes: WalletType[];
}>();

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
];

const showBalance = ref(true);

const showTransactionModal = ref(false);
const transactionModalType = ref<'income' | 'expense' | 'transfer'>('expense');

const transactionForm = useForm({
    type: 'expense' as 'income' | 'expense' | 'transfer',
    amount: '' as string | number,
    wallet_id: '' as string | number,
    category_id: '' as string | number,
    transfer_to_wallet_id: '' as string | number,
    note: '',
    date: new Date().toISOString().split('T')[0],
});

const openTransactionModal = (type: 'income' | 'expense' | 'transfer') => {
    transactionModalType.value = type;
    transactionForm.reset();
    transactionForm.type = type;
    
    if (props.wallets.length > 0) {
        transactionForm.wallet_id = props.wallets[0].id;
        if (type === 'transfer' && props.wallets.length > 1) {
            transactionForm.transfer_to_wallet_id = props.wallets[1].id;
        }
    }
    
    const matchingCats = props.categories.filter(c => c.type === type);
    if (matchingCats.length > 0) {
        transactionForm.category_id = matchingCats[0].id;
    } else {
        transactionForm.category_id = '';
    }

    showTransactionModal.value = true;
};

const submitTransaction = () => {
    transactionForm.post('/transactions', {
        onSuccess: () => {
            showTransactionModal.value = false;
            transactionForm.reset();
        },
    });
};

const showWalletModal = ref(false);
const walletForm = useForm({
    name: '',
    type: '',
    balance: '' as string | number,
    color: '#10b981',
});

const openWalletModal = () => {
    walletForm.reset();
    if (props.walletTypes.length > 0) {
        walletForm.type = props.walletTypes[0].name;
    }
    showWalletModal.value = true;
};

const submitWallet = () => {
    walletForm.post('/wallets', {
        onSuccess: () => {
            showWalletModal.value = false;
            walletForm.reset();
        },
    });
};

const confirmModal = ref({
    show: false,
    title: '',
    message: '',
    onConfirm: () => {},
});

const showConfirm = (title: string, message: string, onConfirm: () => void) => {
    confirmModal.value = {
        show: true,
        title,
        message,
        onConfirm,
    };
};

const executeConfirm = () => {
    confirmModal.value.onConfirm();
    confirmModal.value.show = false;
};

const deleteWallet = (wallet: any) => {
    showConfirm(
        'Hapus Dompet?',
        `Apakah Anda yakin ingin menghapus dompet "${wallet.name}"? Saldo dompet ini akan ikut terhapus dari sistem.`,
        () => { walletForm.delete(`/wallets/${wallet.id}`); }
    );
};

const showManageWalletTypesModal = ref(false);
const walletTypeForm = useForm({
    name: '',
    icon: '💰',
});

const submitWalletType = () => {
    walletTypeForm.post('/wallet-types', {
        onSuccess: () => {
            walletTypeForm.reset();
            if (props.walletTypes.length > 0) {
                walletForm.type = props.walletTypes[0].name;
            }
        }
    });
};

const deleteWalletType = (type: any) => {
    showConfirm(
        'Hapus Jenis Dompet?',
        `Apakah Anda yakin ingin menghapus jenis dompet "${type.name}"? Jenis ini tidak akan tersedia lagi untuk dompet baru.`,
        () => { walletTypeForm.delete(`/wallet-types/${type.id}`); }
    );
};

const getWalletEmoji = (type: string) => {
    const defaults: Record<string, string> = {
        'Rekening Bank': '🏦',
        'Uang Tunai (Cash)': '💵',
        'E-Wallet': '📱',
        'Investasi': '📈',
        'bank': '🏦',
        'cash': '💵',
        'ewallet': '📱',
        'investment': '📈',
    };
    return props.walletTypes.find(t => t.name === type)?.icon || defaults[type] || '💰';
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
    });
};

const maskedBalance = (value: number) => {
    if (!showBalance.value) return '•••••••';
    return formatCurrency(value);
};

const walletIcons: Record<string, string> = {
    bank: '🏦',
    cash: '💵',
    ewallet: '📱',
    investment: '📈',
};

const activeChartTab = ref<'cashflow' | 'categories' | 'ai'>('cashflow');

const weeklyChartData = computed(() => {
    const days = [];
    const today = new Date();
    
    for (let i = 6; i >= 0; i--) {
        const d = new Date();
        d.setDate(today.getDate() - i);
        const dateStr = d.toISOString().split('T')[0];
        const label = d.toLocaleDateString('id-ID', { weekday: 'short' });
        
        days.push({
            date: dateStr,
            label,
            income: 0,
            expense: 0,
        });
    }
    
    props.recentTransactions.forEach(t => {
        const tDate = t.date;
        const day = days.find(d => d.date === tDate);
        if (day) {
            const amt = parseFloat(t.amount);
            if (t.type === 'income') {
                day.income += amt;
            } else if (t.type === 'expense') {
                day.expense += amt;
            }
        }
    });
    
    let maxVal = 100000;
    days.forEach(d => {
        if (d.income > maxVal) maxVal = d.income;
        if (d.expense > maxVal) maxVal = d.expense;
    });
    
    return days.map(d => ({
        ...d,
        incomeHeight: maxVal > 0 ? (d.income / maxVal) * 100 : 0,
        expenseHeight: maxVal > 0 ? (d.expense / maxVal) * 100 : 0,
    }));
});

const donutChartSegments = computed(() => {
    let accumulatedPercentage = 0;
    const totalSpent = props.budgets.reduce((sum, b) => sum + b.spent, 0);
    
    if (totalSpent === 0) return [];
    
    return props.budgets.map(b => {
        const percentage = (b.spent / totalSpent) * 100;
        const radius = 35;
        const circumference = 2 * Math.PI * radius;
        const strokeLength = (percentage / 100) * circumference;
        const strokeOffset = circumference - (accumulatedPercentage / 100) * circumference;
        
        accumulatedPercentage += percentage;
        
        return {
            ...b,
            percentage,
            strokeLength,
            strokeOffset,
            circumference,
            color: b.category_color,
        };
    });
});

const financialInsights = computed(() => {
    const income = props.stats.monthly_income || 0;
    const expense = props.stats.monthly_expense || 0;
    
    const expenseRatio = income > 0 ? (expense / income) * 100 : 0;
    let healthStatus = 'Mulai Catat Transaksi';
    let healthColor = 'text-gray-500 bg-gray-50 dark:bg-zinc-800/40 border border-gray-100 dark:border-zinc-800/50';
    let healthAdvice = 'Silakan mulai catat pemasukan dan pengeluaran Anda bulan ini untuk mendapatkan analisis kesehatan keuangan.';

    if (income > 0) {
        if (expenseRatio <= 50) {
            healthStatus = 'Sangat Sehat (Prima) 🌟';
            healthColor = 'text-emerald-600 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/30';
            healthAdvice = 'Luar biasa! Pengeluaran Anda di bawah 50% dari pendapatan. Anda berada di jalur keuangan yang sangat aman dan dapat mengalokasikan sisa dana untuk tabungan hari tua atau investasi.';
        } else if (expenseRatio <= 70) {
            healthStatus = 'Cukup Sehat 👍';
            healthColor = 'text-amber-600 bg-amber-50 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-900/30';
            healthAdvice = 'Keuangan Anda dalam kondisi stabil. Pengeluaran berkisar antara 50%-70% dari pendapatan. Cobalah kurangi pengeluaran keinginan (want) agar bisa mencapai rasio emas 50% untuk kebutuhan utama.';
        } else {
            healthStatus = 'Waspada (Overspending) ⚠️';
            healthColor = 'text-rose-600 bg-rose-50 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/30';
            healthAdvice = 'Waspada! Pengeluaran Anda melebihi 70% pendapatan Anda. Hal ini berisiko membuat Anda kesulitan menabung atau bahkan terpaksa berhutang. Segera evaluasi dan pangkas pos pengeluaran non-esensial.';
        }
    }

    const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const spendingByDay: Record<number, number> = {0:0, 1:0, 2:0, 3:0, 4:0, 5:0, 6:0};
    
    (props.recentTransactions || []).forEach(t => {
        if (t.type === 'expense') {
            const d = new Date(t.date);
            spendingByDay[d.getDay()] += parseFloat(t.amount);
        }
    });

    let peakDayIndex = -1;
    let peakDayAmount = 0;
    for (let i = 0; i < 7; i++) {
        if (spendingByDay[i] > peakDayAmount) {
            peakDayAmount = spendingByDay[i];
            peakDayIndex = i;
        }
    }

    const peakDayName = peakDayIndex !== -1 ? dayNames[peakDayIndex] : 'Belum Ada';

    let largestCatName = 'Belum Ada';
    let largestCatSpent = 0;
    (props.budgets || []).forEach(b => {
        if (b.spent > largestCatSpent) {
            largestCatSpent = b.spent;
            largestCatName = b.category_name;
        }
    });

    const suggestedNeeds = Math.round(income * 0.5);
    const suggestedWants = Math.round(income * 0.3);
    const suggestedSavings = Math.round(income * 0.2);

    return {
        expenseRatio,
        healthStatus,
        healthColor,
        healthAdvice,
        peakDayName,
        peakDayAmount,
        largestCatName,
        largestCatSpent,
        suggestedNeeds,
        suggestedWants,
        suggestedSavings,
    };
});

const showOnboardingGuide = ref(true);

const dismissOnboarding = () => {
    showOnboardingGuide.value = false;
    if (typeof window !== 'undefined') {
        localStorage.setItem('kaskita_dismissed_onboarding', 'true');
    }
};

onMounted(() => {
    if (typeof window !== 'undefined') {
        const dismissed = localStorage.getItem('kaskita_dismissed_onboarding');
        if (dismissed === 'true') {
            showOnboardingGuide.value = false;
        }

        const urlParams = new URLSearchParams(window.location.search);
        const action = urlParams.get('action');
        if (action === 'income' || action === 'expense' || action === 'transfer') {
            openTransactionModal(action);
            const url = new URL(window.location.href);
            url.searchParams.delete('action');
            window.history.replaceState({}, '', url);
        }
    }
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Dashboard" />

        <div class="dashboard-container">
            <!-- ─── Smart Onboarding Guide Banner ─── -->
            <div 
                v-if="showOnboardingGuide" 
                class="relative overflow-hidden bg-gradient-to-r from-teal-500/10 via-emerald-500/5 to-indigo-500/10 dark:from-teal-950/20 dark:via-zinc-900/40 dark:to-indigo-950/20 border border-teal-100/40 dark:border-zinc-800 rounded-3xl p-6 shadow-sm mb-6 transition-all duration-300"
            >
                <!-- Decorative blurred orbs -->
                <div class="absolute -top-12 -left-12 w-32 h-32 bg-teal-400/10 rounded-full blur-2xl pointer-events-none"></div>
                <div class="absolute -bottom-12 -right-12 w-32 h-32 bg-indigo-400/10 rounded-full blur-2xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                    <div class="space-y-2 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="flex h-2 w-2 rounded-full bg-teal-500 animate-ping"></span>
                            <h3 class="text-sm font-black text-teal-800 dark:text-teal-400 flex items-center gap-1.5 uppercase tracking-wider">
                                💡 Alur Cerdas Keuangan KasKita
                            </h3>
                        </div>
                        <p class="text-xs text-gray-600 dark:text-gray-400 font-medium leading-relaxed max-w-3xl">
                            Untuk mendapatkan hasil analisis keuangan dan budgeting yang maksimal di KasKita, ikuti alur emas 3-langkah berikut ini secara berurutan:
                        </p>
                    </div>
                    <button 
                        @click="dismissOnboarding" 
                        class="p-1.5 rounded-full hover:bg-gray-100 dark:hover:bg-zinc-800 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition cursor-pointer"
                        title="Tutup Panduan"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <!-- 3 Steps Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6 relative z-10">
                    <!-- Step 1 -->
                    <div class="bg-white/80 dark:bg-zinc-900/50 backdrop-blur-md rounded-2xl p-4 border border-teal-100/50 dark:border-zinc-800/80 hover:shadow-md transition duration-200 flex gap-3.5 items-start">
                        <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-extrabold flex items-center justify-center text-xs shrink-0">
                            1
                        </div>
                        <div class="space-y-1">
                            <h4 class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-1">
                                Catat Pemasukan
                            </h4>
                            <p class="text-[10px] text-gray-500 dark:text-gray-400 font-medium leading-relaxed">
                                Catat gaji atau pendapatan awal bulan di tombol <strong>Pemasukan</strong> untuk mengisi saldo dompet Anda.
                            </p>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="bg-white/80 dark:bg-zinc-900/50 backdrop-blur-md rounded-2xl p-4 border border-teal-100/50 dark:border-zinc-800/80 hover:shadow-md transition duration-200 flex gap-3.5 items-start">
                        <div class="w-8 h-8 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 font-extrabold flex items-center justify-center text-xs shrink-0">
                            2
                        </div>
                        <div class="space-y-1">
                            <h4 class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-1">
                                Atur Anggaran (Budget)
                            </h4>
                            <p class="text-[10px] text-gray-500 dark:text-gray-400 font-medium leading-relaxed">
                                Alokasikan pendapatan tersebut ke batas pengeluaran kategori di menu <strong>Budgeting</strong> sebelum berbelanja.
                            </p>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="bg-white/80 dark:bg-zinc-900/50 backdrop-blur-md rounded-2xl p-4 border border-teal-100/50 dark:border-zinc-800/80 hover:shadow-md transition duration-200 flex gap-3.5 items-start">
                        <div class="w-8 h-8 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400 font-extrabold flex items-center justify-center text-xs shrink-0">
                            3
                        </div>
                        <div class="space-y-1">
                            <h4 class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-1">
                                Catat Belanja & Unduh PDF
                            </h4>
                            <p class="text-[10px] text-gray-500 dark:text-gray-400 font-medium leading-relaxed">
                                Catat pengeluaran harian, dan unduh laporan PDF bulanan bergrafis premium untuk dianalisis oleh AI lain!
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ─── Hero Balance Card ─── -->
            <div class="balance-hero">
                <div class="balance-hero-card">
                    <div class="balance-hero-bg"></div>
                    <div class="balance-hero-content">
                        <div class="balance-hero-top">
                            <div class="balance-hero-label">
                                <WalletIcon class="h-4 w-4 opacity-70" />
                                <span>Total Saldo</span>
                            </div>
                            <button
                                @click="showBalance = !showBalance"
                                class="balance-eye-btn"
                            >
                                <Eye v-if="showBalance" class="h-4 w-4" />
                                <EyeOff v-else class="h-4 w-4" />
                            </button>
                        </div>

                        <div class="balance-hero-amount">
                            {{ maskedBalance(stats.total_balance) }}
                        </div>

                        <div class="balance-hero-stats">
                            <div class="balance-stat balance-stat--income">
                                <ArrowUpRight class="h-3.5 w-3.5" />
                                <span>{{ showBalance ? formatCurrency(stats.monthly_income) : '•••' }}</span>
                            </div>
                            <div class="balance-stat-divider"></div>
                            <div class="balance-stat balance-stat--expense">
                                <ArrowDownRight class="h-3.5 w-3.5" />
                                <span>{{ showBalance ? formatCurrency(stats.monthly_expense) : '•••' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Decorative elements -->
                    <div class="balance-hero-orb balance-hero-orb--1"></div>
                    <div class="balance-hero-orb balance-hero-orb--2"></div>
                </div>
            </div>

            <!-- ─── Quick Actions ─── -->
            <div class="quick-actions">
                <button @click="openTransactionModal('income')" class="quick-action-item">
                    <div class="quick-action-icon quick-action-icon--income">
                        <ArrowUpRight class="h-5 w-5" />
                    </div>
                    <span class="quick-action-label">Pemasukan</span>
                </button>
                <button @click="openTransactionModal('expense')" class="quick-action-item">
                    <div class="quick-action-icon quick-action-icon--expense">
                        <ArrowDownRight class="h-5 w-5" />
                    </div>
                    <span class="quick-action-label">Pengeluaran</span>
                </button>
                <button @click="openTransactionModal('transfer')" class="quick-action-item">
                    <div class="quick-action-icon quick-action-icon--transfer">
                        <Sparkles class="h-5 w-5" />
                    </div>
                    <span class="quick-action-label">Transfer</span>
                </button>
                <Link href="/activity" class="quick-action-item">
                    <div class="quick-action-icon quick-action-icon--history">
                        <History class="h-5 w-5" />
                    </div>
                    <span class="quick-action-label">Riwayat</span>
                </Link>
            </div>

            <!-- ─── Charts Section ─── -->
            <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-gray-100 dark:border-zinc-800 p-6 shadow-sm mb-6 space-y-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-2">
                            📊 Analisis Keuangan Keluarga
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pantau dan kelola arus kas keluarga Anda secara interaktif</p>
                    </div>

                    <!-- Tab Switcher -->
                    <div class="flex bg-gray-100 dark:bg-zinc-800 p-1 rounded-xl self-stretch sm:self-auto">
                        <button 
                            @click="activeChartTab = 'cashflow'" 
                            :class="[
                                'flex-1 sm:flex-initial px-4 py-2 rounded-lg text-xs font-bold transition cursor-pointer',
                                activeChartTab === 'cashflow' 
                                    ? 'bg-white dark:bg-zinc-700 text-gray-900 dark:text-white shadow-sm' 
                                    : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'
                            ]"
                        >
                            📈 Tren Cashflow
                        </button>
                        <button 
                            @click="activeChartTab = 'categories'" 
                            :class="[
                                'flex-1 sm:flex-initial px-4 py-2 rounded-lg text-xs font-bold transition cursor-pointer',
                                activeChartTab === 'categories' 
                                    ? 'bg-white dark:bg-zinc-700 text-gray-900 dark:text-white shadow-sm' 
                                    : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'
                            ]"
                        >
                            🍕 Alokasi Kategori
                        </button>
                        <button 
                            @click="activeChartTab = 'ai'" 
                            :class="[
                                'flex-1 sm:flex-initial px-4 py-2 rounded-lg text-xs font-bold transition cursor-pointer',
                                activeChartTab === 'ai' 
                                    ? 'bg-white dark:bg-zinc-700 text-gray-900 dark:text-white shadow-sm' 
                                    : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'
                            ]"
                        >
                            🤖 AI Rekomendasi
                        </button>
                    </div>
                </div>

                <!-- Tab 1: Cashflow Trend Chart -->
                <div v-show="activeChartTab === 'cashflow'" class="space-y-4">
                    <!-- Legend -->
                    <div class="flex items-center gap-4 text-xs font-semibold">
                        <div class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400">
                            <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                            <span>Pemasukan</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-rose-600 dark:text-rose-400">
                            <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                            <span>Pengeluaran</span>
                        </div>
                    </div>

                    <!-- Bars Visualizer -->
                    <div class="grid grid-cols-7 gap-2 sm:gap-4 h-48 pt-4 items-end border-b border-gray-100 dark:border-zinc-800">
                        <div 
                            v-for="day in weeklyChartData" 
                            :key="day.date" 
                            class="flex flex-col items-center h-full justify-end group relative"
                        >
                            <!-- Tooltip on Hover -->
                            <div class="absolute bottom-full mb-2 bg-gray-900 text-white text-[10px] p-2 rounded-lg opacity-0 group-hover:opacity-100 transition duration-200 pointer-events-none z-10 shadow-lg whitespace-nowrap min-w-32 space-y-0.5">
                                <p class="font-extrabold text-gray-300 border-b border-gray-800 pb-1 mb-1">{{ day.label }} ({{ formatDate(day.date) }})</p>
                                <p class="text-emerald-400 font-extrabold">🟢 +{{ formatCurrency(day.income) }}</p>
                                <p class="text-rose-400 font-extrabold">🔴 -{{ formatCurrency(day.expense) }}</p>
                            </div>

                            <!-- Bars Container -->
                            <div class="flex items-end gap-1 sm:gap-2 w-full h-full justify-center">
                                <!-- Income Bar -->
                                <div 
                                    class="w-2 sm:w-3 bg-gradient-to-t from-emerald-500 to-teal-400 rounded-t-md transition-all duration-500 ease-out hover:opacity-80"
                                    :style="{ height: `${day.incomeHeight}%` }"
                                ></div>
                                <!-- Expense Bar -->
                                <div 
                                    class="w-2 sm:w-3 bg-gradient-to-t from-rose-500 to-orange-400 rounded-t-md transition-all duration-500 ease-out hover:opacity-80"
                                    :style="{ height: `${day.expenseHeight}%` }"
                                ></div>
                            </div>

                            <!-- X-Axis Label -->
                            <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400 mt-2 truncate max-w-full">
                                {{ day.label }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Allocation Donut Chart -->
                <div v-show="activeChartTab === 'categories'" class="flex flex-col md:flex-row items-center justify-center gap-8 md:gap-16 py-4">
                    <div v-if="donutChartSegments.length > 0" class="relative w-44 h-44 shrink-0 flex items-center justify-center">
                        <svg class="w-full h-full -rotate-90" viewBox="0 0 100 100">
                            <!-- Background Circle -->
                            <circle cx="50" cy="50" r="35" fill="transparent" class="stroke-gray-100 dark:stroke-zinc-800" stroke-width="12" />
                            <!-- Segment Circles -->
                            <circle 
                                v-for="seg in donutChartSegments"
                                :key="seg.id"
                                cx="50" 
                                cy="50" 
                                r="35" 
                                fill="transparent" 
                                :stroke="seg.color" 
                                stroke-width="12" 
                                :stroke-dasharray="seg.circumference" 
                                :stroke-dashoffset="seg.strokeOffset"
                                class="transition-all duration-500 hover:scale-105 origin-center cursor-pointer"
                                :title="`${seg.category_name}: ${seg.percentage.toFixed(1)}%`"
                            />
                        </svg>
                        <div class="absolute flex flex-col items-center justify-center text-center">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total</span>
                            <span class="text-xs font-black text-gray-900 dark:text-white mt-0.5 truncate max-w-[120px]">
                                {{ formatCurrency(budgets.reduce((sum, b) => sum + b.spent, 0)) }}
                            </span>
                        </div>
                    </div>

                    <!-- Category Legend List -->
                    <div class="flex-1 w-full space-y-3">
                        <div v-if="budgets.length === 0" class="text-center py-6 text-xs font-semibold text-gray-400">
                            Belum ada anggaran pengeluaran tercatat bulan ini.
                        </div>
                        <div 
                            v-else
                            v-for="b in budgets" 
                            :key="b.id" 
                            class="flex items-center justify-between p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-zinc-800/40 transition"
                        >
                            <div class="flex items-center gap-3">
                                <span class="w-3.5 h-3.5 rounded-full shrink-0" :style="{ backgroundColor: b.category_color }"></span>
                                <span class="text-xs font-extrabold text-gray-700 dark:text-gray-300">{{ b.category_name }}</span>
                            </div>
                            <div class="flex items-center gap-4">
                                <span class="text-xs font-bold text-gray-900 dark:text-white">{{ formatCurrency(b.spent) }}</span>
                                <span class="text-[10px] font-extrabold bg-gray-100 dark:bg-zinc-800 px-2 py-0.5 rounded-md text-gray-500 dark:text-gray-400 shrink-0">
                                    {{ budgets.reduce((sum, x) => sum + x.spent, 0) > 0 ? ((b.spent / budgets.reduce((sum, x) => sum + x.spent, 0)) * 100).toFixed(0) : 0 }}%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Tab 3: KasKita AI Financial Audit Section -->
                <div v-show="activeChartTab === 'ai'" class="bg-gradient-to-br from-violet-600 to-indigo-700 rounded-3xl p-6 text-white shadow-lg space-y-5 relative overflow-hidden mt-2">
                    <!-- Background Glowing Orbs -->
                    <div class="absolute -top-10 -right-10 w-40 h-44 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                    <div class="absolute -bottom-10 -left-10 w-40 h-44 bg-indigo-500/20 rounded-full blur-2xl pointer-events-none"></div>

                    <div class="flex items-center justify-between relative z-10">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white/15 backdrop-blur-md rounded-2xl flex items-center justify-center animate-pulse text-xl">
                                🤖
                            </div>
                            <div>
                                <h3 class="text-sm font-black tracking-tight">KasKita AI - Asisten Keuangan Pintar</h3>
                                <p class="text-[9px] text-indigo-200 font-semibold mt-0.5">Analisis Kesehatan Keuangan Berdasarkan Aturan Emas Finansial</p>
                            </div>
                        </div>
                    </div>

                    <!-- Main Analysis Metrics -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 relative z-10">
                        <!-- Health Audit Status -->
                        <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/15 space-y-1">
                            <span class="text-[9px] font-black uppercase tracking-wider text-indigo-200">Kesehatan Arus Kas</span>
                            <div class="text-sm font-black pt-1">{{ financialInsights.healthStatus }}</div>
                            <div class="text-[10px] text-white/80 font-medium">Rasio Belanja: {{ financialInsights.expenseRatio.toFixed(1) }}%</div>
                        </div>

                        <!-- Peak Spending Day -->
                        <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/15 space-y-1">
                            <span class="text-[9px] font-black uppercase tracking-wider text-indigo-200">Hari Pengeluaran Tertinggi</span>
                            <div class="text-sm font-black pt-1">🎬 {{ financialInsights.peakDayName }}</div>
                            <div class="text-[10px] text-white/80 font-medium" v-if="financialInsights.peakDayAmount > 0">
                                Terbelanja: {{ formatCurrency(financialInsights.peakDayAmount) }}
                            </div>
                            <div class="text-[10px] text-white/80 font-medium" v-else>Tidak ada pengeluaran</div>
                        </div>

                        <!-- Top Expense Category -->
                        <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/15 space-y-1">
                            <span class="text-[9px] font-black uppercase tracking-wider text-indigo-200">Kategori Terboros</span>
                            <div class="text-sm font-black pt-1 truncate">🏷️ {{ financialInsights.largestCatName }}</div>
                            <div class="text-[10px] text-white/80 font-medium" v-if="financialInsights.largestCatSpent > 0">
                                Total: {{ formatCurrency(financialInsights.largestCatSpent) }}
                            </div>
                            <div class="text-[10px] text-white/80 font-medium" v-else>Belum ada data</div>
                        </div>
                    </div>

                    <!-- AI Advice Callout Box -->
                    <div class="bg-white/15 backdrop-blur-md rounded-2xl p-4 border border-white/10 relative z-10 space-y-2">
                        <p class="text-[10px] font-black uppercase tracking-widest text-indigo-100 flex items-center gap-1.5">
                            💡 Rekomendasi Finansial Anda
                        </p>
                        <p class="text-xs font-semibold leading-relaxed text-indigo-50">
                            {{ financialInsights.healthAdvice }}
                        </p>
                    </div>

                    <!-- Financial Golden Rule Aligner (50/30/20 Rule recommendation) -->
                    <div v-if="stats.monthly_income > 0" class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/10 relative z-10 space-y-3">
                        <p class="text-[10px] font-black uppercase tracking-widest text-indigo-100">
                            📏 Target Alokasi Sesuai Aturan Emas (50/30/20):
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <!-- Kebutuhan Pokok -->
                            <div class="flex sm:flex-col justify-between sm:justify-center items-center sm:items-center bg-white/5 sm:bg-transparent px-3 py-2 rounded-xl sm:p-0 space-y-0 sm:space-y-0.5">
                                <p class="text-[9px] font-black text-indigo-200">Kebutuhan Pokok (50%)</p>
                                <p class="text-xs font-black">{{ formatCurrency(financialInsights.suggestedNeeds) }}</p>
                            </div>
                            <!-- Keinginan -->
                            <div class="flex sm:flex-col justify-between sm:justify-center items-center sm:items-center bg-white/5 sm:bg-transparent px-3 py-2 rounded-xl sm:p-0 space-y-0 sm:space-y-0.5">
                                <p class="text-[9px] font-black text-indigo-200">Keinginan (30%)</p>
                                <p class="text-xs font-black">{{ formatCurrency(financialInsights.suggestedWants) }}</p>
                            </div>
                            <!-- Tabungan/Investasi -->
                            <div class="flex sm:flex-col justify-between sm:justify-center items-center sm:items-center bg-white/5 sm:bg-transparent px-3 py-2 rounded-xl sm:p-0 space-y-0 sm:space-y-0.5">
                                <p class="text-[9px] font-black text-indigo-200">Tabungan/Investasi (20%)</p>
                                <p class="text-xs font-black text-emerald-300">{{ formatCurrency(financialInsights.suggestedSavings) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dashboard-grid">
                <!-- ─── Wallets Section ─── -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h3 class="section-title">Dompet Saya</h3>
                        <button @click="openWalletModal" class="section-link text-emerald-600 font-bold">+ Tambah</button>
                    </div>

                    <div class="wallet-list">
                        <div
                            v-for="wallet in wallets"
                            :key="wallet.id"
                            class="wallet-card"
                        >
                            <div class="wallet-card-left">
                                <div
                                    class="wallet-icon-wrap"
                                    :style="{ backgroundColor: wallet.color + '15', color: wallet.color }"
                                >
                                    <span class="wallet-emoji">{{ getWalletEmoji(wallet.type) }}</span>
                                </div>
                                <div class="wallet-info">
                                    <span class="wallet-name">{{ wallet.name }}</span>
                                    <span class="wallet-type">{{ wallet.type }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="wallet-balance">
                                    {{ showBalance ? formatCurrency(wallet.balance) : '•••••' }}
                                </div>
                                <button @click="deleteWallet(wallet)" class="text-red-500 opacity-60 hover:opacity-100 transition p-1" title="Hapus Dompet">
                                    <Trash2 class="h-3.5 w-3.5" />
                                </button>
                            </div>
                        </div>

                        <div v-if="wallets.length === 0" class="empty-state">
                            <div class="empty-state-icon">
                                <WalletIcon class="h-8 w-8" />
                            </div>
                            <p class="empty-state-text">Belum ada dompet</p>
                            <Button @click="openWalletModal" size="sm" class="empty-state-btn">
                                <Plus class="mr-1 h-4 w-4" />
                                Tambah Dompet
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- ─── Budget Overview ─── -->
                <div v-if="budgets && budgets.length > 0" class="dashboard-section" style="grid-column: 1 / -1;">
                    <div class="section-header">
                        <h3 class="section-title">💰 Budget Bulan Ini</h3>
                        <Link href="/budget" class="section-link">Lihat Semua</Link>
                    </div>
                    <div class="budget-overview-grid">
                        <div v-for="b in budgets" :key="b.id" class="budget-mini-card">
                            <div class="budget-mini-top">
                                <div class="budget-mini-dot" :style="{ background: b.category_color }"></div>
                                <span class="budget-mini-name">{{ b.category_name }}</span>
                                <span class="budget-mini-pct" :style="{ color: b.status === 'over' ? '#dc2626' : b.status === 'danger' ? '#ef4444' : b.status === 'warning' ? '#d97706' : '#059669' }">{{ b.percentage }}%</span>
                            </div>
                            <div class="budget-mini-bar">
                                <div class="budget-mini-bar-fill" :style="{ width: Math.min(b.percentage, 100) + '%', background: b.status === 'over' ? '#dc2626' : b.status === 'danger' ? '#ef4444' : b.status === 'warning' ? '#f59e0b' : '#059669' }"></div>
                            </div>
                            <div class="budget-mini-bottom">
                                <span>{{ formatCurrency(b.spent) }}</span>
                                <span>{{ formatCurrency(b.amount) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ─── Transactions Section ─── -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h3 class="section-title">Aktivitas Terakhir</h3>
                        <button class="section-link">Riwayat</button>
                    </div>

                    <div class="tx-list">
                        <div
                            v-for="tx in recentTransactions"
                            :key="tx.id"
                            class="tx-card"
                        >
                            <div class="tx-card-left">
                                <div
                                    class="tx-icon-wrap"
                                    :style="{ backgroundColor: (tx.category?.color || '#71717a') + '12' }"
                                >
                                    <History
                                        class="h-5 w-5"
                                        :style="{ color: tx.category?.color || '#71717a' }"
                                    />
                                </div>
                                <div class="tx-info">
                                    <span class="tx-name">{{ tx.category?.name || 'Lainnya' }}</span>
                                    <span class="tx-note">{{ tx.note || 'Transaksi KasKita' }}</span>
                                </div>
                            </div>
                            <div class="tx-card-right">
                                <span
                                    :class="[
                                        'tx-amount',
                                        tx.type === 'income' ? 'tx-amount--income' : 'tx-amount--expense',
                                    ]"
                                >
                                    {{ tx.type === 'income' ? '+' : '-' }}{{ formatCurrency(Number(tx.amount)).replace('Rp', '') }}
                                </span>
                                <span class="tx-date">{{ formatDate(tx.date) }}</span>
                            </div>
                        </div>

                        <div v-if="recentTransactions.length === 0" class="empty-state">
                            <div class="empty-state-icon">
                                <TrendingUp class="h-8 w-8" />
                            </div>
                            <p class="empty-state-text">
                                Mulai catat pengeluaranmu hari ini!
                            </p>
                            <Button size="sm" class="empty-state-btn">
                                <Plus class="mr-1 h-4 w-4" />
                                Catat Transaksi
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Create Transaction Modal -->
        <Teleport to="body">
            <div v-if="showTransactionModal" class="transaction-modal-overlay" @click.self="showTransactionModal = false">
                <div class="transaction-modal">
                    <div class="transaction-modal-header">
                        <h3 class="transaction-modal-title">
                            Catat {{ transactionModalType === 'income' ? 'Pemasukan' : (transactionModalType === 'expense' ? 'Pengeluaran' : 'Transfer') }} Baru
                        </h3>
                        <button @click="showTransactionModal = false" class="transaction-modal-close">
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <form @submit.prevent="submitTransaction" class="transaction-modal-form">
                        <!-- Amount -->
                        <div class="transaction-form-group">
                            <label class="transaction-form-label">Jumlah (Rp)</label>
                            <input 
                                v-model="transactionForm.amount" 
                                type="number" 
                                min="1" 
                                class="transaction-form-input" 
                                placeholder="e.g. 50000" 
                                required 
                            />
                            <p v-if="transactionForm.errors.amount" class="transaction-form-error">{{ transactionForm.errors.amount }}</p>
                        </div>

                        <!-- Source Wallet -->
                        <div class="transaction-form-group">
                            <label class="transaction-form-label">
                                {{ transactionModalType === 'transfer' ? 'Dari Dompet (Asal)' : 'Dompet' }}
                            </label>
                            <select v-model="transactionForm.wallet_id" class="transaction-form-input" required>
                                <option v-for="w in wallets" :key="w.id" :value="w.id">
                                    {{ w.name }} (Sisa: {{ formatCurrency(w.balance) }})
                                </option>
                            </select>
                            <p v-if="transactionForm.errors.wallet_id" class="transaction-form-error">{{ transactionForm.errors.wallet_id }}</p>
                        </div>

                        <!-- Target Wallet (only for transfers) -->
                        <div v-if="transactionModalType === 'transfer'" class="transaction-form-group">
                            <label class="transaction-form-label">Ke Dompet (Tujuan)</label>
                            <select v-model="transactionForm.transfer_to_wallet_id" class="transaction-form-input" required>
                                <option v-for="w in wallets" :key="w.id" :value="w.id">
                                    {{ w.name }} (Sisa: {{ formatCurrency(w.balance) }})
                                </option>
                            </select>
                            <p v-if="transactionForm.errors.transfer_to_wallet_id" class="transaction-form-error">{{ transactionForm.errors.transfer_to_wallet_id }}</p>
                        </div>

                        <!-- Category (only for income/expense) -->
                        <div v-if="transactionModalType !== 'transfer'" class="transaction-form-group">
                            <label class="transaction-form-label">Kategori</label>
                            <select v-model="transactionForm.category_id" class="transaction-form-input" required>
                                <option value="" disabled>Pilih Kategori...</option>
                                <option v-for="c in categories.filter(cat => cat.type === transactionModalType)" :key="c.id" :value="c.id">
                                    {{ c.name }}
                                </option>
                            </select>
                            <p v-if="transactionForm.errors.category_id" class="transaction-form-error">{{ transactionForm.errors.category_id }}</p>
                        </div>

                        <!-- Note -->
                        <div class="transaction-form-group">
                            <label class="transaction-form-label">Keterangan / Catatan</label>
                            <input 
                                v-model="transactionForm.note" 
                                type="text" 
                                class="transaction-form-input" 
                                placeholder="e.g. jajan soto, gaji bulanan" 
                            />
                            <p v-if="transactionForm.errors.note" class="transaction-form-error">{{ transactionForm.errors.note }}</p>
                        </div>

                        <!-- Date -->
                        <div class="transaction-form-group">
                            <label class="transaction-form-label">Tanggal</label>
                            <input 
                                v-model="transactionForm.date" 
                                type="date" 
                                class="transaction-form-input" 
                                required 
                            />
                            <p v-if="transactionForm.errors.date" class="transaction-form-error">{{ transactionForm.errors.date }}</p>
                        </div>

                        <button type="submit" :disabled="transactionForm.processing" class="transaction-form-submit">
                            {{ transactionForm.processing ? 'Menyimpan...' : 'Simpan Transaksi' }}
                        </button>
                    </form>
                </div>
            </div>
        </Teleport>

        <!-- Create Wallet Modal -->
        <Teleport to="body">
            <div v-if="showWalletModal" class="transaction-modal-overlay" @click.self="showWalletModal = false">
                <div class="transaction-modal">
                    <div class="transaction-modal-header">
                        <h3 class="transaction-modal-title">Tambah Dompet Baru</h3>
                        <button @click="showWalletModal = false" class="transaction-modal-close">
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <form @submit.prevent="submitWallet" class="transaction-modal-form">
                        <!-- Wallet Name -->
                        <div class="transaction-form-group">
                            <label class="transaction-form-label">Nama Dompet</label>
                            <input 
                                v-model="walletForm.name" 
                                type="text" 
                                class="transaction-form-input" 
                                placeholder="e.g. Bank BCA, Dompet Saku" 
                                required 
                            />
                            <p v-if="walletForm.errors.name" class="transaction-form-error">{{ walletForm.errors.name }}</p>
                        </div>

                        <!-- Wallet Type -->
                        <div class="transaction-form-group">
                            <div class="flex items-center justify-between">
                                <label class="transaction-form-label">Jenis Dompet</label>
                                <button type="button" @click="showManageWalletTypesModal = true" class="text-xs text-emerald-600 font-bold hover:underline">
                                    + Kelola Jenis
                                </button>
                            </div>
                            <select v-model="walletForm.type" class="transaction-form-input" required>
                                <option v-for="type in walletTypes" :key="type.id" :value="type.name">
                                    {{ type.icon }} {{ type.name }}
                                </option>
                            </select>
                            <p v-if="walletForm.errors.type" class="transaction-form-error">{{ walletForm.errors.type }}</p>
                        </div>

                        <!-- Initial Balance -->
                        <div class="transaction-form-group">
                            <label class="transaction-form-label">Saldo Awal (Rp)</label>
                            <input 
                                v-model="walletForm.balance" 
                                type="number" 
                                min="0" 
                                class="transaction-form-input" 
                                placeholder="e.g. 1000000" 
                                required 
                            />
                            <p v-if="walletForm.errors.balance" class="transaction-form-error">{{ walletForm.errors.balance }}</p>
                        </div>

                        <!-- Theme Color -->
                        <div class="transaction-form-group">
                            <label class="transaction-form-label">Warna Tema Dompet</label>
                            <div class="flex gap-2">
                                <input 
                                    v-model="walletForm.color" 
                                    type="color" 
                                    class="w-12 h-10 p-1 border rounded-lg cursor-pointer" 
                                />
                                <input 
                                    v-model="walletForm.color" 
                                    type="text" 
                                    class="transaction-form-input flex-1" 
                                    placeholder="#10b981" 
                                    required 
                                />
                            </div>
                            <p v-if="walletForm.errors.color" class="transaction-form-error">{{ walletForm.errors.color }}</p>
                        </div>

                        <button type="submit" :disabled="walletForm.processing" class="transaction-form-submit">
                            {{ walletForm.processing ? 'Menyimpan...' : 'Tambah Dompet' }}
                        </button>
                    </form>
                </div>
            </div>
        </Teleport>

        <!-- Manage Wallet Types Modal -->
        <Teleport to="body">
            <div v-if="showManageWalletTypesModal" class="transaction-modal-overlay" @click.self="showManageWalletTypesModal = false">
                <div class="transaction-modal">
                    <div class="transaction-modal-header">
                        <h3 class="transaction-modal-title">Kelola Jenis Dompet</h3>
                        <button @click="showManageWalletTypesModal = false" class="transaction-modal-close">
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <!-- Create New Wallet Type Form -->
                    <form @submit.prevent="submitWalletType" class="mb-6 pb-6 border-b border-gray-200 dark:border-gray-700 flex gap-2 items-end">
                        <div class="flex-1 space-y-1">
                            <label class="text-xs font-bold text-gray-500">Emoji Icon</label>
                            <input v-model="walletTypeForm.icon" type="text" class="transaction-form-input text-center" placeholder="🏦" style="padding: 0; width: 46px;" required />
                        </div>
                        <div class="flex-[3] space-y-1">
                            <label class="text-xs font-bold text-gray-500">Nama Jenis Dompet</label>
                            <input v-model="walletTypeForm.name" type="text" class="transaction-form-input" placeholder="e.g. Gopay, Bank Mandiri" required />
                        </div>
                        <button type="submit" :disabled="walletTypeForm.processing" class="transaction-form-submit px-4 h-[46px] text-sm">
                            Tambah
                        </button>
                    </form>

                    <!-- Wallet Types List -->
                    <div class="space-y-2 max-h-64 overflow-y-auto pr-1" style="scrollbar-width: none;">
                        <div v-for="type in walletTypes" :key="type.id" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <span class="text-xl">{{ type.icon }}</span>
                                <span class="font-bold text-sm text-gray-900 dark:text-white">{{ type.name }}</span>
                            </div>
                            <button @click="deleteWalletType(type)" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-xl transition">
                                <Trash2 class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Attractive Custom Confirm Modal -->
        <Teleport to="body">
            <div v-if="confirmModal.show" class="transaction-modal-overlay" @click.self="confirmModal.show = false" style="z-index: 300;">
                <div class="transaction-modal max-w-sm text-center">
                    <div class="text-5xl mb-4 animate-bounce">⚠️</div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">
                        {{ confirmModal.title }}
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 px-2">
                        {{ confirmModal.message }}
                    </p>
                    <div class="flex gap-3">
                        <button
                            @click="executeConfirm"
                            class="flex-1 bg-red-500 hover:bg-red-600 text-white font-bold py-2.5 rounded-xl transition"
                        >
                            Ya, Hapus
                        </button>
                        <button
                            @click="confirmModal.show = false"
                            class="flex-1 bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-medium transition hover:bg-gray-300"
                        >
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>

<style scoped>
/* ─── Container ─── */
.dashboard-container {
    display: flex;
    flex-direction: column;
    gap: 24px;
    padding: 20px 16px;
    max-width: 1200px;
    margin: 0 auto;
    width: 100%;
    padding-bottom: 100px;
}

@media (min-width: 768px) {
    .dashboard-container {
        padding: 32px;
        gap: 28px;
        padding-bottom: 32px;
    }
}

/* ─── Hero Balance Card ─── */
.balance-hero-card {
    position: relative;
    overflow: hidden;
    border-radius: 24px;
    padding: 24px;
    color: white;
}

.balance-hero-bg {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, #064e3b 0%, #065f46 40%, #047857 70%, #059669 100%);
}

.balance-hero-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(60px);
    opacity: 0.3;
}

.balance-hero-orb--1 {
    width: 200px;
    height: 200px;
    background: #34d399;
    top: -60px;
    right: -40px;
}

.balance-hero-orb--2 {
    width: 120px;
    height: 120px;
    background: #6ee7b7;
    bottom: -30px;
    left: 20%;
}

.balance-hero-content {
    position: relative;
    z-index: 10;
}

.balance-hero-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}

.balance-hero-label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    opacity: 0.8;
}

.balance-eye-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.15);
    border: none;
    color: white;
    cursor: pointer;
    transition: background 0.2s;
}

.balance-eye-btn:hover {
    background: rgba(255, 255, 255, 0.25);
}

.balance-hero-amount {
    font-size: 32px;
    font-weight: 800;
    letter-spacing: -0.03em;
    line-height: 1;
    margin-bottom: 20px;
}

@media (min-width: 768px) {
    .balance-hero-amount {
        font-size: 36px;
    }
}

.balance-hero-stats {
    display: flex;
    align-items: center;
    gap: 0;
    padding: 12px 16px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 14px;
    border: 1px solid rgba(255, 255, 255, 0.08);
}

.balance-stat {
    display: flex;
    align-items: center;
    gap: 6px;
    flex: 1;
    font-size: 13px;
    font-weight: 600;
}

.balance-stat--income {
    color: #a7f3d0;
}

.balance-stat--expense {
    color: #fecaca;
}

.balance-stat-divider {
    width: 1px;
    height: 20px;
    background: rgba(255, 255, 255, 0.15);
    margin: 0 12px;
}

/* ─── Quick Actions ─── */
.quick-actions {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
}

.quick-action-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 16px 8px;
    border-radius: 20px;
    background: var(--card);
    border: 1px solid var(--border);
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.quick-action-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
}

.quick-action-item:active {
    transform: scale(0.96);
}

.quick-action-icon {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
}

.quick-action-icon--income {
    background: linear-gradient(135deg, #d1fae5, #a7f3d0);
    color: #047857;
}

.quick-action-icon--expense {
    background: linear-gradient(135deg, #ffe4e6, #fecdd3);
    color: #be123c;
}

.quick-action-icon--transfer {
    background: linear-gradient(135deg, #ede9fe, #ddd6fe);
    color: #7c3aed;
}

.quick-action-icon--history {
    background: linear-gradient(135deg, #e0f2fe, #bae6fd);
    color: #0284c7;
}

:is(.dark) .quick-action-icon--income {
    background: linear-gradient(135deg, rgba(6, 95, 70, 0.3), rgba(4, 120, 87, 0.2));
    color: #6ee7b7;
}

:is(.dark) .quick-action-icon--expense {
    background: linear-gradient(135deg, rgba(159, 18, 57, 0.3), rgba(190, 18, 60, 0.2));
    color: #fda4af;
}

:is(.dark) .quick-action-icon--transfer {
    background: linear-gradient(135deg, rgba(91, 33, 182, 0.3), rgba(124, 58, 237, 0.2));
    color: #c4b5fd;
}

:is(.dark) .quick-action-icon--history {
    background: linear-gradient(135deg, rgba(3, 105, 161, 0.3), rgba(2, 132, 199, 0.2));
    color: #7dd3fc;
}

.quick-action-label {
    font-size: 11px;
    font-weight: 600;
    color: var(--foreground);
    letter-spacing: 0.01em;
}

/* ─── Dashboard Grid ─── */
.dashboard-grid {
    display: grid;
    gap: 24px;
}

@media (min-width: 768px) {
    .dashboard-grid {
        grid-template-columns: 5fr 7fr;
        gap: 28px;
    }
}

/* ─── Section ─── */
.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
    padding: 0 2px;
}

.section-title {
    font-size: 16px;
    font-weight: 700;
    letter-spacing: -0.02em;
    color: var(--foreground);
}

.section-link {
    font-size: 12px;
    font-weight: 600;
    color: #059669;
    background: none;
    border: none;
    cursor: pointer;
    transition: color 0.2s;
}

.section-link:hover {
    color: #047857;
}

:is(.dark) .section-link {
    color: #6ee7b7;
}

/* ─── Wallet Card ─── */
.wallet-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.wallet-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 16px;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 18px;
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.wallet-card:hover {
    transform: translateX(4px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
}

.wallet-card:active {
    transform: scale(0.98);
}

.wallet-card-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.wallet-icon-wrap {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    font-size: 20px;
}

.wallet-info {
    display: flex;
    flex-direction: column;
}

.wallet-name {
    font-size: 14px;
    font-weight: 700;
    color: var(--foreground);
}

.wallet-type {
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--muted-foreground);
}

.wallet-balance {
    font-size: 14px;
    font-weight: 800;
    color: var(--foreground);
    letter-spacing: -0.01em;
}

/* ─── Transaction Card ─── */
.tx-list {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 20px;
    overflow: hidden;
}

.tx-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 16px;
    transition: background 0.15s ease;
    cursor: pointer;
}

.tx-card:not(:last-child) {
    border-bottom: 1px solid var(--border);
}

.tx-card:hover {
    background: var(--accent);
}

.tx-card-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.tx-icon-wrap {
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    flex-shrink: 0;
}

.tx-info {
    display: flex;
    flex-direction: column;
}

.tx-name {
    font-size: 14px;
    font-weight: 700;
    color: var(--foreground);
}

.tx-note {
    font-size: 11px;
    color: var(--muted-foreground);
    max-width: 130px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.tx-card-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

.tx-amount {
    font-size: 14px;
    font-weight: 800;
    letter-spacing: -0.01em;
}

.tx-amount--income {
    color: #059669;
}

:is(.dark) .tx-amount--income {
    color: #6ee7b7;
}

.tx-amount--expense {
    color: #e11d48;
}

:is(.dark) .tx-amount--expense {
    color: #fb7185;
}

.tx-date {
    font-size: 10px;
    font-weight: 500;
    color: var(--muted-foreground);
    margin-top: 2px;
}

/* ─── Empty State ─── */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 48px 24px;
    text-align: center;
}

.empty-state-icon {
    width: 64px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 20px;
    background: var(--accent);
    color: var(--muted-foreground);
    margin-bottom: 16px;
}

.empty-state-text {
    font-size: 14px;
    font-weight: 500;
    color: var(--muted-foreground);
    margin-bottom: 16px;
}

.empty-state-btn {
    border-radius: 12px;
    background: linear-gradient(135deg, #064e3b, #059669);
    color: white;
    font-weight: 600;
}

/* ─── Scrollbar hide ─── */
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

/* ─── Budget Overview ─── */
.budget-overview-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
@media (max-width: 400px) { .budget-overview-grid { grid-template-columns: 1fr; } }
.budget-mini-card { padding: 14px; background: var(--card); border: 1px solid var(--border); border-radius: 16px; }
.budget-mini-top { display: flex; align-items: center; gap: 6px; margin-bottom: 8px; }
.budget-mini-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.budget-mini-name { font-size: 12px; font-weight: 700; color: var(--foreground); flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.budget-mini-pct { font-size: 11px; font-weight: 700; flex-shrink: 0; }
.budget-mini-bar { height: 5px; background: var(--accent); border-radius: 3px; overflow: hidden; margin-bottom: 6px; }
.budget-mini-bar-fill { height: 100%; border-radius: 3px; transition: width 0.5s ease; }
.budget-mini-bottom { display: flex; justify-content: space-between; font-size: 10px; color: var(--muted-foreground); font-weight: 600; }
/* ─── Transaction Modal ─── */
.transaction-modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); display: flex; align-items: flex-end; justify-content: center; z-index: 200; padding: 16px; }
@media (min-width: 640px) { .transaction-modal-overlay { align-items: center; } }
.transaction-modal { background: var(--card); border-radius: 24px 24px 16px 16px; width: 100%; max-width: 440px; padding: 24px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
@media (min-width: 640px) { .transaction-modal { border-radius: 24px; } }
.transaction-modal-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
.transaction-modal-title { font-size: 18px; font-weight: 700; color: var(--foreground); }
.transaction-modal-close { width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 10px; border: none; background: var(--accent); color: var(--muted-foreground); cursor: pointer; }
.transaction-modal-form { display: flex; flex-direction: column; gap: 16px; }
.transaction-form-group { display: flex; flex-direction: column; gap: 6px; }
.transaction-form-label { font-size: 13px; font-weight: 600; color: var(--foreground); }
.transaction-form-input { height: 46px; padding: 0 14px; border: 1px solid var(--border); border-radius: 14px; font-size: 14px; background: var(--background); color: var(--foreground); outline: none; transition: border-color 0.2s; }
.transaction-form-input:focus { border-color: #059669; box-shadow: 0 0 0 3px rgba(5,150,105,0.1); }
.transaction-form-error { font-size: 12px; color: #ef4444; }
.transaction-form-submit { height: 48px; border-radius: 14px; font-size: 15px; font-weight: 600; color: white; background: linear-gradient(135deg, #064e3b, #059669); border: none; cursor: pointer; box-shadow: 0 4px 14px rgba(5,150,105,0.25); transition: all 0.2s; }
.transaction-form-submit:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(5,150,105,0.35); }
.transaction-form-submit:disabled { opacity: 0.6; }
</style>
