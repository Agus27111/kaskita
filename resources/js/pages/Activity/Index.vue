<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, watch, computed } from 'vue';
import {
    History, TrendingUp, TrendingDown, Filter, ChevronLeft, ChevronRight,
    User as UserIcon, Calendar, Tag, Wallet, RefreshCw, X, ArrowLeftRight, Download
} from 'lucide-vue-next';

interface TransactionItem {
    id: number;
    type: 'expense' | 'income' | 'transfer';
    amount: number;
    note: string | null;
    date: string;
    receipt_photo: string | null;
    category: { id: number; name: string; icon: string | null; color: string | null } | null;
    wallet: { id: number; name: string } | null;
    user: { id: number; name: string; role: string | null; avatar: string | null } | null;
}

interface Member {
    id: number;
    name: string;
    role: string | null;
    avatar: string | null;
}

interface Category {
    id: number;
    name: string;
    type: 'expense' | 'income';
    color: string | null;
}

const props = defineProps<{
    transactions: {
        data: TransactionItem[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        current_page: number;
        last_page: number;
    };
    members: Member[];
    categories: Category[];
    availableYears: number[];
    filters: {
        type: string;
        category_id: string;
        user_id: string;
        month: string;
        year: string;
    };
}>();

const breadcrumbs = [{ title: 'Aktivitas', href: '/activity' }];

const filterType = ref(props.filters.type || '');
const filterCategory = ref(props.filters.category_id || '');
const filterUser = ref(props.filters.user_id || '');
const filterMonth = ref(props.filters.month || '');
const filterYear = ref(props.filters.year || '');
const isDownloading = ref(false);

const formatCurrency = (v: number) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v);

const formatDate = (dateStr: string) => {
    const d = new Date(dateStr);
    return new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium' }).format(d);
};

// Compute dynamic role badge label
const getMemberLabel = (member: { name: string; role: string | null }) => {
    if (!member.role) return member.name;
    const roleCapitalized = member.role.charAt(0).toUpperCase() + member.role.slice(1);
    return `${roleCapitalized} (${member.name})`;
};

const hasActiveFilters = computed(() => {
    return filterType.value !== '' || filterCategory.value !== '' || filterUser.value !== '' || filterMonth.value !== '' || filterYear.value !== '';
});

function resetFilters() {
    filterType.value = '';
    filterCategory.value = '';
    filterUser.value = '';
    filterMonth.value = '';
    filterYear.value = '';
}

watch([filterType, filterCategory, filterUser, filterMonth, filterYear], () => {
    router.get('/activity', {
        type: filterType.value,
        category_id: filterCategory.value,
        user_id: filterUser.value,
        month: filterMonth.value,
        year: filterYear.value,
    }, {
        preserveState: true,
        replace: true,
    });
});

const downloadPdf = () => {
    isDownloading.value = true;
    const params = new URLSearchParams();
    if (filterType.value) params.append('type', filterType.value);
    if (filterCategory.value) params.append('category_id', filterCategory.value);
    if (filterUser.value) params.append('user_id', filterUser.value);
    if (filterMonth.value) params.append('month', filterMonth.value);
    if (filterYear.value) params.append('year', filterYear.value);

    // Direct browser navigation starts file download attachment flow smoothly
    window.location.href = `/activity/download-pdf?${params.toString()}`;

    setTimeout(() => {
        isDownloading.value = false;
    }, 3000);
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Aktivitas" />

        <div class="activity-page">
            <!-- Header Section with Premium Glassmorphism Stats -->
            <div class="activity-hero">
                <div class="activity-hero-bg"></div>
                <div class="activity-hero-content">
                    <div class="activity-hero-top">
                        <History class="h-6 w-6 text-white opacity-90" />
                        <h2 class="activity-hero-title">Aktivitas Keluarga</h2>
                    </div>
                    <p class="activity-hero-subtitle">Pantau riwayat pemasukan dan pengeluaran setiap anggota keluarga secara real-time.</p>
                </div>
            </div>

            <!-- Modern Filters Section -->
            <div class="filters-card">
                <div class="filters-header">
                    <div class="filters-header-left">
                        <Filter class="h-4 w-4 text-emerald-600" />
                        <span class="filters-title">Filter Aktivitas</span>
                    </div>
                    <div class="filters-actions">
                        <button v-if="hasActiveFilters" @click="resetFilters" class="reset-filter-btn">
                            <X class="h-3.5 w-3.5" />
                            Hapus Filter
                        </button>
                        <button @click="downloadPdf" class="download-pdf-btn" :disabled="isDownloading">
                            <RefreshCw v-if="isDownloading" class="h-3.5 w-3.5 animate-spin" />
                            <Download v-else class="h-3.5 w-3.5" />
                            {{ isDownloading ? 'Unduh...' : 'Download PDF' }}
                        </button>
                    </div>
                </div>

                <div class="filters-grid">
                    <div class="filter-group">
                        <label class="filter-label">Jenis Transaksi</label>
                        <select v-model="filterType" class="filter-select">
                            <option value="">Semua Transaksi</option>
                            <option value="expense">Pengeluaran (Cash Out)</option>
                            <option value="income">Pemasukan (Cash In)</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">Anggota Keluarga</label>
                        <select v-model="filterUser" class="filter-select">
                            <option value="">Semua Anggota</option>
                            <option v-for="member in members" :key="member.id" :value="member.id">
                                {{ getMemberLabel(member) }}
                            </option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">Kategori</label>
                        <select v-model="filterCategory" class="filter-select">
                            <option value="">Semua Kategori</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                {{ cat.name }} ({{ cat.type === 'expense' ? 'Pengeluaran' : 'Pemasukan' }})
                            </option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">Bulan</label>
                        <select v-model="filterMonth" class="filter-select">
                            <option value="">Semua Bulan</option>
                            <option value="1">Januari</option>
                            <option value="2">Februari</option>
                            <option value="3">Maret</option>
                            <option value="4">April</option>
                            <option value="5">Mei</option>
                            <option value="6">Juni</option>
                            <option value="7">Juli</option>
                            <option value="8">Agustus</option>
                            <option value="9">September</option>
                            <option value="10">Oktober</option>
                            <option value="11">November</option>
                            <option value="12">Desember</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">Tahun</label>
                        <select v-model="filterYear" class="filter-select">
                            <option value="">Semua Tahun</option>
                            <option v-for="yr in availableYears" :key="yr" :value="yr">
                                {{ yr }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Transaction List -->
            <div class="transactions-section">
                <div v-if="transactions.data.length > 0" class="transactions-list">
                    <div v-for="item in transactions.data" :key="item.id" class="activity-card">
                        <div class="activity-card-inner">
                            <div class="activity-card-left">
                                <!-- Dynamic Icon with Type Color -->
                                <div 
                                    class="type-icon-wrapper"
                                    :class="[
                                        item.type === 'expense' && 'type-icon-wrapper--expense',
                                        item.type === 'income' && 'type-icon-wrapper--income',
                                        item.type === 'transfer' && 'type-icon-wrapper--transfer'
                                    ]"
                                >
                                    <TrendingDown v-if="item.type === 'expense'" class="h-5 w-5" />
                                    <TrendingUp v-else-if="item.type === 'income'" class="h-5 w-5" />
                                    <ArrowLeftRight v-else class="h-5 w-5" />
                                </div>

                                <div class="activity-details">
                                    <div class="activity-title-row">
                                        <h4 class="activity-note">{{ item.note || item.category?.name || 'Transaksi' }}</h4>
                                    </div>
                                    
                                    <!-- Context Badges -->
                                    <div class="activity-badges">
                                        <span class="badge badge--date">
                                            <Calendar class="h-3 w-3" />
                                            {{ formatDate(item.date) }}
                                        </span>
                                        <span v-if="item.category" class="badge badge--category" :style="{ color: item.category.color || '#10b981', backgroundColor: (item.category.color || '#10b981') + '10' }">
                                            <Tag class="h-3 w-3" />
                                            {{ item.category.name }}
                                        </span>
                                        <span v-if="item.wallet" class="badge badge--wallet">
                                            <Wallet class="h-3 w-3" />
                                            {{ item.wallet.name }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Amount and Who Made It -->
                            <div class="activity-card-right">
                                <span 
                                    class="activity-amount"
                                    :class="[
                                        item.type === 'expense' && 'activity-amount--expense',
                                        item.type === 'income' && 'activity-amount--income'
                                    ]"
                                >
                                    {{ item.type === 'expense' ? '-' : '+' }} {{ formatCurrency(item.amount) }}
                                </span>

                                <!-- Premium Member Badge (Dad / Mom / Who did it) -->
                                <div v-if="item.user" class="member-pill">
                                    <div class="member-avatar">
                                        <span class="member-emoji">
                                            {{ item.user.role === 'ayah' ? '👨‍💼' : (item.user.role === 'ibu' ? '👩‍💼' : '👤') }}
                                        </span>
                                    </div>
                                    <span class="member-name-role">
                                        {{ item.user.role ? (item.user.role === 'ayah' ? 'Ayah' : 'Ibu') : item.user.name }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="transactions.last_page > 1" class="pagination-container">
                        <Link
                            v-for="link in transactions.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            :class="[
                                'pagination-link',
                                link.active && 'pagination-link--active',
                                !link.url && 'pagination-link--disabled'
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="activity-empty">
                    <div class="activity-empty-icon">
                        <History class="h-10 w-10 text-zinc-400" />
                    </div>
                    <p class="activity-empty-title">Tidak Ada Aktivitas Ditemukan</p>
                    <p class="activity-empty-text">Cobalah mengubah kombinasi filter Anda atau catat transaksi baru.</p>
                    <button @click="resetFilters" class="activity-empty-btn">Hapus Semua Filter</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.activity-page { display: flex; flex-direction: column; gap: 20px; padding: 20px 16px 100px; max-width: 800px; margin: 0 auto; width: 100%; }
@media (min-width: 768px) { .activity-page { padding: 32px 32px 32px; } }

/* Hero */
.activity-hero { position: relative; overflow: hidden; border-radius: 24px; padding: 24px; color: white; }
.activity-hero-bg { position: absolute; inset: 0; background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 40%, #2563eb 80%, #3b82f6 100%); }
.activity-hero-content { position: relative; z-index: 10; }
.activity-hero-top { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
.activity-hero-title { font-size: 20px; font-weight: 800; letter-spacing: -0.02em; }
.activity-hero-subtitle { font-size: 13px; opacity: 0.85; line-height: 1.5; }

/* Filters */
.filters-card { background: var(--card); border: 1px solid var(--border); border-radius: 20px; padding: 20px; }
.filters-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 12px; gap: 12px; }
.filters-header-left { display: flex; align-items: center; gap: 8px; }
.filters-title { font-size: 14px; font-weight: 700; color: var(--foreground); }
.filters-actions { display: flex; align-items: center; gap: 10px; }
.reset-filter-btn { display: flex; align-items: center; gap: 4px; font-size: 12px; font-weight: 600; color: #ef4444; background: transparent; border: none; cursor: pointer; }
.download-pdf-btn { display: flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 12px; font-size: 12px; font-weight: 700; color: white; background: linear-gradient(135deg, #059669 0%, #10b981 100%); border: none; cursor: pointer; transition: all 0.2s; box-shadow: 0 2px 6px rgba(16, 185, 129, 0.15); }
.download-pdf-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25); }
.download-pdf-btn:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }
.filters-grid { display: grid; grid-template-columns: 1fr; gap: 14px; }
@media (min-width: 640px) { .filters-grid { grid-template-columns: repeat(5, 1fr); } }
.filter-group { display: flex; flex-direction: column; gap: 6px; }
.filter-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--muted-foreground); }
.filter-select { height: 42px; border: 1px solid var(--border); border-radius: 12px; padding: 0 12px; font-size: 13px; background: var(--background); color: var(--foreground); outline: none; cursor: pointer; transition: border-color 0.2s; }
.filter-select:focus { border-color: #2563eb; }

/* Cards */
.transactions-section { display: flex; flex-direction: column; gap: 12px; }
.transactions-list { display: flex; flex-direction: column; gap: 12px; }
.activity-card { background: var(--card); border: 1px solid var(--border); border-radius: 18px; padding: 16px; transition: all 0.2s; }
.activity-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.03); transform: translateY(-1px); }
.activity-card-inner { display: flex; align-items: center; justify-content: space-between; gap: 16px; }
.activity-card-left { display: flex; align-items: center; gap: 14px; flex: 1; }

.type-icon-wrapper { width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; border-radius: 14px; flex-shrink: 0; }
.type-icon-wrapper--expense { background: rgba(239,68,68,0.08); color: #ef4444; }
.type-icon-wrapper--income { background: rgba(16,185,129,0.08); color: #10b981; }
.type-icon-wrapper--transfer { background: rgba(59,130,246,0.08); color: #3b82f6; }

.activity-details { display: flex; flex-direction: column; gap: 4px; }
.activity-note { font-size: 14px; font-weight: 700; color: var(--foreground); line-height: 1.3; }

/* Badges */
.activity-badges { display: flex; flex-wrap: wrap; gap: 6px; }
.badge { display: flex; align-items: center; gap: 4px; font-size: 10px; font-weight: 600; padding: 3px 10px; border-radius: 100px; }
.badge--date { background: var(--accent); color: var(--muted-foreground); }
.badge--wallet { background: var(--accent); color: var(--muted-foreground); }

/* Right Section */
.activity-card-right { display: flex; flex-direction: column; align-items: flex-end; gap: 6px; }
.activity-amount { font-size: 15px; font-weight: 800; letter-spacing: -0.02em; }
.activity-amount--expense { color: #ef4444; }
.activity-amount--income { color: #10b981; }

/* Member Pill */
.member-pill { display: flex; align-items: center; gap: 6px; padding: 4px 10px; background: var(--accent); border-radius: 100px; border: 1px solid var(--border); }
.member-avatar { display: flex; align-items: center; justify-content: center; }
.member-emoji { font-size: 12px; }
.member-name-role { font-size: 11px; font-weight: 700; color: var(--foreground); }

/* Pagination */
.pagination-container { display: flex; flex-wrap: wrap; justify-content: center; gap: 4px; margin-top: 16px; }
.pagination-link { display: flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 10px; border-radius: 10px; border: 1px solid var(--border); background: var(--card); font-size: 13px; font-weight: 600; text-decoration: none; color: var(--muted-foreground); transition: all 0.2s; }
.pagination-link--active { background: #2563eb; border-color: #2563eb; color: white; }
.pagination-link--disabled { opacity: 0.5; pointer-events: none; }

/* Empty State */
.activity-empty { display: flex; flex-direction: column; align-items: center; padding: 48px 24px; text-align: center; background: var(--card); border: 2px dashed var(--border); border-radius: 20px; }
.activity-empty-icon { width: 64px; height: 64px; display: flex; align-items: center; justify-content: center; border-radius: 20px; background: var(--accent); margin-bottom: 14px; }
.activity-empty-title { font-size: 15px; font-weight: 700; color: var(--foreground); margin-bottom: 6px; }
.activity-empty-text { font-size: 13px; color: var(--muted-foreground); margin-bottom: 16px; max-width: 320px; }
.activity-empty-btn { display: flex; align-items: center; padding: 10px 20px; border-radius: 12px; font-size: 13px; font-weight: 600; color: white; background: #2563eb; border: none; cursor: pointer; transition: all 0.2s; }
.activity-empty-btn:hover { background: #1d4ed8; }
</style>
