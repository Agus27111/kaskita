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
        <link
            rel="preconnect"
            href="https://fonts.gstatic.com"
            crossorigin="anonymous"
        />
        <link
            href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div
        class="relative flex min-h-screen flex-col justify-between overflow-hidden bg-gradient-to-br from-emerald-50 via-white to-teal-50 font-sans text-gray-800 selection:bg-emerald-500 selection:text-white dark:from-gray-950 dark:via-zinc-900 dark:to-emerald-950 dark:text-gray-100"
    >
        <!-- Floating decorative elements -->
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div
                class="absolute top-20 left-10 h-96 w-96 animate-pulse rounded-full bg-emerald-200/30 blur-3xl dark:bg-emerald-800/10"
            ></div>
            <div
                class="absolute right-10 bottom-20 h-[500px] w-[500px] animate-pulse rounded-full bg-teal-200/20 blur-3xl dark:bg-teal-800/10"
                style="animation-delay: 2s"
            ></div>
        </div>

        <!-- Header -->
        <header
            class="relative z-10 mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-6"
        >
            <div class="flex items-center gap-3">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-500 text-xl text-white shadow-lg shadow-emerald-500/20"
                >
                    🏠
                </div>
                <span
                    class="bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-xl font-extrabold tracking-tight text-transparent dark:from-emerald-400 dark:to-teal-400"
                >
                    KasKita
                </span>
            </div>

            <nav class="flex items-center gap-4">
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="rounded-2xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-emerald-500/10 transition hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-600"
                >
                    Masuk Dashboard
                </Link>
                <template v-else>
                    <Link
                        :href="login()"
                        class="px-4 py-2 text-sm font-bold text-gray-600 transition hover:text-emerald-600 dark:text-gray-300 dark:hover:text-emerald-400"
                    >
                        Masuk
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="register()"
                        class="rounded-2xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-emerald-500/10 transition hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-600"
                    >
                        Daftar Akun
                    </Link>
                </template>
            </nav>
        </header>

        <!-- Main Content -->
        <main
            class="relative z-10 mx-auto flex w-full max-w-7xl flex-1 flex-col items-center justify-center gap-16 px-6 py-12 md:py-20 lg:flex-row"
        >
            <!-- Left: Hero Text -->
            <div class="flex-1 space-y-8 text-center lg:text-left">
                <div
                    class="inline-flex items-center gap-2 rounded-full bg-emerald-500/10 px-4 py-2 text-xs font-bold tracking-wider text-emerald-600 uppercase dark:text-emerald-400"
                >
                    ✨ APLIKASI KEUANGAN KELUARGA NOMOR #1
                </div>

                <h1
                    class="mx-auto max-w-2xl text-4xl leading-none font-black tracking-tight text-gray-900 md:text-6xl lg:mx-0 dark:text-white"
                >
                    Kelola Finansial Keluarga, <br />
                    <span
                        class="bg-gradient-to-r from-emerald-500 to-teal-500 bg-clip-text text-transparent"
                    >
                        Lebih Cerdas & Harmonis
                    </span>
                </h1>

                <p
                    class="mx-auto max-w-xl text-sm leading-relaxed font-medium text-gray-500 md:text-lg lg:mx-0 dark:text-gray-400"
                >
                    Kelola pencatatan keuangan bersama seluruh anggota keluarga
                    (Ayah, Ibu, Anak) secara real-time, buat anggaran hemat
                    kustom, dapatkan asisten audit asisten AI cerdas berbasis
                    Aturan Emas Finansial dunia, dalam satu aplikasi keluarga
                    KasKita yang indah dan harmonis.
                </p>

                <div
                    class="flex flex-wrap items-center justify-center gap-4 lg:justify-start"
                >
                    <Link
                        v-if="!$page.props.auth.user"
                        :href="register()"
                        class="transform rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 px-8 py-4 text-base font-extrabold text-white shadow-xl shadow-emerald-500/20 transition hover:-translate-y-0.5 hover:from-emerald-600 hover:to-teal-600 hover:shadow-emerald-500/35"
                    >
                        Mulai Gratis Sekarang 🚀
                    </Link>
                    <Link
                        v-else
                        :href="dashboard()"
                        class="transform rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 px-8 py-4 text-base font-extrabold text-white shadow-xl shadow-emerald-500/20 transition hover:-translate-y-0.5 hover:from-emerald-600 hover:to-teal-600 hover:shadow-emerald-500/35"
                    >
                        Buka Dashboard KasKita
                    </Link>
                </div>
            </div>

            <!-- Right: Interactive Feature Grid Preview -->
            <div class="grid w-full max-w-lg flex-1 grid-cols-2 gap-4">
                <div
                    class="transform rounded-3xl border border-gray-200/40 bg-white/80 p-5 shadow-xl backdrop-blur-xl transition hover:-translate-y-1 hover:shadow-2xl dark:border-zinc-800/80 dark:bg-zinc-900/80"
                >
                    <div class="mb-3 text-3xl">👨‍👩‍👧‍👦</div>
                    <h3
                        class="mb-1 text-xs font-extrabold text-gray-900 dark:text-white"
                    >
                        Pencatatan Bersama
                    </h3>
                    <p
                        class="text-[11px] leading-relaxed font-semibold text-gray-500 dark:text-gray-400"
                    >
                        Satu buku kas keluarga untuk diisi bersama oleh Ayah,
                        Ibu, dan Anak secara transparan.
                    </p>
                </div>

                <div
                    class="mt-4 transform rounded-3xl border border-gray-200/40 bg-white/80 p-5 shadow-xl backdrop-blur-xl transition hover:-translate-y-1 hover:shadow-2xl dark:border-zinc-800/80 dark:bg-zinc-900/80"
                >
                    <div class="mb-3 text-3xl">🤖</div>
                    <h3
                        class="mb-1 text-xs font-extrabold text-gray-900 dark:text-white"
                    >
                        Asisten AI Finansial
                    </h3>
                    <p
                        class="text-[11px] leading-relaxed font-semibold text-gray-500 dark:text-gray-400"
                    >
                        Audit arus kas otomatis berbasis AI berdasarkan Aturan
                        Emas 50/30/20.
                    </p>
                </div>

                <div
                    class="transform rounded-3xl border border-gray-200/40 bg-white/80 p-5 shadow-xl backdrop-blur-xl transition hover:-translate-y-1 hover:shadow-2xl dark:border-zinc-800/80 dark:bg-zinc-900/80"
                >
                    <div class="mb-3 text-3xl">🏦</div>
                    <h3
                        class="mb-1 text-xs font-extrabold text-gray-900 dark:text-white"
                    >
                        Dompet Kustom
                    </h3>
                    <p
                        class="text-[11px] leading-relaxed font-semibold text-gray-500 dark:text-gray-400"
                    >
                        Kelola berbagai jenis dompet (Tunai, Bank, e-Wallet)
                        dengan ikon emoji pilihan yang lucu.
                    </p>
                </div>

                <div
                    class="mt-4 transform rounded-3xl border border-gray-200/40 bg-white/80 p-5 shadow-xl backdrop-blur-xl transition hover:-translate-y-1 hover:shadow-2xl dark:border-zinc-800/80 dark:bg-zinc-900/80"
                >
                    <div class="mb-3 text-3xl">🎯</div>
                    <h3
                        class="mb-1 text-xs font-extrabold text-gray-900 dark:text-white"
                    >
                        Sistem Budgeting
                    </h3>
                    <p
                        class="text-[11px] leading-relaxed font-semibold text-gray-500 dark:text-gray-400"
                    >
                        Batasi anggaran belanja per kategori untuk
                        mendisiplinkan pengeluaran bulanan.
                    </p>
                </div>

                <div
                    class="transform rounded-3xl border border-gray-200/40 bg-white/80 p-5 shadow-xl backdrop-blur-xl transition hover:-translate-y-1 hover:shadow-2xl dark:border-zinc-800/80 dark:bg-zinc-900/80"
                >
                    <div class="mb-3 text-3xl">🔔</div>
                    <h3
                        class="mb-1 text-xs font-extrabold text-gray-900 dark:text-white"
                    >
                        Notifikasi Real-Time
                    </h3>
                    <p
                        class="text-[11px] leading-relaxed font-semibold text-gray-500 dark:text-gray-400"
                    >
                        Terima notifikasi instan setiap ada anggota keluarga
                        yang memasukkan transaksi baru.
                    </p>
                </div>

                <div
                    class="mt-4 transform rounded-3xl border border-gray-200/40 bg-white/80 p-5 shadow-xl backdrop-blur-xl transition hover:-translate-y-1 hover:shadow-2xl dark:border-zinc-800/80 dark:bg-zinc-900/80"
                >
                    <div class="mb-3 text-3xl">📈</div>
                    <h3
                        class="mb-1 text-xs font-extrabold text-gray-900 dark:text-white"
                    >
                        Tren Interaktif
                    </h3>
                    <p
                        class="text-[11px] leading-relaxed font-semibold text-gray-500 dark:text-gray-400"
                    >
                        Bagan diagram alokasi katori dan grafik batang
                        double-bar cashflow yang sangat indah.
                    </p>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer
            class="relative z-10 mx-auto w-full max-w-7xl border-t border-gray-100 px-6 py-8 text-center dark:border-zinc-800/80"
        >
            <p class="text-xs font-bold text-gray-400 dark:text-zinc-500">
                &copy; 2026 KasKita. Dibuat dengan penuh cinta untuk
                keharmonisan finansial keluarga Indonesia. ❤️
            </p>
        </footer>
    </div>
</template>

<style scoped>
body {
    font-family: 'Plus Jakarta Sans', sans-serif;
}
</style>
