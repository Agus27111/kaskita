<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

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
    family: {
        id: number;
        name: string;
        avatar: string | null;
        invite_code: string;
    };
    members: Member[];
    invitations: Invitation[];
    isAdmin: boolean;
}>();

// --- Edit nama keluarga ---
const editingName = ref(false);
const nameForm = useForm({ name: props.family.name });

const saveName = () => {
    nameForm.put('/family', {
        onSuccess: () => {
            editingName.value = false;
        },
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
        onSuccess: () => {
            confirmRemove.value = null;
        },
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
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

const copied = ref(false);

const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text);
    copied.value = true;
    setTimeout(() => {
        copied.value = false;
    }, 2000);
};

const whatsappShareUrl = computed(() => {
    if (typeof window === 'undefined') {
        return '#';
    }

    const registerUrl = `${window.location.origin}/register`;
    const text =
        `Halo! Yuk bergabung ke grup keuangan keluarga kami "${props.family.name}" di KasKita. 😊\n\n` +
        `1. Daftar akun baru di sini:\n👉 ${registerUrl}\n\n` +
        `2. Lalu masukkan Kode Undangan berikut pada halaman Setup Keluarga:\n👉 ${props.family.invite_code}`;

    return `https://api.whatsapp.com/send?text=${encodeURIComponent(text)}`;
});
</script>

<template>
    <AppLayout>
        <Head title="Pengaturan Keluarga" />

        <div class="mx-auto max-w-4xl space-y-8 px-4 py-8">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Pengaturan Keluarga
                </h1>
                <p class="mt-1 text-gray-500 dark:text-gray-400">
                    Kelola anggota dan pengaturan keluarga Anda
                </p>
            </div>

            <!-- Family Name Card -->
            <div
                class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 text-2xl font-bold text-white shadow-lg shadow-emerald-500/20"
                        >
                            {{ family.name.charAt(0).toUpperCase() }}
                        </div>
                        <div v-if="!editingName">
                            <h2
                                class="text-xl font-bold text-gray-900 dark:text-white"
                            >
                                {{ family.name }}
                            </h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ members.length }} anggota
                            </p>
                        </div>
                        <form
                            v-else
                            @submit.prevent="saveName"
                            class="flex gap-2"
                        >
                            <input
                                v-model="nameForm.name"
                                type="text"
                                class="rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 text-gray-800 outline-none focus:ring-2 focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                required
                            />
                            <button
                                type="submit"
                                :disabled="nameForm.processing"
                                class="rounded-xl bg-emerald-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-600"
                            >
                                Simpan
                            </button>
                            <button
                                @click="
                                    editingName = false;
                                    nameForm.name = family.name;
                                "
                                type="button"
                                class="rounded-xl bg-gray-200 px-4 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300"
                            >
                                Batal
                            </button>
                        </form>
                    </div>
                    <button
                        v-if="isAdmin && !editingName"
                        @click="editingName = true"
                        class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-emerald-500 dark:hover:bg-gray-700"
                        title="Edit nama"
                    >
                        ✏️
                    </button>
                </div>
            </div>

            <!-- Members Card -->
            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800"
            >
                <div
                    class="flex items-center justify-between border-b border-gray-200 p-6 dark:border-gray-700"
                >
                    <h3
                        class="text-lg font-semibold text-gray-900 dark:text-white"
                    >
                        👥 Anggota Keluarga
                    </h3>
                    <button
                        v-if="isAdmin"
                        @click="showInviteModal = true"
                        class="flex items-center gap-2 rounded-xl bg-emerald-500 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-emerald-600"
                    >
                        ➕ Undang Anggota
                    </button>
                </div>

                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    <div
                        v-for="member in members"
                        :key="member.id"
                        class="dark:hover:bg-gray-750 flex items-center justify-between p-4 transition hover:bg-gray-50"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                v-if="member.avatar"
                                class="h-10 w-10 overflow-hidden rounded-full"
                            >
                                <img
                                    :src="member.avatar"
                                    :alt="member.name"
                                    class="h-full w-full object-cover"
                                />
                            </div>
                            <div
                                v-else
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 text-sm font-bold text-white"
                            >
                                {{ initials(member.name) }}
                            </div>
                            <div>
                                <div
                                    class="text-sm font-medium text-gray-900 dark:text-white"
                                >
                                    {{ member.name }}
                                </div>
                                <div
                                    class="text-xs text-gray-500 dark:text-gray-400"
                                >
                                    {{ member.email }}
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span
                                :class="[
                                    roleBadgeClass(member.role),
                                    'rounded-full px-2.5 py-1 text-xs font-medium',
                                ]"
                            >
                                {{ roleLabel(member.role) }}
                            </span>
                            <div v-if="isAdmin" class="flex items-center gap-1">
                                <button
                                    v-if="member.role === 'anggota'"
                                    @click="
                                        changeRole(member, 'admin_keluarga')
                                    "
                                    class="rounded px-2 py-1 text-xs text-amber-600 transition hover:bg-amber-50 hover:text-amber-700 dark:hover:bg-amber-900/20"
                                    title="Jadikan Admin"
                                >
                                    👑
                                </button>
                                <button
                                    v-if="member.role === 'admin_keluarga'"
                                    @click="changeRole(member, 'anggota')"
                                    class="rounded px-2 py-1 text-xs text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700"
                                    title="Jadikan Anggota"
                                >
                                    ↓
                                </button>
                                <button
                                    @click="confirmRemove = member"
                                    class="rounded px-2 py-1 text-xs text-red-500 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20"
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
            <div
                class="space-y-4 rounded-3xl bg-gradient-to-br from-emerald-500 to-teal-600 p-6 text-white shadow-xl shadow-emerald-500/20"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-black tracking-tight">
                            🔑 Kode Undangan & Berbagi Cepat
                        </h3>
                        <p class="mt-1 text-xs font-semibold text-emerald-100">
                            Bagikan kode ini via WhatsApp agar anggota keluarga
                            lainnya bisa bergabung seketika.
                        </p>
                    </div>
                    <div class="text-3xl">💬</div>
                </div>

                <div
                    class="flex flex-col items-stretch gap-3 pt-2 sm:flex-row sm:items-center"
                >
                    <div
                        class="flex flex-1 items-center justify-between overflow-hidden rounded-2xl border border-white/20 bg-white/15 px-4 py-3 backdrop-blur-md"
                    >
                        <span
                            class="mr-2 font-mono text-base font-black tracking-widest text-white select-all"
                        >
                            {{ family.invite_code }}
                        </span>
                        <button
                            @click="copyToClipboard(family.invite_code)"
                            class="shrink-0 cursor-pointer rounded-xl bg-white px-3 py-1.5 text-xs font-extrabold text-emerald-700 transition hover:bg-emerald-50"
                        >
                            {{ copied ? 'Tersalin! ✅' : 'Salin' }}
                        </button>
                    </div>

                    <a
                        :href="whatsappShareUrl"
                        target="_blank"
                        class="flex shrink-0 cursor-pointer items-center justify-center gap-2 rounded-2xl bg-white px-5 py-3 text-xs font-extrabold text-emerald-700 shadow-lg transition hover:bg-emerald-50"
                    >
                        <span>Bagikan ke WA 🚀</span>
                    </a>
                </div>
            </div>

            <!-- Pending Invitations -->
            <div
                v-if="invitations.length > 0"
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800"
            >
                <div class="border-b border-gray-200 p-6 dark:border-gray-700">
                    <h3
                        class="text-lg font-semibold text-gray-900 dark:text-white"
                    >
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
                            <div
                                class="text-sm font-medium text-gray-900 dark:text-white"
                            >
                                {{ inv.email }}
                            </div>
                            <div
                                class="text-xs text-gray-500 dark:text-gray-400"
                            >
                                Role: {{ roleLabel(inv.role) }} · Kedaluwarsa
                                {{
                                    new Date(inv.expires_at).toLocaleDateString(
                                        'id-ID',
                                    )
                                }}
                            </div>
                        </div>
                        <button
                            v-if="isAdmin"
                            @click="cancelInvitation(inv)"
                            class="rounded-lg px-3 py-1.5 text-xs text-red-500 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20"
                        >
                            Batalkan
                        </button>
                    </div>
                </div>
            </div>

            <!-- Danger Zone -->
            <div
                class="rounded-2xl border border-red-200 bg-red-50 p-6 dark:border-red-800/50 dark:bg-red-950/30"
            >
                <h3
                    class="mb-2 text-lg font-semibold text-red-700 dark:text-red-400"
                >
                    ⚠️ Zona Berbahaya
                </h3>
                <p class="mb-4 text-sm text-red-600 dark:text-red-400/80">
                    Setelah Anda meninggalkan keluarga, data transaksi Anda
                    tetap tersimpan di keluarga tersebut.
                </p>
                <button
                    @click="confirmLeave = true"
                    class="rounded-xl bg-red-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-600"
                >
                    Tinggalkan Keluarga
                </button>
            </div>
        </div>

        <!-- Invite Modal -->
        <Teleport to="body">
            <div
                v-if="showInviteModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                @click.self="showInviteModal = false"
            >
                <div
                    class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800"
                >
                    <h3
                        class="mb-4 text-lg font-bold text-gray-900 dark:text-white"
                    >
                        ➕ Undang Anggota Keluarga
                    </h3>
                    <form @submit.prevent="sendInvite" class="space-y-4">
                        <div>
                            <label
                                class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >Email</label
                            >
                            <input
                                v-model="inviteForm.email"
                                type="email"
                                placeholder="nama@email.com"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-800 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-700/50 dark:text-gray-200"
                                required
                            />
                            <p
                                v-if="inviteForm.errors.email"
                                class="mt-1 text-sm text-red-500"
                            >
                                {{ inviteForm.errors.email }}
                            </p>
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >Role</label
                            >
                            <select
                                v-model="inviteForm.role"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-800 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-700/50 dark:text-gray-200"
                            >
                                <option value="anggota">
                                    Anggota — bisa catat transaksi & lihat
                                    laporan
                                </option>
                                <option value="admin_keluarga">
                                    Admin — semua fitur + kelola anggota
                                </option>
                            </select>
                        </div>
                        <div class="flex gap-3 pt-2">
                            <button
                                type="submit"
                                :disabled="inviteForm.processing"
                                class="flex-1 rounded-xl bg-emerald-500 py-3 font-bold text-white transition hover:bg-emerald-600"
                            >
                                {{
                                    inviteForm.processing
                                        ? 'Mengirim...'
                                        : 'Kirim Undangan'
                                }}
                            </button>
                            <button
                                @click="showInviteModal = false"
                                type="button"
                                class="rounded-xl bg-gray-200 px-5 py-3 font-medium text-gray-600 transition hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300"
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
            <div
                v-if="confirmRemove"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                @click.self="confirmRemove = null"
            >
                <div
                    class="w-full max-w-sm rounded-2xl bg-white p-6 text-center shadow-2xl dark:bg-gray-800"
                >
                    <div class="mb-3 text-4xl">😢</div>
                    <h3
                        class="mb-2 text-lg font-bold text-gray-900 dark:text-white"
                    >
                        Keluarkan {{ confirmRemove.name }}?
                    </h3>
                    <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">
                        {{ confirmRemove.name }} tidak akan bisa mengakses data
                        keluarga ini lagi.
                    </p>
                    <div class="flex gap-3">
                        <button
                            @click="removeMember(confirmRemove!)"
                            class="flex-1 rounded-xl bg-red-500 py-2.5 font-bold text-white transition hover:bg-red-600"
                        >
                            Ya, Keluarkan
                        </button>
                        <button
                            @click="confirmRemove = null"
                            class="flex-1 rounded-xl bg-gray-200 font-medium text-gray-600 transition hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300"
                        >
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Confirm Leave Modal -->
        <Teleport to="body">
            <div
                v-if="confirmLeave"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                @click.self="confirmLeave = false"
            >
                <div
                    class="w-full max-w-sm rounded-2xl bg-white p-6 text-center shadow-2xl dark:bg-gray-800"
                >
                    <div class="mb-3 text-4xl">🚪</div>
                    <h3
                        class="mb-2 text-lg font-bold text-gray-900 dark:text-white"
                    >
                        Tinggalkan Keluarga?
                    </h3>
                    <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">
                        Data transaksi Anda tetap tersimpan, tapi Anda tidak
                        bisa mengaksesnya lagi.
                    </p>
                    <div class="flex gap-3">
                        <button
                            @click="leaveFamily"
                            class="flex-1 rounded-xl bg-red-500 py-2.5 font-bold text-white transition hover:bg-red-600"
                        >
                            Ya, Tinggalkan
                        </button>
                        <button
                            @click="confirmLeave = false"
                            class="flex-1 rounded-xl bg-gray-200 font-medium text-gray-600 transition hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300"
                        >
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
