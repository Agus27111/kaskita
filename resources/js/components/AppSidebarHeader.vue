<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { getInitials } from '@/composables/useInitials';
import type { BreadcrumbItem } from '@/types';
import { Bell } from 'lucide-vue-next';

const props = withDefaults(
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
    if (hour < 11) return 'Selamat Pagi';
    if (hour < 15) return 'Selamat Siang';
    if (hour < 18) return 'Selamat Sore';
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
                <div class="hidden md:block" v-if="breadcrumbs && breadcrumbs.length > 0">
                    <Breadcrumbs :breadcrumbs="breadcrumbs" />
                </div>

                <!-- Mobile: Greeting -->
                <div class="app-header-greeting md:hidden">
                    <span class="app-header-greeting-label">{{ greeting }} 👋</span>
                    <span class="app-header-greeting-name">{{ firstName }}</span>
                </div>
            </div>

            <!-- Right: Notification + Avatar -->
            <div class="app-header-right">
                <DropdownMenu>
                    <DropdownMenuTrigger :as-child="true">
                        <button class="app-header-notif-btn outline-none focus:outline-none">
                            <Bell class="h-5 w-5" />
                            <span class="app-header-notif-dot"></span>
                        </button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-80 md:w-[350px] rounded-2xl p-4 bg-white/95 dark:bg-zinc-900/95 backdrop-blur-md shadow-2xl border border-gray-100 dark:border-zinc-800 space-y-3">
                        <div class="flex items-center justify-between pb-2 border-b border-gray-50 dark:border-zinc-800/50">
                            <h4 class="text-xs font-black uppercase tracking-wider text-gray-900 dark:text-white">Notifikasi Keluarga</h4>
                            <span class="text-[9px] font-bold bg-rose-500/10 text-rose-600 px-2.5 py-0.5 rounded-full">4 Notif</span>
                        </div>
                        
                        <div class="space-y-3 max-h-[350px] overflow-y-auto">
                            <!-- Daily Charity (Sedekah) Reminder -->
                            <div class="flex gap-3 p-3 rounded-2xl bg-violet-500/10 border border-violet-500/20 hover:bg-violet-500/15 transition relative overflow-hidden group">
                                <div class="absolute -right-4 -bottom-4 w-12 h-12 bg-violet-500/10 rounded-full blur-xl pointer-events-none transition group-hover:scale-125"></div>
                                <div class="w-8 h-8 rounded-lg bg-violet-500/20 text-violet-600 dark:text-violet-400 flex items-center justify-center shrink-0 text-xs font-bold animate-pulse">
                                    🕌
                                </div>
                                <div class="space-y-1 flex-1 text-left relative z-10">
                                    <div class="flex items-center gap-1.5">
                                        <p class="text-xs font-black text-violet-700 dark:text-violet-400">Pengingat Sedekah Harian</p>
                                        <span class="text-[7px] font-extrabold bg-violet-500 text-white px-1 py-0.2 rounded-md uppercase tracking-wider">Berkah</span>
                                    </div>
                                    <p class="text-[10px] text-gray-700 dark:text-gray-300 font-extrabold leading-relaxed italic">
                                        "Sedekah itu tidak akan mengurangi harta." (HR. Muslim)
                                    </p>
                                    <p class="text-[9px] text-gray-500 dark:text-gray-400 font-medium leading-relaxed mt-1">
                                        Yuk, raih keberkahan finansial keluarga hari ini dengan berbagi kepada sesama.
                                    </p>
                                    <p class="text-[8px] text-violet-500 dark:text-violet-400 font-bold">Setiap Hari ✨</p>
                                </div>
                            </div>

                            <!-- Notif 1 -->
                            <div class="flex gap-3 p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-zinc-800/40 transition">
                                <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 text-xs font-bold">
                                    ⚠️
                                </div>
                                <div class="space-y-0.5 flex-1 text-left">
                                    <p class="text-xs font-black text-gray-900 dark:text-white">Batas Anggaran Kritis!</p>
                                    <p class="text-[10px] text-gray-500 dark:text-gray-400 font-medium leading-relaxed">Kategori <strong>Makanan</strong> Anda telah mencapai 75% pemakaian batas bulanan.</p>
                                    <p class="text-[8px] text-gray-400 dark:text-zinc-500 font-bold">1 jam yang lalu</p>
                                </div>
                            </div>

                            <!-- Notif 2 -->
                            <div class="flex gap-3 p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-zinc-800/40 transition">
                                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 text-xs font-bold">
                                    📈
                                </div>
                                <div class="space-y-0.5 flex-1 text-left">
                                    <p class="text-xs font-black text-gray-900 dark:text-white">Rasio Arus Kas Bulanan</p>
                                    <p class="text-[10px] text-gray-500 dark:text-gray-400 font-medium leading-relaxed">Kondisi keuangan keluarga Anda saat ini berkategori <strong>Cukup Sehat</strong> (62% terpakai).</p>
                                    <p class="text-[8px] text-gray-400 dark:text-zinc-500 font-bold">5 jam yang lalu</p>
                                </div>
                            </div>

                            <!-- Notif 3 -->
                            <div class="flex gap-3 p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-zinc-800/40 transition">
                                <div class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 text-xs font-bold">
                                    💰
                                </div>
                                <div class="space-y-0.5 flex-1 text-left">
                                    <p class="text-xs font-black text-gray-900 dark:text-white">Pemasukan Terdaftar</p>
                                    <p class="text-[10px] text-gray-500 dark:text-gray-400 font-medium leading-relaxed">Pemasukan sebesar <strong>Rp 15.000.000</strong> berhasil ditambahkan ke Dompet Utama oleh keluarga.</p>
                                    <p class="text-[8px] text-gray-400 dark:text-zinc-500 font-bold">1 hari yang lalu</p>
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
                            <Avatar class="size-9 overflow-hidden rounded-full ring-2 ring-white/80 dark:ring-zinc-800">
                                <AvatarImage
                                    v-if="auth.user.avatar"
                                    :src="auth.user.avatar"
                                    :alt="auth.user.name"
                                />
                                <AvatarFallback
                                    class="rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 text-white text-xs font-bold"
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
