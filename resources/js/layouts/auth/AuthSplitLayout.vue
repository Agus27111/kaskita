<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

const page = usePage();
const name = page.props.name;

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="auth-layout">
        <!-- Left branding panel (desktop) -->
        <div class="auth-brand-panel">
            <div class="auth-brand-bg"></div>
            <div class="auth-brand-overlay"></div>

            <!-- Floating orbs for depth -->
            <div class="auth-orb auth-orb--1"></div>
            <div class="auth-orb auth-orb--2"></div>
            <div class="auth-orb auth-orb--3"></div>

            <!-- Brand content -->
            <div class="auth-brand-content">
                <Link :href="home()" class="auth-brand-logo">
                    <div class="auth-brand-logo-icon">
                        <AppLogoIcon class="size-7 fill-current text-white" />
                    </div>
                    <span class="auth-brand-name">{{ name }}</span>
                </Link>

                <div class="auth-brand-hero">
                    <h2 class="auth-brand-tagline">
                        Kelola Keuangan<br />
                        <span class="auth-brand-tagline-accent">Keluarga Anda</span>
                    </h2>
                    <p class="auth-brand-desc">
                        Catat pemasukan, pengeluaran, dan pantau kesehatan
                        keuangan keluarga secara real-time.
                    </p>
                </div>

                <!-- Feature badges -->
                <div class="auth-brand-features">
                    <div class="auth-feature-badge">
                        <span class="auth-feature-emoji">💰</span>
                        <span>Multi-Dompet</span>
                    </div>
                    <div class="auth-feature-badge">
                        <span class="auth-feature-emoji">👨‍👩‍👧‍👦</span>
                        <span>Kolaborasi Keluarga</span>
                    </div>
                    <div class="auth-feature-badge">
                        <span class="auth-feature-emoji">📊</span>
                        <span>Laporan Otomatis</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right form panel -->
        <div class="auth-form-panel">
            <!-- Mobile logo -->
            <div class="auth-mobile-logo lg:hidden">
                <Link :href="home()" class="auth-mobile-logo-link">
                    <div class="auth-mobile-logo-icon">
                        <AppLogoIcon class="size-5 fill-current text-white" />
                    </div>
                    <span class="auth-mobile-logo-name">{{ name }}</span>
                </Link>
            </div>

            <div class="auth-form-wrapper">
                <div class="auth-form-header" v-if="title || description">
                    <h1 class="auth-form-title" v-if="title">{{ title }}</h1>
                    <p class="auth-form-desc" v-if="description">{{ description }}</p>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>

<style scoped>
/* ─── Layout ─── */
.auth-layout {
    display: grid;
    min-height: 100dvh;
    grid-template-columns: 1fr;
}

@media (min-width: 1024px) {
    .auth-layout {
        grid-template-columns: 1fr 1fr;
    }
}

/* ─── Brand Panel (Left) ─── */
.auth-brand-panel {
    display: none;
    position: relative;
    overflow: hidden;
}

@media (min-width: 1024px) {
    .auth-brand-panel {
        display: flex;
        flex-direction: column;
    }
}

.auth-brand-bg {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, #064e3b 0%, #065f46 30%, #047857 60%, #059669 100%);
}

.auth-brand-overlay {
    position: absolute;
    inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

/* Floating Orbs */
.auth-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.3;
}

.auth-orb--1 {
    width: 300px;
    height: 300px;
    background: #34d399;
    top: -50px;
    right: -80px;
    animation: orbFloat1 8s ease-in-out infinite;
}

.auth-orb--2 {
    width: 200px;
    height: 200px;
    background: #6ee7b7;
    bottom: 100px;
    left: -60px;
    animation: orbFloat2 10s ease-in-out infinite;
}

.auth-orb--3 {
    width: 150px;
    height: 150px;
    background: #a7f3d0;
    bottom: -30px;
    right: 30%;
    animation: orbFloat3 12s ease-in-out infinite;
}

@keyframes orbFloat1 {
    0%, 100% { transform: translate(0, 0) scale(1); }
    50% { transform: translate(-30px, 40px) scale(1.1); }
}

@keyframes orbFloat2 {
    0%, 100% { transform: translate(0, 0) scale(1); }
    50% { transform: translate(20px, -30px) scale(0.9); }
}

@keyframes orbFloat3 {
    0%, 100% { transform: translate(0, 0) scale(1); }
    50% { transform: translate(-20px, -20px) scale(1.15); }
}

/* Brand Content */
.auth-brand-content {
    position: relative;
    z-index: 10;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100%;
    padding: 40px;
}

.auth-brand-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    color: white;
}

.auth-brand-logo-icon {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    border-radius: 14px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.auth-brand-name {
    font-size: 20px;
    font-weight: 700;
    letter-spacing: -0.02em;
}

.auth-brand-hero {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    max-width: 400px;
}

.auth-brand-tagline {
    font-size: 40px;
    font-weight: 800;
    line-height: 1.1;
    color: white;
    letter-spacing: -0.03em;
}

.auth-brand-tagline-accent {
    background: linear-gradient(135deg, #a7f3d0, #6ee7b7);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.auth-brand-desc {
    margin-top: 16px;
    font-size: 15px;
    line-height: 1.6;
    color: rgba(255, 255, 255, 0.7);
}

/* Feature Badges */
.auth-brand-features {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.auth-feature-badge {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 100px;
    font-size: 13px;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.9);
}

.auth-feature-emoji {
    font-size: 14px;
}

/* ─── Form Panel (Right) ─── */
.auth-form-panel {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 32px 24px;
    background: var(--background);
}

@media (min-width: 640px) {
    .auth-form-panel {
        padding: 40px;
    }
}

/* Mobile logo */
.auth-mobile-logo {
    margin-bottom: 40px;
}

.auth-mobile-logo-link {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    color: var(--foreground);
}

.auth-mobile-logo-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #064e3b, #059669);
    border-radius: 12px;
}

.auth-mobile-logo-name {
    font-size: 18px;
    font-weight: 700;
    letter-spacing: -0.02em;
}

/* Form wrapper */
.auth-form-wrapper {
    width: 100%;
    max-width: 380px;
}

.auth-form-header {
    margin-bottom: 32px;
    text-align: center;
}

.auth-form-title {
    font-size: 22px;
    font-weight: 700;
    letter-spacing: -0.02em;
    color: var(--foreground);
}

.auth-form-desc {
    margin-top: 8px;
    font-size: 14px;
    color: var(--muted-foreground);
}
</style>
