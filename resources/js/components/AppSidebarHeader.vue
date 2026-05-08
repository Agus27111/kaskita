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
                <button class="app-header-notif-btn">
                    <Bell class="h-5 w-5" />
                    <span class="app-header-notif-dot"></span>
                </button>

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
