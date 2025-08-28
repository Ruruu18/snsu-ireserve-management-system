<script setup>
import InputError from '@/Components/InputError.vue';
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
    <Head title="Register" />

    <!-- Full Screen Background with Backdrop -->
    <div class="min-h-screen relative flex items-center justify-center"
         style="background-image: url('/images/Backdrop.png'); background-size: cover; background-position: center; background-repeat: no-repeat;">

        <!-- Overlay for better contrast -->
        <div class="absolute inset-0 bg-black bg-opacity-20"></div>

        <!-- Main Content Container -->
        <div class="relative z-10 w-full max-w-md mx-4">
            <!-- Logo Section - Above white container -->
            <div class="text-center -mb-20">
                <img src="/images/LT.png" alt="SNSU Logo" class="mx-auto h-96 w-auto">
            </div>

            <!-- Registration Form Card -->
            <div class="bg-white rounded-3xl shadow-2xl p-8 border border-gray-100">

                 <!-- Registration Form -->
                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Name Field -->
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <TextInput
                            id="name"
                            type="text"
                            class="block w-full pl-12 pr-4 py-4 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition duration-200 text-sm bg-gray-50 focus:bg-white"
                            v-model="form.name"
                            required
                            autofocus
                            autocomplete="name"
                            placeholder="Name"
                        />
                        <InputError class="mt-1 text-xs" :message="form.errors.name" />
                    </div>

                    <!-- Email Field -->
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                            </svg>
                        </div>
                        <TextInput
                            id="email"
                            type="email"
                            class="block w-full pl-12 pr-4 py-4 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition duration-200 text-sm bg-gray-50 focus:bg-white"
                            v-model="form.email"
                            required
                            autocomplete="username"
                            placeholder="Email"
                        />
                        <InputError class="mt-1 text-xs" :message="form.errors.email" />
                    </div>

                    <!-- Password Field -->
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <TextInput
                            id="password"
                            type="password"
                            class="block w-full pl-12 pr-4 py-4 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition duration-200 text-sm bg-gray-50 focus:bg-white"
                            v-model="form.password"
                            required
                            autocomplete="new-password"
                            placeholder="Password"
                        />
                        <InputError class="mt-1 text-xs" :message="form.errors.password" />
                    </div>

                    <!-- Confirm Password Field -->
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <TextInput
                            id="password_confirmation"
                            type="password"
                            class="block w-full pl-12 pr-4 py-4 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition duration-200 text-sm bg-gray-50 focus:bg-white"
                            v-model="form.password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="Confirm Password"
                        />
                        <InputError class="mt-1 text-xs" :message="form.errors.password_confirmation" />
                    </div>

                    <!-- Register Button -->
                    <button
                        type="submit"
                        class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-4 px-4 rounded-2xl transition duration-200 transform hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 shadow-lg hover:shadow-xl mt-6"
                        :class="{ 'opacity-75 cursor-not-allowed': form.processing }"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing" class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Registering...
                        </span>
                        <span v-else>Register</span>
                    </button>

                    <!-- Login Link -->
                    <div class="text-center pt-4">
                        <Link
                            :href="route('login')"
                            class="text-sm text-gray-600 hover:text-green-600 transition duration-200"
                        >
                            Already registered?
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
