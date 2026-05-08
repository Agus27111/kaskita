<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { LogIn } from 'lucide-vue-next';
import AuthLayout from '@/layouts/AuthLayout.vue';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();
</script>

<template>
    <AuthLayout title="Selamat Datang Kembali" description="Masuk ke akun KasKita Anda">
        <Head title="Masuk" />

        <div
            v-if="status"
            class="mb-4 rounded-xl bg-emerald-50 p-3 text-center text-sm font-medium text-emerald-600 dark:bg-emerald-950/30 dark:text-emerald-400"
        >
            {{ status }}
        </div>

        <Form
            :action="store().url"
            :method="store().method"
            :reset-on-success="['password']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-5"
        >
            <div class="grid gap-5">
                <div class="grid gap-2">
                    <Label for="email" class="text-xs font-bold text-gray-500">EMAIL</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        placeholder="nama@email.com"
                        class="login-input"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password" class="text-xs font-bold text-gray-500">PASSWORD</Label>
                        <TextLink
                            v-if="canResetPassword"
                            :href="request()"
                            class="text-xs font-bold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400"
                            :tabindex="5"
                        >
                            Lupa password?
                        </TextLink>
                    </div>
                    <PasswordInput
                        id="password"
                        name="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        placeholder="Masukkan password"
                        class="login-input"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="flex items-center gap-3">
                    <Checkbox id="remember" name="remember" :tabindex="3" />
                    <Label for="remember" class="text-xs text-gray-500 dark:text-gray-400 font-bold cursor-pointer select-none">
                        Ingat saya
                    </Label>
                </div>

                <Button
                    type="submit"
                    class="login-btn mt-2"
                    :tabindex="4"
                    :disabled="processing"
                    data-test="login-button"
                >
                    <Spinner v-if="processing" />
                    <LogIn v-else class="mr-2 h-4 w-4" />
                    Masuk
                </Button>
            </div>

            <div
                class="text-center text-xs text-gray-400 dark:text-gray-500 font-bold"
                v-if="canRegister"
            >
                Belum punya akun?
                <TextLink
                    :href="register()"
                    :tabindex="5"
                    class="font-extrabold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 ml-1"
                >
                    Daftar sekarang
                </TextLink>
            </div>
        </Form>
    </AuthLayout>
</template>

<style scoped>
.login-input {
    height: 48px;
    border-radius: 12px;
    font-size: 14px;
    background-color: rgb(249, 250, 251);
    border-color: rgb(229, 231, 235);
    transition: all 0.2s ease;
}

.dark .login-input {
    background-color: rgba(63, 63, 70, 0.2);
    border-color: rgb(63, 63, 70);
}

.login-input:focus {
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.login-btn {
    height: 48px;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 700;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.2);
    color: white;
    transition: all 0.3s ease;
}

.login-btn:hover:not(:disabled) {
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.3);
    transform: translateY(-1px);
}
</style>
