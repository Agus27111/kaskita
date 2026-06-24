<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { LogIn } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();
</script>

<template>
    <AuthLayout
        title="Selamat Datang Kembali"
        description="Masuk ke akun KasKita Anda"
    >
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
                    <Label for="email" class="text-xs font-bold text-gray-500"
                        >EMAIL</Label
                    >
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
                        <Label
                            for="password"
                            class="text-xs font-bold text-gray-500"
                            >PASSWORD</Label
                        >
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
                    <Label
                        for="remember"
                        class="cursor-pointer text-xs font-bold text-gray-500 select-none dark:text-gray-400"
                    >
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

                <div class="relative my-2">
                    <div class="absolute inset-0 flex items-center">
                        <span class="w-full border-t border-gray-200 dark:border-zinc-800" />
                    </div>
                    <div class="relative flex justify-center text-xs uppercase">
                        <span class="bg-white px-2 font-bold text-gray-500 dark:bg-zinc-900 dark:text-gray-400">
                            Atau
                        </span>
                    </div>
                </div>

                <a
                    href="/auth/google"
                    class="google-btn"
                    tabindex="5"
                >
                    <svg class="mr-2 h-5 w-5" viewBox="0 0 24 24">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4" />
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853" />
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05" />
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335" />
                    </svg>
                    Masuk dengan Google
                </a>
            </div>

            <div
                class="text-center text-xs font-bold text-gray-400 dark:text-gray-500"
                v-if="canRegister"
            >
                Belum punya akun?
                <TextLink
                    :href="register()"
                    :tabindex="6"
                    class="ml-1 font-extrabold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400"
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

.google-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 48px;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 700;
    background-color: white;
    border: 1px solid rgb(229, 231, 235);
    color: rgb(55, 65, 81);
    transition: all 0.3s ease;
}

.dark .google-btn {
    background-color: rgba(63, 63, 70, 0.2);
    border-color: rgb(63, 63, 70);
    color: rgb(228, 228, 231);
}

.google-btn:hover {
    background-color: rgb(249, 250, 251);
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
}

.dark .google-btn:hover {
    background-color: rgba(63, 63, 70, 0.4);
}
</style>
