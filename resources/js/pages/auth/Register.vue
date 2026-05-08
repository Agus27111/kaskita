<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/register';
import { UserPlus } from 'lucide-vue-next';
import AuthLayout from '@/layouts/AuthLayout.vue';
</script>

<template>
    <AuthLayout title="Buat Akun Baru" description="Isi data diri Anda untuk mulai mengelola keuangan keluarga">
        <Head title="Daftar" />

        <Form
            :action="store().url"
            :method="store().method"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-5"
        >
            <div class="grid gap-4">
                <div class="grid gap-2">
                    <Label for="name" class="text-xs font-bold text-gray-500">NAMA LENGKAP</Label>
                    <Input
                        id="name"
                        type="text"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="name"
                        name="name"
                        placeholder="Masukkan nama lengkap"
                        class="register-input"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email" class="text-xs font-bold text-gray-500">EMAIL</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        :tabindex="2"
                        autocomplete="email"
                        name="email"
                        placeholder="nama@email.com"
                        class="register-input"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password" class="text-xs font-bold text-gray-500">PASSWORD</Label>
                    <PasswordInput
                        id="password"
                        required
                        :tabindex="3"
                        autocomplete="new-password"
                        name="password"
                        placeholder="Minimal 8 karakter"
                        class="register-input"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation" class="text-xs font-bold text-gray-500">KONFIRMASI PASSWORD</Label>
                    <PasswordInput
                        id="password_confirmation"
                        required
                        :tabindex="4"
                        autocomplete="new-password"
                        name="password_confirmation"
                        placeholder="Ulangi password"
                        class="register-input"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <Button
                    type="submit"
                    class="register-btn mt-2"
                    tabindex="5"
                    :disabled="processing"
                    data-test="register-user-button"
                >
                    <Spinner v-if="processing" />
                    <UserPlus v-else class="mr-2 h-4 w-4" />
                    Buat Akun
                </Button>
            </div>

            <div class="text-center text-xs text-gray-400 dark:text-gray-500 font-bold">
                Sudah punya akun?
                <TextLink
                    :href="login()"
                    class="font-extrabold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 ml-1"
                    :tabindex="6"
                >
                    Masuk di sini
                </TextLink>
            </div>
        </Form>
    </AuthLayout>
</template>

<style scoped>
.register-input {
    height: 48px;
    border-radius: 12px;
    font-size: 14px;
    background-color: rgb(249, 250, 251);
    border-color: rgb(229, 231, 235);
    transition: all 0.2s ease;
}

.dark .register-input {
    background-color: rgba(63, 63, 70, 0.2);
    border-color: rgb(63, 63, 70);
}

.register-input:focus {
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.register-btn {
    height: 48px;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 700;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.2);
    color: white;
    transition: all 0.3s ease;
}

.register-btn:hover:not(:disabled) {
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.3);
    transform: translateY(-1px);
}
</style>
