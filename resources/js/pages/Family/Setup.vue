<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onUnmounted } from 'vue';

const activeTab = ref<'create' | 'join'>('create');

const createForm = useForm({
    name: '',
});

const joinForm = useForm({
    token: '',
});

const submitCreate = () => {
    createForm.post('/family', {
        onSuccess: () => createForm.reset(),
    });
};

const submitJoin = () => {
    joinForm.post('/family/join', {
        onSuccess: () => joinForm.reset(),
    });
};

const emoji = ref('👨‍👩‍👧‍👦');
const emojis = ['👨‍👩‍👧‍👦', '👨‍👩‍👧', '👨‍👩‍👦', '💑', '👪'];
let emojiIndex = 0;

const interval = setInterval(() => {
    emojiIndex = (emojiIndex + 1) % emojis.length;
    emoji.value = emojis[emojiIndex];
}, 2000);

onUnmounted(() => clearInterval(interval));
</script>

<template>
    <Head title="Setup Keluarga" />

    <div
        class="flex min-h-screen items-center justify-center bg-gradient-to-br from-emerald-50 via-white to-teal-50 p-4 dark:from-gray-950 dark:via-gray-900 dark:to-emerald-950"
    >
        <!-- Floating decorative elements -->
        <div class="pointer-events-none fixed inset-0 overflow-hidden">
            <div
                class="absolute top-20 left-10 h-72 w-72 animate-pulse rounded-full bg-emerald-200/30 blur-3xl dark:bg-emerald-800/20"
            ></div>
            <div
                class="absolute right-10 bottom-20 h-96 w-96 animate-pulse rounded-full bg-teal-200/30 blur-3xl dark:bg-teal-800/20"
                style="animation-delay: 1s"
            ></div>
        </div>

        <div class="relative w-full max-w-lg">
            <!-- Card -->
            <div
                class="overflow-hidden rounded-3xl border border-gray-200/50 bg-white/90 shadow-2xl backdrop-blur-xl dark:border-gray-800/80 dark:bg-zinc-900/90"
            >
                <!-- Header illustration -->
                <div
                    class="bg-gradient-to-r from-emerald-500 to-teal-500 px-8 py-10 text-center"
                >
                    <div
                        class="mb-4 animate-bounce text-6xl"
                        style="animation-duration: 2s"
                    >
                        {{ emoji }}
                    </div>
                    <h1
                        class="text-2xl font-black tracking-tight text-white md:text-3xl"
                    >
                        Setup Akun Keluarga
                    </h1>
                    <p class="mt-2 text-sm font-medium text-emerald-100">
                        Mulai langkah cerdas mengelola finansial keluarga Anda!
                    </p>
                </div>

                <!-- Tab Selectors -->
                <div class="flex border-b border-gray-100 dark:border-gray-800">
                    <button
                        @click="activeTab = 'create'"
                        class="flex-1 border-b-2 py-4 text-sm font-bold transition duration-200"
                        :class="
                            activeTab === 'create'
                                ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400'
                                : 'border-transparent text-gray-400 hover:text-gray-600 dark:hover:text-gray-300'
                        "
                    >
                        🏠 Buat Keluarga Baru
                    </button>
                    <button
                        @click="activeTab = 'join'"
                        class="flex-1 border-b-2 py-4 text-sm font-bold transition duration-200"
                        :class="
                            activeTab === 'join'
                                ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400'
                                : 'border-transparent text-gray-400 hover:text-gray-600 dark:hover:text-gray-300'
                        "
                    >
                        🔑 Gabung Keluarga Lama
                    </button>
                </div>

                <!-- Content Area -->
                <div class="p-8">
                    <!-- Tab 1: Buat Keluarga Baru -->
                    <div v-if="activeTab === 'create'" class="space-y-6">
                        <div>
                            <h2
                                class="text-lg font-bold text-gray-800 dark:text-white"
                            >
                                Beri Nama Keluarga Baru Anda
                            </h2>
                            <p
                                class="mt-1 text-xs leading-relaxed text-gray-500 dark:text-gray-400"
                            >
                                Setelah membuat keluarga, Anda akan otomatis
                                menjadi Admin Keluarga dan bisa mengundang
                                anggota keluarga lainnya (ayah, ibu, anak)
                                nanti.
                            </p>
                        </div>

                        <form @submit.prevent="submitCreate" class="space-y-4">
                            <div>
                                <label
                                    class="mb-2 block text-xs font-bold text-gray-500"
                                    >NAMA KELUARGA</label
                                >
                                <div class="relative">
                                    <span
                                        class="absolute top-1/2 left-4 -translate-y-1/2 text-lg"
                                        >🏠</span
                                    >
                                    <input
                                        v-model="createForm.name"
                                        type="text"
                                        placeholder="contoh: Keluarga Budi Santoso"
                                        class="w-full rounded-xl border border-gray-200 bg-gray-50 py-3.5 pr-4 pl-12 text-sm text-gray-800 placeholder-gray-400 transition outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-zinc-800/50 dark:text-white"
                                        required
                                        autofocus
                                    />
                                </div>
                                <p
                                    v-if="createForm.errors.name"
                                    class="mt-1 text-xs font-semibold text-red-500"
                                >
                                    {{ createForm.errors.name }}
                                </p>
                            </div>

                            <button
                                type="submit"
                                :disabled="
                                    createForm.processing ||
                                    !createForm.name.trim()
                                "
                                class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 px-6 py-3.5 font-bold text-white shadow-lg shadow-emerald-500/25 transition duration-200 hover:from-emerald-600 hover:to-teal-600 disabled:opacity-50"
                            >
                                <span v-if="createForm.processing"
                                    >Memproses...</span
                                >
                                <span v-else>🚀 Buat Keluarga & Mulai!</span>
                            </button>
                        </form>
                    </div>

                    <!-- Tab 2: Gabung Keluarga Lama -->
                    <div v-if="activeTab === 'join'" class="space-y-6">
                        <div>
                            <h2
                                class="text-lg font-bold text-gray-800 dark:text-white"
                            >
                                Masukkan Kode Undangan Keluarga
                            </h2>
                            <p
                                class="mt-1 text-xs leading-relaxed text-gray-500 dark:text-gray-400"
                            >
                                Tanyakan kode undangan (token) dari admin
                                keluarga Anda yang dikirim melalui email atau
                                menu "Kelola Anggota".
                            </p>
                        </div>

                        <form @submit.prevent="submitJoin" class="space-y-4">
                            <div>
                                <label
                                    class="mb-2 block text-xs font-bold text-gray-500"
                                    >KODE UNDANGAN / TOKEN</label
                                >
                                <div class="relative">
                                    <span
                                        class="absolute top-1/2 left-4 -translate-y-1/2 text-lg"
                                        >🔑</span
                                    >
                                    <input
                                        v-model="joinForm.token"
                                        type="text"
                                        placeholder="Masukkan kode unik undangan keluarga"
                                        class="w-full rounded-xl border border-gray-200 bg-gray-50 py-3.5 pr-4 pl-12 text-sm text-gray-800 placeholder-gray-400 transition outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-zinc-800/50 dark:text-white"
                                        required
                                    />
                                </div>
                                <p
                                    v-if="joinForm.errors.token"
                                    class="mt-1 text-xs font-semibold text-red-500"
                                >
                                    {{ joinForm.errors.token }}
                                </p>
                            </div>

                            <button
                                type="submit"
                                :disabled="
                                    joinForm.processing ||
                                    !joinForm.token.trim()
                                "
                                class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 px-6 py-3.5 font-bold text-white shadow-lg shadow-emerald-500/25 transition duration-200 hover:from-emerald-600 hover:to-teal-600 disabled:opacity-50"
                            >
                                <span v-if="joinForm.processing"
                                    >Memproses...</span
                                >
                                <span v-else>🤝 Bergabung ke Keluarga!</span>
                            </button>
                        </form>
                    </div>

                    <!-- Divider -->
                    <div
                        class="mt-8 border-t border-gray-100 pt-6 text-center dark:border-gray-800"
                    >
                        <p
                            class="text-xs font-medium text-gray-400 dark:text-gray-500"
                        >
                            Butuh bantuan? Silahkan hubungi admin utama keluarga
                            Anda.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Features preview -->
            <div class="mt-6 grid grid-cols-3 gap-3">
                <div
                    class="rounded-xl border border-gray-200/30 bg-white/60 p-3 text-center backdrop-blur-sm dark:border-gray-800/30 dark:bg-zinc-900/40"
                >
                    <div class="mb-1 text-2xl">💰</div>
                    <div
                        class="text-[10px] font-bold text-gray-600 dark:text-gray-400"
                    >
                        Catat Finansial
                    </div>
                </div>
                <div
                    class="rounded-xl border border-gray-200/30 bg-white/60 p-3 text-center backdrop-blur-sm dark:border-gray-800/30 dark:bg-zinc-900/40"
                >
                    <div class="mb-1 text-2xl">📊</div>
                    <div
                        class="text-[10px] font-bold text-gray-600 dark:text-gray-400"
                    >
                        Laporan Visual
                    </div>
                </div>
                <div
                    class="rounded-xl border border-gray-200/30 bg-white/60 p-3 text-center backdrop-blur-sm dark:border-gray-800/30 dark:bg-zinc-900/40"
                >
                    <div class="mb-1 text-2xl">🤝</div>
                    <div
                        class="text-[10px] font-bold text-gray-600 dark:text-gray-400"
                    >
                        Multi Anggota
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
