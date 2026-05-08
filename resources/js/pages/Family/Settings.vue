<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, computed } from 'vue';

interface Member {
    id: number;
    name: string;
    email: string;
    avatar: string | null;
    role: string;
    created_at: string;
}

interface Invitation {
    id: number;
    email: string;
    token: string;
    role: string;
    expires_at: string;
    created_at: string;
}

const props = defineProps<{
    family: { id: number; name: string; avatar: string | null; invite_code: string };
    members: Member[];
    invitations: Invitation[];
    isAdmin: boolean;
}>();

// --- Edit nama keluarga ---
const editingName = ref(false);
const nameForm = useForm({ name: props.family.name });

const saveName = () => {
    nameForm.put('/family', {
        onSuccess: () => { editingName.value = false; },
    });
};

// --- Kirim undangan ---
const showInviteModal = ref(false);
const inviteForm = useForm({
    email: '',
    role: 'anggota',
});

const sendInvite = () => {
    inviteForm.post('/family/invite', {
        onSuccess: () => {
            inviteForm.reset();
            showInviteModal.value = false;
        },
    });
};

// --- Remove member ---
const confirmRemove = ref<Member | null>(null);

const removeMember = (member: Member) => {
    router.delete(`/family/member/${member.id}`, {
        onSuccess: () => { confirmRemove.value = null; },
    });
};

// --- Change role ---
const changeRole = (member: Member, newRole: string) => {
    router.put(`/family/member/${member.id}/role`, { role: newRole });
};

// --- Cancel invitation ---
const cancelInvitation = (invitation: Invitation) => {
    router.delete(`/family/invitation/${invitation.id}`);
};

// --- Leave family ---
const confirmLeave = ref(false);

const leaveFamily = () => {
    router.post('/family/leave');
};

// --- Helpers ---
const roleLabel = (role: string) => {
    return role === 'admin_keluarga' ? 'Admin' : 'Anggota';
};

const roleBadgeClass = (role: string) => {
    return role === 'admin_keluarga'
        ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'
        : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400';
};

const initials = (name: string) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const copied = ref(false);

const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text);
    copied.value = true;
    setTimeout(() => { copied.value = false; }, 2000);
};

const whatsappShareUrl = computed(() => {
    if (typeof window === 'undefined') return '#';
    const registerUrl = `${window.location.origin}/register`;
    const text = `Halo! Yuk bergabung ke grup keuangan keluarga kami "${props.family.name}" di KasKita. 😊\n\n` +
                 `1. Daftar akun baru di sini:\n👉 ${registerUrl}\n\n` +
                 `2. Lalu masukkan Kode Undangan berikut pada halaman Setup Keluarga:\n👉 ${props.family.invite_code}`;
    return `https://api.whatsapp.com/send?text=${encodeURIComponent(text)}`;
});

const inviteLink = computed(() => {
    if (typeof window === 'undefined') return null;
    
    return `${window.location.origin}/family/invitation/${props.family.invite_code}`;
});
</script>

<template>
    <AppLayout>
        <Head title="Pengaturan Keluarga" />

        <div class="max-w-4xl mx-auto px-4 py-8 space-y-8">
        <!-- Header -->
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                Pengaturan Keluarga
            </h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">
                Kelola anggota dan pengaturan keluarga Anda
            </p>
        </div>

        <!-- Family Name Card -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-gradient-to-br from-emerald-400 to-teal-500 rounded-2xl flex items-center justify-center text-white text-2xl font-bold shadow-lg shadow-emerald-500/20">
                        {{ family.name.charAt(0).toUpperCase() }}
                    </div>
                    <div v-if="!editingName">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                            {{ family.name }}
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ members.length }} anggota
                        </p>
                    </div>
                    <form v-else @submit.prevent="saveName" class="flex gap-2">
                        <input
                            v-model="nameForm.name"
                            type="text"
                            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-emerald-500 outline-none"
                            required
                        />
                        <button type="submit" :disabled="nameForm.processing" class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-sm font-medium transition">
                            Simpan
                        </button>
                        <button @click="editingName = false; nameForm.name = family.name" type="button" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl text-sm font-medium transition hover:bg-gray-300">
                            Batal
                        </button>
                    </form>
                </div>
                <button
                    v-if="isAdmin && !editingName"
                    @click="editingName = true"
                    class="text-gray-400 hover:text-emerald-500 transition p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                    title="Edit nama"
                >
                    ✏️
                </button>
            </div>
        </div>

        <!-- Members Card -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    👥 Anggota Keluarga
                </h3>
                <button
                    v-if="isAdmin"
                    @click="showInviteModal = true"
                    class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-sm font-medium transition flex items-center gap-2 shadow-sm"
                >
                    ➕ Undang Anggota
                </button>
            </div>

            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                <div
                    v-for="member in members"
                    :key="member.id"
                    class="flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-750 transition"
                >
                    <div class="flex items-center gap-3">
                        <div v-if="member.avatar" class="w-10 h-10 rounded-full overflow-hidden">
                            <img :src="member.avatar" :alt="member.name" class="w-full h-full object-cover" />
                        </div>
                        <div v-else class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center text-white text-sm font-bold">
                            {{ initials(member.name) }}
                        </div>
                        <div>
                            <div class="font-medium text-gray-900 dark:text-white text-sm">
                                {{ member.name }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ member.email }}
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span :class="[roleBadgeClass(member.role), 'text-xs font-medium px-2.5 py-1 rounded-full']">
                            {{ roleLabel(member.role) }}
                        </span>
                        <div v-if="isAdmin" class="flex items-center gap-1">
                            <button
                                v-if="member.role === 'anggota'"
                                @click="changeRole(member, 'admin_keluarga')"
                                class="text-xs text-amber-600 hover:text-amber-700 hover:bg-amber-50 dark:hover:bg-amber-900/20 px-2 py-1 rounded transition"
                                title="Jadikan Admin"
                            >
                                👑
                            </button>
                            <button
                                v-if="member.role === 'admin_keluarga'"
                                @click="changeRole(member, 'anggota')"
                                class="text-xs text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 px-2 py-1 rounded transition"
                                title="Jadikan Anggota"
                            >
                                ↓
                            </button>
                            <button
                                @click="confirmRemove = member"
                                class="text-xs text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 px-2 py-1 rounded transition"
                                title="Keluarkan"
                            >
                                ✕
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 🔑 Kode Undangan & Berbagi WhatsApp -->
        <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-3xl p-6 text-white shadow-xl shadow-emerald-500/20 space-y-4">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-lg font-black tracking-tight">🔑 Kode Undangan & Berbagi Cepat</h3>
                    <p class="text-emerald-100 text-xs font-semibold mt-1">Bagikan kode ini via WhatsApp agar anggota keluarga lainnya bisa bergabung seketika.</p>
                </div>
                <div class="text-3xl">💬</div>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-2">
                <div class="flex-1 bg-white/15 backdrop-blur-md rounded-2xl px-4 py-3 flex items-center justify-between border border-white/20 overflow-hidden">
                    <span class="font-mono text-base font-black tracking-widest select-all mr-2 text-white">
                        {{ family.invite_code }}
                    </span>
                    <button 
                        @click="copyToClipboard(family.invite_code)"
                        class="text-xs bg-white text-emerald-700 font-extrabold px-3 py-1.5 rounded-xl hover:bg-emerald-50 transition shrink-0 cursor-pointer"
                    >
                        {{ copied ? 'Tersalin! ✅' : 'Salin' }}
                    </button>
                </div>

                <a 
                    :href="whatsappShareUrl"
                    target="_blank"
                    class="bg-white hover:bg-emerald-50 text-emerald-700 font-extrabold px-5 py-3 rounded-2xl flex items-center justify-center gap-2 text-xs transition shadow-lg shrink-0 cursor-pointer"
                >
                    <span>Bagikan ke WA 🚀</span>
                </a>
            </div>
        </div>

        <!-- Pending Invitations -->
        <div v-if="invitations.length > 0" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    📨 Undangan Menunggu
                </h3>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                <div
                    v-for="inv in invitations"
                    :key="inv.id"
                    class="flex items-center justify-between p-4"
                >
                    <div>
                        <div class="font-medium text-gray-900 dark:text-white text-sm">
                            {{ inv.email }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            Role: {{ roleLabel(inv.role) }} · Kedaluwarsa {{ new Date(inv.expires_at).toLocaleDateString('id-ID') }}
                        </div>
                    </div>
                    <button
                        v-if="isAdmin"
                        @click="cancelInvitation(inv)"
                        class="text-xs text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 px-3 py-1.5 rounded-lg transition"
                    >
                        Batalkan
                    </button>
                </div>
            </div>
        </div>

        <!-- Danger Zone -->
        <div class="bg-red-50 dark:bg-red-950/30 rounded-2xl border border-red-200 dark:border-red-800/50 p-6">
            <h3 class="text-lg font-semibold text-red-700 dark:text-red-400 mb-2">
                ⚠️ Zona Berbahaya
            </h3>
            <p class="text-sm text-red-600 dark:text-red-400/80 mb-4">
                Setelah Anda meninggalkan keluarga, data transaksi Anda tetap tersimpan di keluarga tersebut.
            </p>
            <button
                @click="confirmLeave = true"
                class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl text-sm font-medium transition"
            >
                Tinggalkan Keluarga
            </button>
        </div>
    </div>

    <!-- Invite Modal -->
    <Teleport to="body">
        <div v-if="showInviteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="showInviteModal = false">
            <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full p-6 shadow-2xl">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
                    ➕ Undang Anggota Keluarga
                </h3>
                <form @submit.prevent="sendInvite" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                        <input
                            v-model="inviteForm.email"
                            type="email"
                            placeholder="nama@email.com"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-gray-800 dark:text-gray-200"
                            required
                        />
                        <p v-if="inviteForm.errors.email" class="text-red-500 text-sm mt-1">{{ inviteForm.errors.email }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
                        <select
                            v-model="inviteForm.role"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none text-gray-800 dark:text-gray-200"
                        >
                            <option value="anggota">Anggota — bisa catat transaksi & lihat laporan</option>
                            <option value="admin_keluarga">Admin — semua fitur + kelola anggota</option>
                        </select>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button
                            type="submit"
                            :disabled="inviteForm.processing"
                            class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-3 rounded-xl transition"
                        >
                            {{ inviteForm.processing ? 'Mengirim...' : 'Kirim Undangan' }}
                        </button>
                        <button
                            @click="showInviteModal = false"
                            type="button"
                            class="px-5 py-3 bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-medium transition hover:bg-gray-300"
                        >
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>

    <!-- Confirm Remove Modal -->
    <Teleport to="body">
        <div v-if="confirmRemove" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="confirmRemove = null">
            <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-sm w-full p-6 shadow-2xl text-center">
                <div class="text-4xl mb-3">😢</div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">
                    Keluarkan {{ confirmRemove.name }}?
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                    {{ confirmRemove.name }} tidak akan bisa mengakses data keluarga ini lagi.
                </p>
                <div class="flex gap-3">
                    <button
                        @click="removeMember(confirmRemove!)"
                        class="flex-1 bg-red-500 hover:bg-red-600 text-white font-bold py-2.5 rounded-xl transition"
                    >
                        Ya, Keluarkan
                    </button>
                    <button
                        @click="confirmRemove = null"
                        class="flex-1 bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-medium transition hover:bg-gray-300"
                    >
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </Teleport>

    <!-- Confirm Leave Modal -->
    <Teleport to="body">
        <div v-if="confirmLeave" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="confirmLeave = false">
            <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-sm w-full p-6 shadow-2xl text-center">
                <div class="text-4xl mb-3">🚪</div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">
                    Tinggalkan Keluarga?
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                    Data transaksi Anda tetap tersimpan, tapi Anda tidak bisa mengaksesnya lagi.
                </p>
                <div class="flex gap-3">
                    <button
                        @click="leaveFamily"
                        class="flex-1 bg-red-500 hover:bg-red-600 text-white font-bold py-2.5 rounded-xl transition"
                    >
                        Ya, Tinggalkan
                    </button>
                    <button
                        @click="confirmLeave = false"
                        class="flex-1 bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl font-medium transition hover:bg-gray-300"
                    >
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
    </AppLayout>
</template>
