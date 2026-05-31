<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    LayoutGrid,
    Coins,
    Plus,
    ArrowUpRight,
    ArrowDownRight,
    ArrowLeftRight,
    History,
    Users,
} from 'lucide-vue-next';
import { ref } from 'vue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import { dashboard } from '@/routes';

const { isCurrentUrl } = useCurrentUrl();
const showQuickMenu = ref(false);

const leftItems = [
    {
        title: 'Home',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Budget',
        href: '/budget',
        icon: Coins,
    },
];

const rightItems = [
    {
        title: 'Aktivitas',
        href: '/activity',
        icon: History,
    },
    {
        title: 'Keluarga',
        href: '/family/settings',
        icon: Users,
    },
];
</script>

<template>
    <nav class="bottom-nav-wrapper lg:hidden">
        <div class="bottom-nav-container">
            <!-- Left items -->
            <Link
                v-for="item in leftItems"
                :key="item.title"
                :href="toUrl(item.href)"
                class="bottom-nav-item"
                :class="{ 'bottom-nav-item--active': isCurrentUrl(item.href) }"
            >
                <component :is="item.icon" class="bottom-nav-icon" />
                <span class="bottom-nav-label">{{ item.title }}</span>
            </Link>

            <!-- Center FAB Button -->
            <button
                @click="showQuickMenu = !showQuickMenu"
                class="bottom-nav-center-btn cursor-pointer border-none outline-none focus:ring-0"
            >
                <div
                    class="bottom-nav-center-inner"
                    :style="{
                        background: showQuickMenu
                            ? 'linear-gradient(135deg, #f43f5e 0%, #e11d48 100%)'
                            : 'linear-gradient(135deg, #8b5cf6 0%, #a78bfa 50%, #c4b5fd 100%)',
                        transform: showQuickMenu ? 'rotate(45deg)' : 'none',
                    }"
                >
                    <Plus class="bottom-nav-center-icon" />
                </div>
            </button>

            <!-- Right items -->
            <Link
                v-for="item in rightItems"
                :key="item.title"
                :href="toUrl(item.href)"
                class="bottom-nav-item"
                :class="{ 'bottom-nav-item--active': isCurrentUrl(item.href) }"
            >
                <component :is="item.icon" class="bottom-nav-icon" />
                <span class="bottom-nav-label">{{ item.title }}</span>
            </Link>
        </div>
    </nav>

    <!-- Quick Actions Floating Overlay -->
    <Teleport to="body">
        <div
            v-if="showQuickMenu"
            class="fixed inset-0 z-45 flex flex-col justify-end bg-black/40 p-6 backdrop-blur-md transition duration-300 ease-out dark:bg-black/60"
            @click.self="showQuickMenu = false"
        >
            <div
                class="mx-auto mb-24 w-full max-w-[380px] animate-in space-y-4 duration-300 ease-out slide-in-from-bottom-12"
            >
                <div
                    class="space-y-2 rounded-3xl border border-gray-100 bg-white/95 p-4 shadow-2xl backdrop-blur-xl dark:border-zinc-800 dark:bg-zinc-900/95"
                >
                    <p
                        class="border-b border-gray-50 pb-2 text-center text-[10px] font-black tracking-widest text-gray-400 uppercase dark:border-zinc-800/50 dark:text-zinc-500"
                    >
                        Catat Transaksi Baru
                    </p>

                    <!-- Pemasukan Option -->
                    <Link
                        href="/dashboard?action=income"
                        @click="showQuickMenu = false"
                        class="flex items-center gap-4 rounded-2xl p-3.5 text-sm font-extrabold text-emerald-600 transition hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-950/20"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10"
                        >
                            <ArrowUpRight class="h-5 w-5" />
                        </div>
                        <div class="text-left">
                            <p class="text-xs font-extrabold">
                                Catat Pemasukan
                            </p>
                            <p
                                class="mt-0.5 text-[9px] font-semibold text-gray-400 dark:text-zinc-500"
                            >
                                Catat gaji, bonus, atau uang masuk lainnya
                            </p>
                        </div>
                    </Link>

                    <!-- Pengeluaran Option -->
                    <Link
                        href="/dashboard?action=expense"
                        @click="showQuickMenu = false"
                        class="flex items-center gap-4 rounded-2xl p-3.5 text-sm font-extrabold text-rose-600 transition hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/20"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-500/10"
                        >
                            <ArrowDownRight class="h-5 w-5" />
                        </div>
                        <div class="text-left">
                            <p class="text-xs font-extrabold">
                                Catat Pengeluaran
                            </p>
                            <p
                                class="mt-0.5 text-[9px] font-semibold text-gray-400 dark:text-zinc-500"
                            >
                                Catat belanjaan, jajan, tagihan bulanan
                            </p>
                        </div>
                    </Link>

                    <!-- Transfer Option -->
                    <Link
                        href="/dashboard?action=transfer"
                        @click="showQuickMenu = false"
                        class="flex items-center gap-4 rounded-2xl p-3.5 text-sm font-extrabold text-violet-600 transition hover:bg-violet-50 dark:text-violet-400 dark:hover:bg-violet-950/20"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-500/10"
                        >
                            <ArrowLeftRight class="h-5 w-5" />
                        </div>
                        <div class="text-left">
                            <p class="text-xs font-extrabold">
                                Lakukan Transfer
                            </p>
                            <p
                                class="mt-0.5 text-[9px] font-semibold text-gray-400 dark:text-zinc-500"
                            >
                                Pindahkan saldo antar dompet keluarga
                            </p>
                        </div>
                    </Link>
                </div>

                <button
                    @click="showQuickMenu = false"
                    class="w-full cursor-pointer rounded-2xl bg-gray-100 py-4 text-xs font-black text-gray-800 shadow-md transition hover:bg-gray-200 dark:bg-zinc-800 dark:text-gray-200"
                >
                    Tutup
                </button>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
/* ─── Wrapper: fixed at bottom, horizontally centered ─── */
.bottom-nav-wrapper {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    margin: 0 auto;
    width: 100%;
    max-width: 450px;
    z-index: 100;
    display: flex;
    justify-content: center;
    padding: 0 16px;
    padding-bottom: env(safe-area-inset-bottom, 12px);
    pointer-events: none;
}

/* ─── Pill container ─── */
.bottom-nav-container {
    display: flex;
    align-items: center;
    justify-content: space-around;
    gap: 4px;
    width: 100%;
    max-width: 420px;
    padding: 8px 12px;
    margin-bottom: 8px;
    border-radius: 28px;
    background: rgba(255, 255, 255, 0.92);
    backdrop-filter: blur(20px) saturate(180%);
    -webkit-backdrop-filter: blur(20px) saturate(180%);
    box-shadow:
        0 8px 32px rgba(0, 0, 0, 0.08),
        0 2px 8px rgba(0, 0, 0, 0.04),
        inset 0 1px 0 rgba(255, 255, 255, 0.6);
    pointer-events: auto;
}

:is(.dark) .bottom-nav-container {
    background: rgba(24, 24, 27, 0.92);
    box-shadow:
        0 8px 32px rgba(0, 0, 0, 0.3),
        0 2px 8px rgba(0, 0, 0, 0.2),
        inset 0 1px 0 rgba(255, 255, 255, 0.05);
}

/* ─── Nav item (regular) ─── */
.bottom-nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
    padding: 6px 14px;
    border-radius: 16px;
    text-decoration: none;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    color: #a1a1aa;
    position: relative;
}

.bottom-nav-item:active {
    transform: scale(0.92);
}

/* Active state */
.bottom-nav-item--active {
    color: #18181b;
}

:is(.dark) .bottom-nav-item--active {
    color: #fafafa;
}

/* ─── Icon ─── */
.bottom-nav-icon {
    width: 22px;
    height: 22px;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.bottom-nav-item--active .bottom-nav-icon {
    transform: translateY(-1px);
}

/* ─── Label ─── */
.bottom-nav-label {
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 0.01em;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

/* ─── Center FAB Button ─── */
.bottom-nav-center-btn {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: -20px 6px 0;
    z-index: 10;
    text-decoration: none;
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.bottom-nav-center-btn:active {
    transform: scale(0.88);
}

.bottom-nav-center-inner {
    width: 52px;
    height: 52px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow:
        0 8px 24px rgba(139, 92, 246, 0.35),
        0 2px 8px rgba(139, 92, 246, 0.2),
        inset 0 1px 1px rgba(255, 255, 255, 0.3);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.bottom-nav-center-btn:hover .bottom-nav-center-inner {
    box-shadow:
        0 12px 28px rgba(139, 92, 246, 0.45),
        0 4px 12px rgba(139, 92, 246, 0.25),
        inset 0 1px 1px rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
}

.bottom-nav-center-icon {
    width: 24px;
    height: 24px;
    color: #fff;
    stroke-width: 2.5;
}

/* ─── Active indicator dot ─── */
.bottom-nav-item--active::after {
    content: '';
    position: absolute;
    bottom: -2px;
    width: 4px;
    height: 4px;
    border-radius: 50%;
    background: #18181b;
    animation: fadeInDot 0.3s ease forwards;
}

:is(.dark) .bottom-nav-item--active::after {
    background: #fafafa;
}

@keyframes fadeInDot {
    from {
        opacity: 0;
        transform: scale(0);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}
</style>
