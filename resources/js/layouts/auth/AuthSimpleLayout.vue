<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-emerald-50 via-white to-teal-50 dark:from-gray-950 dark:via-zinc-900 dark:to-emerald-950 p-6 relative overflow-hidden"
    >
        <!-- Floating decorative elements -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-10 left-10 w-72 h-72 bg-emerald-200/20 dark:bg-emerald-800/10 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute bottom-10 right-10 w-96 h-96 bg-teal-200/20 dark:bg-teal-800/10 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s"></div>
        </div>

        <div class="w-full max-w-[380px] relative z-10">
            <div class="bg-white/90 dark:bg-zinc-900/90 backdrop-blur-xl rounded-3xl border border-gray-200/50 dark:border-zinc-800/80 p-7 shadow-2xl flex flex-col gap-5">
                <div class="flex flex-col items-center gap-4">
                    <Link
                        :href="home()"
                        class="flex flex-col items-center gap-2 font-medium"
                    >
                        <div
                            class="mb-1 flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-2xl"
                        >
                            🏠
                        </div>
                        <span class="sr-only">{{ title }}</span>
                    </Link>
                    <div class="space-y-1.5 text-center">
                        <h1 class="text-2xl font-black tracking-tight text-gray-900 dark:text-white">{{ title }}</h1>
                        <p class="text-center text-xs font-semibold text-gray-500 dark:text-zinc-400">
                            {{ description }}
                        </p>
                    </div>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
