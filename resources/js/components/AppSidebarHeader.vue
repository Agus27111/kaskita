<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { Bell } from 'lucide-vue-next';
import { computed } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { SidebarTrigger } from '@/components/ui/sidebar';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { getInitials } from '@/composables/useInitials';
import type { BreadcrumbItem } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const page = usePage();
const auth = computed(() => page.props.auth);

const greeting = computed(() => {
    const hour = new Date().getHours();

    if (hour < 11) {
        return 'Selamat Pagi';
    }

    if (hour < 15) {
        return 'Selamat Siang';
    }

    if (hour < 18) {
        return 'Selamat Sore';
    }

    return 'Selamat Malam';
});

const firstName = computed(() => {
    const name = auth.value?.user?.name || '';

    return name.split(' ')[0];
});
</script>

<template>
    <header class="app-header">
        <div class="app-header-inner">
            <!-- Left: Sidebar trigger + Greeting or Breadcrumbs -->
            <div class="app-header-left">
                <SidebarTrigger class="app-header-trigger" />

                <!-- Desktop: Breadcrumbs -->
                <div
                    class="hidden md:block"
                    v-if="breadcrumbs && breadcrumbs.length > 0"
                >
                    <Breadcrumbs :breadcrumbs="breadcrumbs" />
                </div>

                <!-- Mobile: Greeting -->
                <div class="app-header-greeting md:hidden">
                    <span class="app-header-greeting-label"
                        >{{ greeting }} 👋</span
                    >
                    <span class="app-header-greeting-name">{{
                        firstName
                    }}</span>
                </div>
            </div>

            <!-- Right: Notification + Avatar -->
            <div class="app-header-right">
                <DropdownMenu>
                    <DropdownMenuTrigger :as-child="true">
                        <button
                            class="app-header-notif-btn outline-none focus:outline-none"
                        >
                            <Bell class="h-5 w-5" />
                            <span class="app-header-notif-dot"></span>
                        </button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent
                        align="end"
                        class="w-80 space-y-3 rounded-2xl border border-gray-100 bg-white/95 p-4 shadow-2xl backdrop-blur-md md:w-[350px] dark:border-zinc-800 dark:bg-zinc-900/95"
                    >
                        <div
                            class="flex items-center justify-between border-b border-gray-50 pb-2 dark:border-zinc-800/50"
                        >
                            <h4
                                class="text-xs font-black tracking-wider text-gray-900 uppercase dark:text-white"
                            >
                                Notifikasi Keluarga
                            </h4>
                            <span
                                class="rounded-full bg-rose-500/10 px-2.5 py-0.5 text-[9px] font-bold text-rose-600"
                                >4 Notif</span
                            >
                        </div>

                        <div class="max-h-[350px] space-y-3 overflow-y-auto">
                            <!-- Daily Charity (Sedekah) Reminder -->
                            <div
                                class="group relative flex gap-3 overflow-hidden rounded-2xl border border-violet-500/20 bg-violet-500/10 p-3 transition hover:bg-violet-500/15"
                            >
                                <div
                                    class="pointer-events-none absolute -right-4 -bottom-4 h-12 w-12 rounded-full bg-violet-500/10 blur-xl transition group-hover:scale-125"
                                ></div>
                                <div
                                    class="flex h-8 w-8 shrink-0 animate-pulse items-center justify-center rounded-lg bg-violet-500/20 text-xs font-bold text-violet-600 dark:text-violet-400"
                                >
                                    🕌
                                </div>
                                <div
                                    class="relative z-10 flex-1 space-y-1 text-left"
                                >
                                    <div class="flex items-center gap-1.5">
                                        <p
                                            class="text-xs font-black text-violet-700 dark:text-violet-400"
                                        >
                                            Pengingat Sedekah Harian
                                        </p>
                                        <span
                                            class="py-0.2 rounded-md bg-violet-500 px-1 text-[7px] font-extrabold tracking-wider text-white uppercase"
                                            >Berkah</span
                                        >
                                    </div>
                                    <p
                                        class="text-[10px] leading-relaxed font-extrabold text-gray-700 italic dark:text-gray-300"
                                    >
                                        "Sedekah itu tidak akan mengurangi
                                        harta." (HR. Muslim)
                                    </p>
                                    <p
                                        class="mt-1 text-[9px] leading-relaxed font-medium text-gray-500 dark:text-gray-400"
                                    >
                                        Yuk, raih keberkahan finansial keluarga
                                        hari ini dengan berbagi kepada sesama.
                                    </p>
                                    <p
                                        class="text-[8px] font-bold text-violet-500 dark:text-violet-400"
                                    >
                                        Setiap Hari ✨
                                    </p>
                                </div>
                            </div>

                            <!-- Notif 1 -->
                            <div
                                class="flex gap-3 rounded-xl p-2 transition hover:bg-gray-50 dark:hover:bg-zinc-800/40"
                            >
                                <div
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-500/10 text-xs font-bold text-amber-600 dark:text-amber-400"
                                >
                                    ⚠️
                                </div>
                                <div class="flex-1 space-y-0.5 text-left">
                                    <p
                                        class="text-xs font-black text-gray-900 dark:text-white"
                                    >
                                        Batas Anggaran Kritis!
                                    </p>
                                    <p
                                        class="text-[10px] leading-relaxed font-medium text-gray-500 dark:text-gray-400"
                                    >
                                        Kategori <strong>Makanan</strong> Anda
                                        telah mencapai 75% pemakaian batas
                                        bulanan.
                                    </p>
                                    <p
                                        class="text-[8px] font-bold text-gray-400 dark:text-zinc-500"
                                    >
                                        1 jam yang lalu
                                    </p>
                                </div>
                            </div>

                            <!-- Notif 2 -->
                            <div
                                class="flex gap-3 rounded-xl p-2 transition hover:bg-gray-50 dark:hover:bg-zinc-800/40"
                            >
                                <div
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10 text-xs font-bold text-emerald-600 dark:text-emerald-400"
                                >
                                    📈
                                </div>
                                <div class="flex-1 space-y-0.5 text-left">
                                    <p
                                        class="text-xs font-black text-gray-900 dark:text-white"
                                    >
                                        Rasio Arus Kas Bulanan
                                    </p>
                                    <p
                                        class="text-[10px] leading-relaxed font-medium text-gray-500 dark:text-gray-400"
                                    >
                                        Kondisi keuangan keluarga Anda saat ini
                                        berkategori
                                        <strong>Cukup Sehat</strong> (62%
                                        terpakai).
                                    </p>
                                    <p
                                        class="text-[8px] font-bold text-gray-400 dark:text-zinc-500"
                                    >
                                        5 jam yang lalu
                                    </p>
                                </div>
                            </div>

                            <!-- Notif 3 -->
                            <div
                                class="flex gap-3 rounded-xl p-2 transition hover:bg-gray-50 dark:hover:bg-zinc-800/40"
                            >
                                <div
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-500/10 text-xs font-bold text-indigo-600 dark:text-indigo-400"
                                >
                                    💰
                                </div>
                                <div class="flex-1 space-y-0.5 text-left">
                                    <p
                                        class="text-xs font-black text-gray-900 dark:text-white"
                                    >
                                        Pemasukan Terdaftar
                                    </p>
                                    <p
                                        class="text-[10px] leading-relaxed font-medium text-gray-500 dark:text-gray-400"
                                    >
                                        Pemasukan sebesar
                                        <strong>Rp 15.000.000</strong> berhasil
                                        ditambahkan ke Dompet Utama oleh
                                        keluarga.
                                    </p>
                                    <p
                                        class="text-[8px] font-bold text-gray-400 dark:text-zinc-500"
                                    >
                                        1 hari yang lalu
                                    </p>
                                </div>
                            </div>
                        </div>
                    </DropdownMenuContent>
                </DropdownMenu>

                <DropdownMenu>
                    <DropdownMenuTrigger :as-child="true">
                        <Button
                            variant="ghost"
                            size="icon"
                            class="app-header-avatar-btn"
                        >
                            <Avatar
                                class="size-9 overflow-hidden rounded-full ring-2 ring-white/80 dark:ring-zinc-800"
                            >
                                <AvatarImage
                                    v-if="auth.user.avatar"
                                    :src="auth.user.avatar"
                                    :alt="auth.user.name"
                                />
                                <AvatarFallback
                                    class="rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 text-xs font-bold text-white"
                                >
                                    {{ getInitials(auth.user?.name) }}
                                </AvatarFallback>
                            </Avatar>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-56">
                        <UserMenuContent :user="auth.user" />
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </div>
    </header>
</template>

<style scoped>
.app-header {
    position: sticky;
    top: 0;
    z-index: 40;
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(16px) saturate(180%);
    -webkit-backdrop-filter: blur(16px) saturate(180%);
    border-bottom: 1px solid rgba(0, 0, 0, 0.04);
}

:is(.dark) .app-header {
    background: rgba(9, 9, 11, 0.85);
    border-bottom-color: rgba(255, 255, 255, 0.04);
}

.app-header-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 60px;
    padding: 0 20px;
}

@media (min-width: 768px) {
    .app-header-inner {
        height: 56px;
        padding: 0 16px;
    }
}

.app-header-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.app-header-trigger {
    margin-left: -4px;
}

/* Mobile greeting */
.app-header-greeting {
    display: flex;
    flex-direction: column;
    line-height: 1.2;
}

.app-header-greeting-label {
    font-size: 11px;
    font-weight: 500;
    color: var(--muted-foreground);
    letter-spacing: 0.01em;
}

.app-header-greeting-name {
    font-size: 16px;
    font-weight: 700;
    color: var(--foreground);
    letter-spacing: -0.02em;
}

/* Right side */
.app-header-right {
    display: flex;
    align-items: center;
    gap: 8px;
}

.app-header-notif-btn {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 12px;
    color: var(--muted-foreground);
    transition: all 0.2s ease;
    background: transparent;
    border: none;
    cursor: pointer;
}

.app-header-notif-btn:hover {
    background: var(--accent);
    color: var(--foreground);
}

.app-header-notif-dot {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #ef4444;
    border: 2px solid white;
}

:is(.dark) .app-header-notif-dot {
    border-color: #09090b;
}

.app-header-avatar-btn {
    position: relative;
    width: auto;
    height: auto;
    padding: 2px;
    border-radius: 50%;
}

.app-header-avatar-btn:focus-within {
    ring: 2px;
    ring-color: var(--primary);
}
</style>
