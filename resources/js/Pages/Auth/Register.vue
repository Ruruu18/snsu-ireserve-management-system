<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Create Account - IReserve" />

        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-2">Create Your Account</h2>
            <p class="text-gray-600">Join IReserve and start managing your reservations</p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <InputLabel for="name" value="Full Name" class="text-gray-700 font-medium" />
                <TextInput
                    id="name"
                    type="text"
                    class="mt-2 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Enter your full name"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Email Address" class="text-gray-700 font-medium" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-2 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200"
                    v-model="form.email"
                    required
                    autocomplete="username"
                    placeholder="Enter your email address"
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
                    autocomplete="new-password"
                    placeholder="Create a strong password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div>
                <InputLabel for="password_confirmation" value="Confirm Password" class="text-gray-700 font-medium" />
                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-2 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="Confirm your password"
                />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-xs text-gray-600">
                    By creating an account, you agree to our Terms of Service and Privacy Policy.
                </p>
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
                    Creating account...
                </span>
                <span v-else>Create Account</span>
            </PrimaryButton>

            <div class="text-center">
                <p class="text-sm text-gray-600">
                    Already have an account?
                    <Link
                        :href="route('login')"
                        class="font-medium text-indigo-600 hover:text-indigo-500 transition duration-200"
                    >
                        Sign in here
                    </Link>
                </p>
            </div>
        </form>
    </GuestLayout>
</template>
