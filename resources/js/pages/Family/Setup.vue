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

    <div class="min-h-screen bg-gradient-to-br from-emerald-50 via-white to-teal-50 dark:from-gray-950 dark:via-gray-900 dark:to-emerald-950 flex items-center justify-center p-4">
        <!-- Floating decorative elements -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-20 left-10 w-72 h-72 bg-emerald-200/30 dark:bg-emerald-800/20 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute bottom-20 right-10 w-96 h-96 bg-teal-200/30 dark:bg-teal-800/20 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s"></div>
        </div>

        <div class="relative w-full max-w-lg">
            <!-- Card -->
            <div class="bg-white/90 dark:bg-zinc-900/90 backdrop-blur-xl rounded-3xl shadow-2xl border border-gray-200/50 dark:border-gray-800/80 overflow-hidden">
                <!-- Header illustration -->
                <div class="bg-gradient-to-r from-emerald-500 to-teal-500 px-8 py-10 text-center">
                    <div class="text-6xl mb-4 animate-bounce" style="animation-duration: 2s">
                        {{ emoji }}
                    </div>
                    <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">
                        Setup Akun Keluarga
                    </h1>
                    <p class="text-emerald-100 mt-2 text-sm font-medium">
                        Mulai langkah cerdas mengelola finansial keluarga Anda!
                    </p>
                </div>

                <!-- Tab Selectors -->
                <div class="flex border-b border-gray-100 dark:border-gray-800">
                    <button 
                        @click="activeTab = 'create'"
                        class="flex-1 py-4 text-sm font-bold border-b-2 transition duration-200"
                        :class="activeTab === 'create' ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400' : 'border-transparent text-gray-400 hover:text-gray-600 dark:hover:text-gray-300'"
                    >
                        🏠 Buat Keluarga Baru
                    </button>
                    <button 
                        @click="activeTab = 'join'"
                        class="flex-1 py-4 text-sm font-bold border-b-2 transition duration-200"
                        :class="activeTab === 'join' ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400' : 'border-transparent text-gray-400 hover:text-gray-600 dark:hover:text-gray-300'"
                    >
                        🔑 Gabung Keluarga Lama
                    </button>
                </div>

                <!-- Content Area -->
                <div class="p-8">
                    <!-- Tab 1: Buat Keluarga Baru -->
                    <div v-if="activeTab === 'create'" class="space-y-6">
                        <div>
                            <h2 class="text-lg font-bold text-gray-800 dark:text-white">
                                Beri Nama Keluarga Baru Anda
                            </h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">
                                Setelah membuat keluarga, Anda akan otomatis menjadi Admin Keluarga dan bisa mengundang anggota keluarga lainnya (ayah, ibu, anak) nanti.
                            </p>
                        </div>

                        <form @submit.prevent="submitCreate" class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-2">NAMA KELUARGA</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-lg">🏠</span>
                                    <input
                                        v-model="createForm.name"
                                        type="text"
                                        placeholder="contoh: Keluarga Budi Santoso"
                                        class="w-full pl-12 pr-4 py-3.5 bg-gray-50 dark:bg-zinc-800/50 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition text-sm text-gray-800 dark:text-white placeholder-gray-400"
                                        required
                                        autofocus
                                    />
                                </div>
                                <p v-if="createForm.errors.name" class="text-red-500 text-xs mt-1 font-semibold">{{ createForm.errors.name }}</p>
                            </div>

                            <button
                                type="submit"
                                :disabled="createForm.processing || !createForm.name.trim()"
                                class="w-full bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-bold py-3.5 px-6 rounded-xl transition duration-200 shadow-lg shadow-emerald-500/25 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
                            >
                                <span v-if="createForm.processing">Memproses...</span>
                                <span v-else>🚀 Buat Keluarga & Mulai!</span>
                            </button>
                        </form>
                    </div>

                    <!-- Tab 2: Gabung Keluarga Lama -->
                    <div v-if="activeTab === 'join'" class="space-y-6">
                        <div>
                            <h2 class="text-lg font-bold text-gray-800 dark:text-white">
                                Masukkan Kode Undangan Keluarga
                            </h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">
                                Tanyakan kode undangan (token) dari admin keluarga Anda yang dikirim melalui email atau menu "Kelola Anggota".
                            </p>
                        </div>

                        <form @submit.prevent="submitJoin" class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-2">KODE UNDANGAN / TOKEN</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-lg">🔑</span>
                                    <input
                                        v-model="joinForm.token"
                                        type="text"
                                        placeholder="Masukkan kode unik undangan keluarga"
                                        class="w-full pl-12 pr-4 py-3.5 bg-gray-50 dark:bg-zinc-800/50 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition text-sm text-gray-800 dark:text-white placeholder-gray-400"
                                        required
                                    />
                                </div>
                                <p v-if="joinForm.errors.token" class="text-red-500 text-xs mt-1 font-semibold">{{ joinForm.errors.token }}</p>
                            </div>

                            <button
                                type="submit"
                                :disabled="joinForm.processing || !joinForm.token.trim()"
                                class="w-full bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-bold py-3.5 px-6 rounded-xl transition duration-200 shadow-lg shadow-emerald-500/25 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
                            >
                                <span v-if="joinForm.processing">Memproses...</span>
                                <span v-else>🤝 Bergabung ke Keluarga!</span>
                            </button>
                        </form>
                    </div>

                    <!-- Divider -->
                    <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-800 text-center">
                        <p class="text-xs text-gray-400 dark:text-gray-500 font-medium">
                            Butuh bantuan? Silahkan hubungi admin utama keluarga Anda.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Features preview -->
            <div class="mt-6 grid grid-cols-3 gap-3">
                <div class="bg-white/60 dark:bg-zinc-900/40 backdrop-blur-sm rounded-xl p-3 text-center border border-gray-200/30 dark:border-gray-800/30">
                    <div class="text-2xl mb-1">💰</div>
                    <div class="text-[10px] text-gray-600 dark:text-gray-400 font-bold">Catat Finansial</div>
                </div>
                <div class="bg-white/60 dark:bg-zinc-900/40 backdrop-blur-sm rounded-xl p-3 text-center border border-gray-200/30 dark:border-gray-800/30">
                    <div class="text-2xl mb-1">📊</div>
                    <div class="text-[10px] text-gray-600 dark:text-gray-400 font-bold">Laporan Visual</div>
                </div>
                <div class="bg-white/60 dark:bg-zinc-900/40 backdrop-blur-sm rounded-xl p-3 text-center border border-gray-200/30 dark:border-gray-800/30">
                    <div class="text-2xl mb-1">🤝</div>
                    <div class="text-[10px] text-gray-600 dark:text-gray-400 font-bold">Multi Anggota</div>
                </div>
            </div>
        </div>
    </div>
</template>
