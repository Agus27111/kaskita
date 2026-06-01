<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 40 40"
        :class="className"
        v-bind="$attrs"
    >
        <rect x="3" y="3" width="34" height="34" rx="11" fill="currentColor" />
        <path
            fill="#ffffff"
            d="M11 11h5.2v7.4L25.9 11H32l-8.8 8.2L32 29h-6.1l-5.7-6.2-4 3.7V29H11V11Z"
        />
        <circle cx="28" cy="13" r="2.1" fill="#ffffff" opacity="0.85" />
    </svg>
</template>
