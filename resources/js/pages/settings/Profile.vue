<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';

type Props = {
    mustVerifyEmail: boolean;
    status?: string;
};

defineProps<Props>();

const page = usePage();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Profile settings', href: edit() }]">
        <SettingsLayout>
            <Head title="Profile settings" />

            <h1 class="sr-only">Profile settings</h1>

            <div class="flex flex-col space-y-6">
                <Heading
                    variant="small"
                    title="Profile information"
                    description="Update your name and email address"
                />

                <Form
                    v-bind="ProfileController.update.form()"
                    class="space-y-6"
                    v-slot="{ errors, processing }"
                >
                    <div class="grid gap-2">
                        <Label
                            for="name"
                            class="text-xs font-bold text-gray-500"
                            >NAMA LENGKAP</Label
                        >
                        <Input
                            id="name"
                            class="mt-1 block w-full rounded-xl border border-gray-200 bg-gray-50/50 dark:border-zinc-700 dark:bg-zinc-800/50"
                            name="name"
                            :default-value="user.name"
                            required
                            autocomplete="name"
                            placeholder="Full name"
                        />
                        <InputError class="mt-2" :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label
                            for="email"
                            class="text-xs font-bold text-gray-500"
                            >EMAIL ADDRESS</Label
                        >
                        <Input
                            id="email"
                            type="email"
                            class="mt-1 block w-full rounded-xl border border-gray-200 bg-gray-50/50 dark:border-zinc-700 dark:bg-zinc-800/50"
                            name="email"
                            :default-value="user.email"
                            required
                            autocomplete="username"
                            placeholder="Email address"
                        />
                        <InputError class="mt-2" :message="errors.email" />
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="-mt-4 text-sm text-muted-foreground">
                            Your email address is unverified.
                            <Link
                                :href="send()"
                                as="button"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                            >
                                Click here to resend the verification email.
                            </Link>
                        </p>

                        <div
                            v-if="status === 'verification-link-sent'"
                            class="mt-2 text-sm font-medium text-green-600"
                        >
                            A new verification link has been sent to your email
                            address.
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            :disabled="processing"
                            data-test="update-profile-button"
                            class="cursor-pointer rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 px-6 py-2.5 font-bold text-white shadow-md shadow-emerald-500/10 transition hover:from-emerald-600 hover:to-teal-600"
                            >Save</Button
                        >
                    </div>
                </Form>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>
