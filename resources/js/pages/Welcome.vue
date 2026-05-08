<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login, register } from '@/routes';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);
</script>

<template>
    <Head title="Selamat Datang di KasKita">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    </Head>

    <div class="min-h-screen bg-gradient-to-br from-emerald-50 via-white to-teal-50 dark:from-gray-950 dark:via-zinc-900 dark:to-emerald-950 text-gray-800 dark:text-gray-100 font-sans selection:bg-emerald-500 selection:text-white relative overflow-hidden flex flex-col justify-between">
        <!-- Floating decorative elements -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-20 left-10 w-96 h-96 bg-emerald-200/30 dark:bg-emerald-800/10 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute bottom-20 right-10 w-[500px] h-[500px] bg-teal-200/20 dark:bg-teal-800/10 rounded-full blur-3xl animate-pulse" style="animation-delay: 2s"></div>
        </div>

        <!-- Header -->
        <header class="w-full max-w-7xl mx-auto px-6 py-6 flex items-center justify-between relative z-10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-500 flex items-center justify-center text-white text-xl shadow-lg shadow-emerald-500/20">
                    🏠
                </div>
                <span class="text-xl font-extrabold tracking-tight bg-gradient-to-r from-emerald-600 to-teal-600 dark:from-emerald-400 dark:to-teal-400 bg-clip-text text-transparent">
                    KasKita
                </span>
            </div>

            <nav class="flex items-center gap-4">
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-600 text-white font-bold text-sm px-5 py-2.5 rounded-2xl transition shadow-lg shadow-emerald-500/10"
                >
                    Masuk Dashboard
                </Link>
                <template v-else>
                    <Link
                        :href="login()"
                        class="text-gray-600 dark:text-gray-300 hover:text-emerald-600 dark:hover:text-emerald-400 font-bold text-sm px-4 py-2 transition"
                    >
                        Masuk
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="register()"
                        class="bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-600 text-white font-bold text-sm px-5 py-2.5 rounded-2xl transition shadow-lg shadow-emerald-500/10"
                    >
                        Daftar Akun
                    </Link>
                </template>
            </nav>
        </header>

        <!-- Main Content -->
        <main class="w-full max-w-7xl mx-auto px-6 py-12 md:py-20 flex flex-col lg:flex-row items-center gap-16 relative z-10 flex-1 justify-center">
            <!-- Left: Hero Text -->
            <div class="flex-1 space-y-8 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-xs font-bold tracking-wider uppercase">
                    ✨ APLIKASI KEUANGAN KELUARGA NOMOR #1
                </div>

                <h1 class="text-4xl md:text-6xl font-black tracking-tight leading-none text-gray-900 dark:text-white max-w-2xl mx-auto lg:mx-0">
                    Kelola Finansial Keluarga, <br />
                    <span class="bg-gradient-to-r from-emerald-500 to-teal-500 bg-clip-text text-transparent"> Lebih Cerdas & Harmonis </span>
                </h1>

                <p class="text-sm md:text-lg text-gray-500 dark:text-gray-400 leading-relaxed max-w-xl mx-auto lg:mx-0 font-medium">
                    Kelola pencatatan keuangan bersama seluruh anggota keluarga (Ayah, Ibu, Anak) secara real-time, buat anggaran hemat kustom, dapatkan asisten audit asisten AI cerdas berbasis Aturan Emas Finansial dunia, dalam satu aplikasi keluarga KasKita yang indah dan harmonis.
                </p>

                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-4">
                    <Link
                        v-if="!$page.props.auth.user"
                        :href="register()"
                        class="bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-extrabold px-8 py-4 rounded-2xl shadow-xl shadow-emerald-500/20 hover:shadow-emerald-500/35 transition transform hover:-translate-y-0.5 text-base"
                    >
                        Mulai Gratis Sekarang 🚀
                    </Link>
                    <Link
                        v-else
                        :href="dashboard()"
                        class="bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-extrabold px-8 py-4 rounded-2xl shadow-xl shadow-emerald-500/20 hover:shadow-emerald-500/35 transition transform hover:-translate-y-0.5 text-base"
                    >
                        Buka Dashboard KasKita
                    </Link>
                </div>
            </div>

            <!-- Right: Interactive Feature Grid Preview -->
            <div class="flex-1 w-full max-w-lg grid grid-cols-2 gap-4">
                <div class="bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-gray-200/40 dark:border-zinc-800/80 p-5 rounded-3xl shadow-xl hover:shadow-2xl transition transform hover:-translate-y-1">
                    <div class="text-3xl mb-3">👨‍👩‍👧‍👦</div>
                    <h3 class="font-extrabold text-xs text-gray-900 dark:text-white mb-1">Pencatatan Bersama</h3>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed font-semibold">Satu buku kas keluarga untuk diisi bersama oleh Ayah, Ibu, dan Anak secara transparan.</p>
                </div>

                <div class="bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-gray-200/40 dark:border-zinc-800/80 p-5 rounded-3xl shadow-xl hover:shadow-2xl transition transform hover:-translate-y-1 mt-4">
                    <div class="text-3xl mb-3">🤖</div>
                    <h3 class="font-extrabold text-xs text-gray-900 dark:text-white mb-1">Asisten AI Finansial</h3>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed font-semibold">Audit arus kas otomatis berbasis AI berdasarkan Aturan Emas 50/30/20.</p>
                </div>

                <div class="bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-gray-200/40 dark:border-zinc-800/80 p-5 rounded-3xl shadow-xl hover:shadow-2xl transition transform hover:-translate-y-1">
                    <div class="text-3xl mb-3">🏦</div>
                    <h3 class="font-extrabold text-xs text-gray-900 dark:text-white mb-1">Dompet Kustom</h3>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed font-semibold">Kelola berbagai jenis dompet (Tunai, Bank, e-Wallet) dengan ikon emoji pilihan yang lucu.</p>
                </div>

                <div class="bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-gray-200/40 dark:border-zinc-800/80 p-5 rounded-3xl shadow-xl hover:shadow-2xl transition transform hover:-translate-y-1 mt-4">
                    <div class="text-3xl mb-3">🎯</div>
                    <h3 class="font-extrabold text-xs text-gray-900 dark:text-white mb-1">Sistem Budgeting</h3>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed font-semibold">Batasi anggaran belanja per kategori untuk mendisiplinkan pengeluaran bulanan.</p>
                </div>

                <div class="bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-gray-200/40 dark:border-zinc-800/80 p-5 rounded-3xl shadow-xl hover:shadow-2xl transition transform hover:-translate-y-1">
                    <div class="text-3xl mb-3">🔔</div>
                    <h3 class="font-extrabold text-xs text-gray-900 dark:text-white mb-1">Notifikasi Real-Time</h3>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed font-semibold">Terima notifikasi instan setiap ada anggota keluarga yang memasukkan transaksi baru.</p>
                </div>

                <div class="bg-white/80 dark:bg-zinc-900/80 backdrop-blur-xl border border-gray-200/40 dark:border-zinc-800/80 p-5 rounded-3xl shadow-xl hover:shadow-2xl transition transform hover:-translate-y-1 mt-4">
                    <div class="text-3xl mb-3">📈</div>
                    <h3 class="font-extrabold text-xs text-gray-900 dark:text-white mb-1">Tren Interaktif</h3>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed font-semibold">Bagan diagram alokasi katori dan grafik batang double-bar cashflow yang sangat indah.</p>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="w-full max-w-7xl mx-auto px-6 py-8 border-t border-gray-100 dark:border-zinc-800/80 text-center relative z-10">
            <p class="text-xs text-gray-400 dark:text-zinc-500 font-bold">
                &copy; 2026 KasKita. Dibuat dengan penuh cinta untuk keharmonisan finansial keluarga Indonesia. ❤️
            </p>
        </footer>
    </div>
</template>

<style scoped>
body {
    font-family: 'Plus Jakarta Sans', sans-serif;
}
</style>
