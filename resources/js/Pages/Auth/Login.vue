<script setup>
import Checkbox from 'primevue/checkbox';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Button from "@/Components/Button.vue"

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <form
            @submit.prevent="submit"
            class="space-y-4 w-full"
        >
            <div class="flex flex-col gap-1">
                <InputLabel for="email" value="Email" />

                <InputText
                    id="email"
                    type="email"
                    class="block w-full"
                    v-model="form.email"
                    autofocus
                    :invalid="!!form.errors.email"
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="flex flex-col gap-1">
                <InputLabel for="password" value="Password" />

                <Password
                    input-id="password"
                    v-model="form.password"
                    toggleMask
                    :inputStyle="{'width': '100%'}"
                    :style="{'width': '100%'}"
                    :invalid="!!form.errors.password"
                    :feedback="false"
                    autocomplete="current-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 flex justify-between">
                <label class="flex items-center">
                    <Checkbox
                        name="remember"
                        v-model="form.remember"
                        :binary="true"
                    />
                    <span class="ms-2 text-xs text-gray-600">Remember me</span>
                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="underline text-xs text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Forgot your password?
                </Link>
            </div>

            <Button
                type="submit"
                variant="primary-flat"
                :disabled="form.processing"
                class="w-full"
            >
                Log In
            </Button>
        </form>
    </GuestLayout>
</template>
