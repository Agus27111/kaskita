<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import {
    Plus,
    TrendingUp,
    TrendingDown,
    AlertTriangle,
    X,
    Pencil,
    Trash2,
    Check,
    Target,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

interface Category {
    id: number;
    name: string;
    icon: string | null;
    color: string | null;
}

interface BudgetItem {
    id: number;
    category_id: number;
    category: Category | null;
    amount: number;
    spent: number;
    remaining: number;
    percentage: number;
    status: 'safe' | 'warning' | 'danger' | 'over';
    is_over_budget: boolean;
    period: 'monthly' | 'weekly';
    month: number | null;
    year: number | null;
    week: number | null;
    note: string | null;
    is_recurring: boolean;
}

const props = defineProps<{
    monthlyBudgets: BudgetItem[];
    weeklyBudgets: BudgetItem[];
    categories: Category[];
    filters: { month: number; year: number; week: number };
    summary: {
        total_budget: number;
        total_spent: number;
        total_remaining: number;
        monthly_income: number;
        budget_vs_income: number;
    };
}>();

const breadcrumbs = [{ title: 'Budget', href: '/budget' }];
const showModal = ref(false);
const editingBudget = ref<BudgetItem | null>(null);
const activeTab = ref<'monthly' | 'weekly'>('monthly');

const form = useForm({
    category_id: '' as string | number,
    new_category_name: '',
    amount: '' as string | number,
    period: 'monthly' as 'monthly' | 'weekly',
    note: '',
});

const formatCurrency = (v: number) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(v);

const monthNames = [
    'Jan',
    'Feb',
    'Mar',
    'Apr',
    'Mei',
    'Jun',
    'Jul',
    'Agu',
    'Sep',
    'Okt',
    'Nov',
    'Des',
];

const statusColors: Record<string, { bg: string; text: string; bar: string }> =
    {
        safe: { bg: 'rgba(5,150,105,0.08)', text: '#059669', bar: '#059669' },
        warning: {
            bg: 'rgba(245,158,11,0.08)',
            text: '#d97706',
            bar: '#f59e0b',
        },
        danger: { bg: 'rgba(239,68,68,0.08)', text: '#dc2626', bar: '#ef4444' },
        over: { bg: 'rgba(239,68,68,0.12)', text: '#dc2626', bar: '#dc2626' },
    };

const statusLabel: Record<string, string> = {
    safe: 'Aman',
    warning: 'Hati-hati',
    danger: 'Hampir Habis',
    over: 'Melebihi Budget',
};

const overallPct = computed(() => {
    if (props.summary.total_budget === 0) {
        return 0;
    }

    return Math.min(
        100,
        Math.round(
            (props.summary.total_spent / props.summary.total_budget) * 100,
        ),
    );
});

const overallStatus = computed(() => {
    const p = overallPct.value;

    if (p > 100) {
        return 'over';
    }

    if (p >= 80) {
        return 'danger';
    }

    if (p >= 60) {
        return 'warning';
    }

    return 'safe';
});

const usedCategories = computed(() => {
    const ids = new Set([
        ...props.monthlyBudgets.map((b) => b.category_id),
        ...props.weeklyBudgets.map((b) => b.category_id),
    ]);

    return ids;
});

const availableCategories = computed(() =>
    props.categories.filter((c) => {
        if (editingBudget.value) {
            return true;
        }

        return !usedCategories.value.has(c.id);
    }),
);

function openCreate() {
    editingBudget.value = null;
    form.reset();
    form.period = activeTab.value;
    showModal.value = true;
}

function openEdit(budget: BudgetItem) {
    editingBudget.value = budget;
    form.category_id = budget.category_id;
    form.amount = budget.amount;
    form.period = budget.period;
    form.note = budget.note || '';
    showModal.value = true;
}

function submit() {
    if (editingBudget.value) {
        form.put(`/budget/${editingBudget.value.id}`, {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    } else {
        form.post('/budget', {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    }
}

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

function deleteBudget(id: number) {
    showConfirm(
        'Hapus Rencana Budget?',
        'Apakah Anda yakin ingin menghapus rencana budget ini? Tindakan ini tidak dapat dibatalkan.',
        () => {
            router.delete(`/budget/${id}`);
        },
    );
}

const showManageCategories = ref(false);

function deleteCategory(category: Category) {
    showConfirm(
        'Hapus Kategori?',
        `Apakah Anda yakin ingin menghapus kategori "${category.name}"? Semua rencana budget yang terhubung dengan kategori ini akan ikut terhapus dari sistem.`,
        () => {
            router.delete(`/categories/${category.id}`);
        },
    );
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Budget" />

        <div class="budget-page">
            <!-- Summary Hero -->
            <div class="budget-hero">
                <div class="budget-hero-bg"></div>
                <div class="budget-hero-orb budget-hero-orb--1"></div>
                <div class="budget-hero-orb budget-hero-orb--2"></div>
                <div class="budget-hero-content">
                    <div class="budget-hero-top">
                        <div class="budget-hero-label">
                            <Target class="h-4 w-4 opacity-70" />
                            <span
                                >Budget
                                {{ monthNames[(filters.month || 1) - 1] }}
                                {{ filters.year }}</span
                            >
                        </div>
                    </div>

                    <div class="budget-hero-amount">
                        {{ formatCurrency(summary.total_budget) }}
                    </div>

                    <div class="budget-hero-bar-wrap">
                        <div class="budget-hero-bar">
                            <div
                                class="budget-hero-bar-fill"
                                :style="{
                                    width: Math.min(overallPct, 100) + '%',
                                    background: statusColors[overallStatus].bar,
                                }"
                            ></div>
                        </div>
                        <span class="budget-hero-bar-label"
                            >{{ overallPct }}% terpakai</span
                        >
                    </div>

                    <div class="budget-hero-stats">
                        <div class="budget-hero-stat">
                            <TrendingDown class="h-3.5 w-3.5" />
                            <span
                                >Terpakai:
                                {{ formatCurrency(summary.total_spent) }}</span
                            >
                        </div>
                        <div class="budget-hero-stat-divider"></div>
                        <div class="budget-hero-stat">
                            <TrendingUp class="h-3.5 w-3.5" />
                            <span
                                >Sisa:
                                {{
                                    formatCurrency(summary.total_remaining)
                                }}</span
                            >
                        </div>
                    </div>

                    <div
                        v-if="summary.monthly_income > 0"
                        class="budget-income-note"
                    >
                        💰 Penghasilan bulan ini:
                        {{ formatCurrency(summary.monthly_income) }} — Budget
                        menggunakan {{ summary.budget_vs_income }}% dari
                        penghasilan
                    </div>
                </div>
            </div>

            <!-- Tab Switch -->
            <div class="budget-tabs">
                <button
                    :class="[
                        'budget-tab',
                        activeTab === 'monthly' && 'budget-tab--active',
                    ]"
                    @click="activeTab = 'monthly'"
                >
                    Bulanan
                </button>
                <button
                    :class="[
                        'budget-tab',
                        activeTab === 'weekly' && 'budget-tab--active',
                    ]"
                    @click="activeTab = 'weekly'"
                >
                    Mingguan
                </button>
            </div>

            <!-- Budget List -->
            <div class="budget-section">
                <div class="budget-section-header">
                    <h3 class="budget-section-title">
                        {{
                            activeTab === 'monthly'
                                ? 'Budget Bulanan'
                                : 'Budget Mingguan'
                        }}
                    </h3>
                    <div class="flex gap-2">
                        <button
                            class="budget-add-btn"
                            style="
                                background: var(--accent);
                                color: var(--muted-foreground);
                            "
                            @click="showManageCategories = true"
                        >
                            <Trash2 class="h-4 w-4" />
                            Kategori
                        </button>
                        <button class="budget-add-btn" @click="openCreate">
                            <Plus class="h-4 w-4" />
                            Tambah
                        </button>
                    </div>
                </div>

                <div class="budget-list">
                    <div
                        v-for="budget in activeTab === 'monthly'
                            ? monthlyBudgets
                            : weeklyBudgets"
                        :key="budget.id"
                        class="budget-card"
                    >
                        <div class="budget-card-top">
                            <div class="budget-card-left">
                                <div
                                    class="budget-card-icon"
                                    :style="{
                                        backgroundColor:
                                            (budget.category?.color ||
                                                '#71717a') + '15',
                                        color:
                                            budget.category?.color || '#71717a',
                                    }"
                                >
                                    <PiggyBank class="h-5 w-5" />
                                </div>
                                <div class="budget-card-info">
                                    <span class="budget-card-name">{{
                                        budget.category?.name || 'Lainnya'
                                    }}</span>
                                    <span
                                        class="budget-card-note"
                                        v-if="budget.note"
                                        >{{ budget.note }}</span
                                    >
                                </div>
                            </div>
                            <div class="budget-card-right">
                                <div class="budget-card-actions">
                                    <button
                                        @click="openEdit(budget)"
                                        class="budget-card-action"
                                    >
                                        <Pencil class="h-3.5 w-3.5" />
                                    </button>
                                    <button
                                        @click="deleteBudget(budget.id)"
                                        class="budget-card-action budget-card-action--danger"
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="budget-card-bar-wrap">
                            <div class="budget-card-bar">
                                <div
                                    class="budget-card-bar-fill"
                                    :style="{
                                        width:
                                            Math.min(budget.percentage, 100) +
                                            '%',
                                        background:
                                            statusColors[budget.status].bar,
                                    }"
                                ></div>
                            </div>
                        </div>

                        <div class="budget-card-bottom">
                            <span class="budget-card-spent"
                                >{{ formatCurrency(budget.spent) }} /
                                {{ formatCurrency(budget.amount) }}</span
                            >
                            <span
                                class="budget-card-status"
                                :style="{
                                    color: statusColors[budget.status].text,
                                    backgroundColor:
                                        statusColors[budget.status].bg,
                                }"
                            >
                                <AlertTriangle
                                    v-if="budget.status === 'over'"
                                    class="h-3 w-3"
                                />
                                {{ statusLabel[budget.status] }} ({{
                                    budget.percentage
                                }}%)
                            </span>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div
                        v-if="
                            (activeTab === 'monthly'
                                ? monthlyBudgets
                                : weeklyBudgets
                            ).length === 0
                        "
                        class="budget-empty"
                    >
                        <div class="budget-empty-icon">
                            <PiggyBank class="h-10 w-10" />
                        </div>
                        <p class="budget-empty-text">
                            Belum ada budget
                            {{
                                activeTab === 'monthly' ? 'bulanan' : 'mingguan'
                            }}
                        </p>
                        <button class="budget-empty-btn" @click="openCreate">
                            <Plus class="mr-1 h-4 w-4" />
                            Buat Budget Pertama
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Teleport to="body">
            <div
                v-if="showModal"
                class="budget-modal-overlay"
                @click.self="showModal = false"
            >
                <div class="budget-modal">
                    <div class="budget-modal-header">
                        <h3 class="budget-modal-title">
                            {{
                                editingBudget
                                    ? 'Edit Budget'
                                    : 'Buat Budget Baru'
                            }}
                        </h3>
                        <button
                            @click="showModal = false"
                            class="budget-modal-close"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>
                    <form @submit.prevent="submit" class="budget-modal-form">
                        <div v-if="!editingBudget" class="budget-form-group">
                            <label class="budget-form-label">Kategori</label>
                            <select
                                v-model="form.category_id"
                                class="budget-form-input"
                                required
                            >
                                <option value="" disabled>
                                    Pilih kategori...
                                </option>
                                <option
                                    v-for="cat in availableCategories"
                                    :key="cat.id"
                                    :value="cat.id"
                                >
                                    {{ cat.name }}
                                </option>
                                <option value="new">
                                    ✨ + Buat Kategori Kustom Baru
                                </option>
                            </select>
                            <p
                                v-if="form.errors.category_id"
                                class="budget-form-error"
                            >
                                {{ form.errors.category_id }}
                            </p>
                        </div>

                        <div
                            v-if="!editingBudget && form.category_id === 'new'"
                            class="budget-form-group"
                        >
                            <label class="budget-form-label"
                                >Nama Kategori Kustom</label
                            >
                            <input
                                v-model="form.new_category_name"
                                type="text"
                                class="budget-form-input"
                                placeholder="e.g. Uang Makan Anak"
                                required
                            />
                            <p
                                v-if="form.errors.new_category_name"
                                class="budget-form-error"
                            >
                                {{ form.errors.new_category_name }}
                            </p>
                        </div>

                        <div class="budget-form-group">
                            <label class="budget-form-label"
                                >Jumlah Budget (Rp)</label
                            >
                            <input
                                v-model="form.amount"
                                type="number"
                                min="1"
                                class="budget-form-input"
                                placeholder="300000"
                                required
                            />
                            <p
                                v-if="form.errors.amount"
                                class="budget-form-error"
                            >
                                {{ form.errors.amount }}
                            </p>
                        </div>

                        <div v-if="!editingBudget" class="budget-form-group">
                            <label class="budget-form-label">Periode</label>
                            <div class="budget-form-radio-group">
                                <label
                                    class="budget-form-radio"
                                    :class="
                                        form.period === 'monthly' &&
                                        'budget-form-radio--active'
                                    "
                                >
                                    <input
                                        type="radio"
                                        v-model="form.period"
                                        value="monthly"
                                        class="sr-only"
                                    />
                                    <Check
                                        v-if="form.period === 'monthly'"
                                        class="h-4 w-4"
                                    />
                                    Per Bulan
                                </label>
                                <label
                                    class="budget-form-radio"
                                    :class="
                                        form.period === 'weekly' &&
                                        'budget-form-radio--active'
                                    "
                                >
                                    <input
                                        type="radio"
                                        v-model="form.period"
                                        value="weekly"
                                        class="sr-only"
                                    />
                                    <Check
                                        v-if="form.period === 'weekly'"
                                        class="h-4 w-4"
                                    />
                                    Per Minggu
                                </label>
                            </div>
                        </div>

                        <div class="budget-form-group">
                            <label class="budget-form-label"
                                >Catatan (opsional)</label
                            >
                            <input
                                v-model="form.note"
                                type="text"
                                class="budget-form-input"
                                placeholder="e.g. untuk transport harian"
                            />
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="budget-form-submit"
                        >
                            {{
                                form.processing
                                    ? 'Menyimpan...'
                                    : editingBudget
                                      ? 'Simpan Perubahan'
                                      : 'Buat Budget'
                            }}
                        </button>
                    </form>
                </div>
            </div>
        </Teleport>

        <!-- Manage Categories Modal -->
        <Teleport to="body">
            <div
                v-if="showManageCategories"
                class="budget-modal-overlay"
                @click.self="showManageCategories = false"
            >
                <div class="budget-modal">
                    <div class="budget-modal-header">
                        <h3 class="budget-modal-title">Kelola Kategori</h3>
                        <button
                            @click="showManageCategories = false"
                            class="budget-modal-close"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <div
                        class="max-h-96 space-y-3 overflow-y-auto pr-1"
                        style="scrollbar-width: none"
                    >
                        <div
                            v-for="cat in categories"
                            :key="cat.id"
                            class="flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-800/50"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="h-4 w-4 rounded-full"
                                    :style="{
                                        background: cat.color || '#71717a',
                                    }"
                                ></div>
                                <span
                                    class="text-sm font-bold text-gray-900 dark:text-white"
                                    >{{ cat.name }}</span
                                >
                            </div>
                            <button
                                @click="deleteCategory(cat)"
                                class="rounded-xl p-2 text-red-500 transition hover:bg-red-50 dark:hover:bg-red-950/30"
                            >
                                <Trash2 class="h-4 w-4" />
                            </button>
                        </div>

                        <div
                            v-if="categories.length === 0"
                            class="py-8 text-center text-gray-500"
                        >
                            Belum ada kategori kustom.
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Attractive Custom Confirm Modal -->
        <Teleport to="body">
            <div
                v-if="confirmModal.show"
                class="budget-modal-overlay"
                @click.self="confirmModal.show = false"
                style="z-index: 300"
            >
                <div
                    class="budget-modal max-w-sm text-center"
                    style="padding: 24px"
                >
                    <div class="mb-4 animate-bounce text-5xl">⚠️</div>
                    <h3
                        class="budget-modal-title mb-2"
                        style="
                            font-size: 18px;
                            text-align: center;
                            font-weight: 800;
                            color: var(--foreground);
                        "
                    >
                        {{ confirmModal.title }}
                    </h3>
                    <p
                        class="mb-6 px-2 text-sm text-gray-500 dark:text-gray-400"
                        style="line-height: 1.5; font-weight: 500"
                    >
                        {{ confirmModal.message }}
                    </p>
                    <div class="flex gap-3">
                        <button
                            @click="executeConfirm"
                            class="flex-1 cursor-pointer rounded-xl border-none bg-red-500 py-2.5 font-bold text-white transition hover:bg-red-600"
                            style="
                                height: 44px;
                                font-size: 14px;
                                border-radius: 12px;
                                background: #ef4444;
                                color: white;
                            "
                        >
                            Ya, Hapus
                        </button>
                        <button
                            @click="confirmModal.show = false"
                            class="flex-1 cursor-pointer rounded-xl border-none bg-gray-100 font-medium text-gray-600 transition dark:bg-gray-800 dark:text-gray-300"
                            style="
                                height: 44px;
                                font-size: 14px;
                                border-radius: 12px;
                                background: var(--accent);
                                color: var(--muted-foreground);
                            "
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
.budget-page {
    display: flex;
    flex-direction: column;
    gap: 20px;
    padding: 20px 16px 100px;
    max-width: 800px;
    margin: 0 auto;
    width: 100%;
}
@media (min-width: 768px) {
    .budget-page {
        padding: 32px 32px 32px;
    }
}

/* Hero */
.budget-hero {
    position: relative;
    overflow: hidden;
    border-radius: 24px;
    padding: 24px;
    color: white;
}
.budget-hero-bg {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        135deg,
        #064e3b 0%,
        #065f46 40%,
        #047857 70%,
        #059669 100%
    );
}
.budget-hero-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(60px);
    opacity: 0.3;
}
.budget-hero-orb--1 {
    width: 180px;
    height: 180px;
    background: #34d399;
    top: -50px;
    right: -30px;
}
.budget-hero-orb--2 {
    width: 100px;
    height: 100px;
    background: #6ee7b7;
    bottom: -20px;
    left: 15%;
}
.budget-hero-content {
    position: relative;
    z-index: 10;
}
.budget-hero-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
}
.budget-hero-label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    opacity: 0.8;
}
.budget-hero-amount {
    font-size: 28px;
    font-weight: 800;
    letter-spacing: -0.03em;
    margin-bottom: 16px;
}
.budget-hero-bar-wrap {
    margin-bottom: 16px;
}
.budget-hero-bar {
    height: 8px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 4px;
    overflow: hidden;
}
.budget-hero-bar-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.6s ease;
}
.budget-hero-bar-label {
    font-size: 11px;
    opacity: 0.7;
    margin-top: 4px;
    display: block;
}
.budget-hero-stats {
    display: flex;
    align-items: center;
    padding: 10px 14px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.08);
}
.budget-hero-stat {
    display: flex;
    align-items: center;
    gap: 6px;
    flex: 1;
    font-size: 12px;
    font-weight: 600;
}
.budget-hero-stat-divider {
    width: 1px;
    height: 18px;
    background: rgba(255, 255, 255, 0.15);
    margin: 0 10px;
}
.budget-income-note {
    margin-top: 12px;
    font-size: 11px;
    opacity: 0.7;
    line-height: 1.4;
}

/* Tabs */
.budget-tabs {
    display: flex;
    background: var(--accent);
    border-radius: 14px;
    padding: 4px;
}
.budget-tab {
    flex: 1;
    padding: 10px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 600;
    text-align: center;
    background: transparent;
    border: none;
    color: var(--muted-foreground);
    cursor: pointer;
    transition: all 0.2s;
}
.budget-tab--active {
    background: var(--card);
    color: var(--foreground);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

/* Section */
.budget-section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}
.budget-section-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--foreground);
}
.budget-add-btn {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 8px 14px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    color: white;
    background: linear-gradient(135deg, #064e3b, #059669);
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}
.budget-add-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
}

/* Budget Card */
.budget-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.budget-card {
    padding: 16px;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 18px;
    transition: all 0.2s;
}
.budget-card:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
}
.budget-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}
.budget-card-left {
    display: flex;
    align-items: center;
    gap: 12px;
}
.budget-card-icon {
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    flex-shrink: 0;
}
.budget-card-info {
    display: flex;
    flex-direction: column;
}
.budget-card-name {
    font-size: 14px;
    font-weight: 700;
    color: var(--foreground);
}
.budget-card-note {
    font-size: 11px;
    color: var(--muted-foreground);
}
.budget-card-actions {
    display: flex;
    gap: 4px;
}
.budget-card-action {
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    border: none;
    background: var(--accent);
    color: var(--muted-foreground);
    cursor: pointer;
    transition: all 0.15s;
}
.budget-card-action:hover {
    color: var(--foreground);
}
.budget-card-action--danger:hover {
    color: #ef4444;
    background: rgba(239, 68, 68, 0.1);
}
.budget-card-bar-wrap {
    margin-bottom: 10px;
}
.budget-card-bar {
    height: 6px;
    background: var(--accent);
    border-radius: 3px;
    overflow: hidden;
}
.budget-card-bar-fill {
    height: 100%;
    border-radius: 3px;
    transition: width 0.5s ease;
}
.budget-card-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.budget-card-spent {
    font-size: 12px;
    font-weight: 600;
    color: var(--muted-foreground);
}
.budget-card-status {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 100px;
}

/* Empty */
.budget-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 48px 24px;
    text-align: center;
    background: var(--card);
    border: 2px dashed var(--border);
    border-radius: 20px;
}
.budget-empty-icon {
    width: 64px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 20px;
    background: var(--accent);
    color: var(--muted-foreground);
    margin-bottom: 14px;
}
.budget-empty-text {
    font-size: 14px;
    color: var(--muted-foreground);
    margin-bottom: 16px;
}
.budget-empty-btn {
    display: flex;
    align-items: center;
    padding: 10px 20px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 600;
    color: white;
    background: linear-gradient(135deg, #064e3b, #059669);
    border: none;
    cursor: pointer;
}

/* Modal */
.budget-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: flex-end;
    justify-content: center;
    z-index: 200;
    padding: 16px;
}
@media (min-width: 640px) {
    .budget-modal-overlay {
        align-items: center;
    }
}
.budget-modal {
    background: var(--card);
    border-radius: 24px 24px 16px 16px;
    width: 100%;
    max-width: 440px;
    padding: 24px;
    max-height: 90vh;
    overflow-y: auto;
}
@media (min-width: 640px) {
    .budget-modal {
        border-radius: 24px;
    }
}
.budget-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
}
.budget-modal-title {
    font-size: 18px;
    font-weight: 700;
    color: var(--foreground);
}
.budget-modal-close {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    border: none;
    background: var(--accent);
    color: var(--muted-foreground);
    cursor: pointer;
}
.budget-modal-form {
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.budget-form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.budget-form-label {
    font-size: 13px;
    font-weight: 600;
    color: var(--foreground);
}
.budget-form-input {
    height: 46px;
    padding: 0 14px;
    border: 1px solid var(--border);
    border-radius: 14px;
    font-size: 14px;
    background: var(--background);
    color: var(--foreground);
    outline: none;
    transition: border-color 0.2s;
}
.budget-form-input:focus {
    border-color: #059669;
    box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
}
.budget-form-error {
    font-size: 12px;
    color: #ef4444;
}
.budget-form-radio-group {
    display: flex;
    gap: 8px;
}
.budget-form-radio {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 12px;
    border-radius: 14px;
    font-size: 13px;
    font-weight: 600;
    border: 2px solid var(--border);
    color: var(--muted-foreground);
    cursor: pointer;
    transition: all 0.2s;
}
.budget-form-radio--active {
    border-color: #059669;
    color: #059669;
    background: rgba(5, 150, 105, 0.05);
}
.budget-form-submit {
    height: 48px;
    border-radius: 14px;
    font-size: 15px;
    font-weight: 600;
    color: white;
    background: linear-gradient(135deg, #064e3b, #059669);
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(5, 150, 105, 0.25);
    transition: all 0.2s;
}
.budget-form-submit:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(5, 150, 105, 0.35);
}
.budget-form-submit:disabled {
    opacity: 0.6;
}
</style>
