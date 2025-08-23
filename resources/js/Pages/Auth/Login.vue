<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

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
        <Head title="Sign In - IReserve" />

        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-2">Welcome Back</h2>
            <p class="text-gray-600">Sign in to your IReserve account</p>
        </div>

        <div v-if="status" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-sm font-medium text-green-600">{{ status }}</p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <InputLabel for="email" value="Email Address" class="text-gray-700 font-medium" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-2 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="Enter your email"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Password" class="text-gray-700 font-medium" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-2 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    placeholder="Enter your password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ml-2 text-sm text-gray-600">Remember me</span>
                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm text-indigo-600 hover:text-indigo-500 font-medium transition duration-200"
                >
                    Forgot password?
                </Link>
            </div>

            <PrimaryButton
                class="w-full justify-center py-3 px-4 text-base font-medium rounded-lg transition duration-200 transform hover:scale-[1.02]"
                :class="{ 'opacity-75 cursor-not-allowed': form.processing }"
                :disabled="form.processing"
            >
                <span v-if="form.processing" class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Signing in...
                </span>
                <span v-else>Sign In</span>
            </PrimaryButton>

            <div class="text-center">
                <p class="text-sm text-gray-600">
                    Don't have an account?
                    <Link
                        :href="route('register')"
                        class="font-medium text-indigo-600 hover:text-indigo-500 transition duration-200"
                    >
                        Create an account
                    </Link>
                </p>
            </div>
        </form>
    </GuestLayout>
</template>
