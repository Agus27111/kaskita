<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import BottomNav from '@/components/BottomNav.vue';
import { Toaster } from '@/components/ui/sonner';
import type { BreadcrumbItem } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { watch, ref } from 'vue';
import { Bell, X } from 'lucide-vue-next';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const toast = ref<{ show: boolean; message: string; type: 'success' | 'info' | 'error' }>({
    show: false,
    message: '',
    type: 'success',
});

watch(() => page.props.flash, (flash: any) => {
    if (flash && (flash.success || flash.info || flash.error)) {
        toast.value = {
            show: true,
            message: flash.success || flash.error || flash.info,
            type: flash.success ? 'success' : (flash.error ? 'error' : 'info'),
        };
        setTimeout(() => {
            toast.value.show = false;
        }, 6000);
    }
}, { deep: true, immediate: true });
</script>

<template>
    <div class="relative min-h-screen">
        <AppShell variant="sidebar">
            <AppSidebar />
            <AppContent variant="sidebar" class="overflow-x-hidden pb-20 lg:pb-0">
                <AppSidebarHeader :breadcrumbs="breadcrumbs" />
                <slot />
            </AppContent>
            <Toaster />
        </AppShell>
        <BottomNav />

        <!-- Gorgeous Floating Custom Toast Notification -->
        <Teleport to="body">
            <Transition name="slide-fade">
                <div v-if="toast.show" class="fixed top-4 right-4 left-4 md:left-auto md:w-96 z-[999] p-4 rounded-2xl shadow-2xl flex items-start gap-3 border border-emerald-500/20 bg-white/95 dark:bg-zinc-900/95 backdrop-blur-md">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600 flex-shrink-0 animate-pulse">
                        <Bell class="h-5 w-5" />
                    </div>
                    <div class="flex-1 space-y-1">
                        <h4 class="font-bold text-sm text-gray-900 dark:text-white">Notifikasi KasKita</h4>
                        <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed font-semibold">
                            {{ toast.message }}
                        </p>
                    </div>
                    <button @click="toast.show = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition p-1">
                        <X class="h-4 w-4" />
                    </button>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<style scoped>
.slide-fade-enter-active {
  transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
.slide-fade-leave-active {
  transition: all 0.3s cubic-bezier(0.7, 0, 0.84, 0);
}
.slide-fade-enter-from {
  transform: translateY(-20px) scale(0.95);
  opacity: 0;
}
.slide-fade-leave-to {
  transform: translateY(-10px) scale(0.98);
  opacity: 0;
}
</style>
