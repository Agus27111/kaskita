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
        class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-gradient-to-br from-emerald-50 via-white to-teal-50 p-6 dark:from-gray-950 dark:via-zinc-900 dark:to-emerald-950"
    >
        <!-- Floating decorative elements -->
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div
                class="absolute top-10 left-10 h-72 w-72 animate-pulse rounded-full bg-emerald-200/20 blur-3xl dark:bg-emerald-800/10"
            ></div>
            <div
                class="absolute right-10 bottom-10 h-96 w-96 animate-pulse rounded-full bg-teal-200/20 blur-3xl dark:bg-teal-800/10"
                style="animation-delay: 1s"
            ></div>
        </div>

        <div class="relative z-10 w-full max-w-[380px]">
            <div
                class="flex flex-col gap-5 rounded-3xl border border-gray-200/50 bg-white/90 p-7 shadow-2xl backdrop-blur-xl dark:border-zinc-800/80 dark:bg-zinc-900/90"
            >
                <div class="flex flex-col items-center gap-4">
                    <Link
                        :href="home()"
                        class="flex flex-col items-center gap-2 font-medium"
                    >
                        <div
                            class="mb-1 flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-500/10 text-2xl text-emerald-600 dark:text-emerald-400"
                        >
                            🏠
                        </div>
                        <span class="sr-only">{{ title }}</span>
                    </Link>
                    <div class="space-y-1.5 text-center">
                        <h1
                            class="text-2xl font-black tracking-tight text-gray-900 dark:text-white"
                        >
                            {{ title }}
                        </h1>
                        <p
                            class="text-center text-xs font-semibold text-gray-500 dark:text-zinc-400"
                        >
                            {{ description }}
                        </p>
                    </div>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
