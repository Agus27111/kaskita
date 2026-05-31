<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { Bell, Inbox, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
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
import type { AppNotifications, BreadcrumbItem } from '@/types';

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
const isClearingNotifications = ref(false);
const emptyNotifications: AppNotifications = {
    items: [],
    count: 0,
};

const notifications = computed(
    () => page.props.notifications ?? emptyNotifications,
);

const notificationItems = computed(() => notifications.value.items);
const notificationCount = computed(() => notifications.value.count);
const hasNotifications = computed(() => notificationCount.value > 0);
const notificationBadge = computed(() =>
    notificationCount.value > 9 ? '9+' : String(notificationCount.value),
);
const notificationCountLabel = computed(
    () => `${notificationCount.value} Notif`,
);

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

const clearNotifications = () => {
    if (!hasNotifications.value || isClearingNotifications.value) {
        return;
    }

    router.delete('/notifications', {
        preserveScroll: true,
        onStart: () => {
            isClearingNotifications.value = true;
        },
        onFinish: () => {
            isClearingNotifications.value = false;
        },
    });
};
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
                            type="button"
                            aria-label="Notifikasi"
                            class="app-header-notif-btn outline-none focus:outline-none"
                        >
                            <Bell class="h-5 w-5" />
                            <span
                                v-if="hasNotifications"
                                class="app-header-notif-dot"
                            >
                                {{ notificationBadge }}
                            </span>
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
                                v-if="hasNotifications"
                                class="rounded-full bg-rose-500/10 px-2.5 py-0.5 text-[9px] font-bold text-rose-600"
                                >{{ notificationCountLabel }}</span
                            >
                        </div>

                        <div
                            v-if="notificationItems.length > 0"
                            class="max-h-[350px] space-y-2 overflow-y-auto"
                        >
                            <div
                                v-for="notification in notificationItems"
                                :key="notification.id"
                                class="flex gap-3 rounded-xl p-2 transition hover:bg-gray-50 dark:hover:bg-zinc-800/40"
                            >
                                <div
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400"
                                >
                                    <Bell class="h-4 w-4" />
                                </div>
                                <div class="min-w-0 flex-1 space-y-1 text-left">
                                    <p
                                        class="text-xs leading-relaxed font-bold break-words text-gray-900 dark:text-white"
                                    >
                                        {{ notification.message }}
                                    </p>
                                    <p
                                        class="text-[9px] font-bold text-gray-400 dark:text-zinc-500"
                                    >
                                        {{ notification.sent_at_human }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div
                            v-else
                            class="flex flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-gray-200 px-4 py-8 text-center dark:border-zinc-800"
                        >
                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-zinc-800 dark:text-zinc-400"
                            >
                                <Inbox class="h-4 w-4" />
                            </div>
                            <p
                                class="text-xs font-bold text-gray-500 dark:text-zinc-400"
                            >
                                Belum ada notifikasi
                            </p>
                        </div>

                        <button
                            v-if="hasNotifications"
                            type="button"
                            :disabled="isClearingNotifications"
                            class="flex w-full items-center justify-center gap-2 rounded-xl border border-rose-500/20 bg-rose-500/10 px-3 py-2 text-xs font-black text-rose-600 transition hover:bg-rose-500/15 disabled:cursor-not-allowed disabled:opacity-60 dark:text-rose-400"
                            @click="clearNotifications"
                        >
                            <Trash2 class="h-4 w-4" />
                            <span>
                                {{
                                    isClearingNotifications
                                        ? 'Membersihkan...'
                                        : 'Bersihkan notifikasi'
                                }}
                            </span>
                        </button>
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
    top: 5px;
    right: 5px;
    display: flex;
    min-width: 16px;
    height: 16px;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    background: #ef4444;
    border: 2px solid white;
    padding: 0 3px;
    color: white;
    font-size: 9px;
    font-weight: 800;
    line-height: 1;
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
