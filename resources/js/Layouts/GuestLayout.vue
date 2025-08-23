<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-cyan-50">
        <div class="flex min-h-screen flex-col items-center justify-center px-6 py-12">
            <!-- Logo Section -->
            <div class="mb-8">
                <Link href="/" class="flex items-center space-x-3">
                    <ApplicationLogo class="h-12 w-12 fill-current text-indigo-600" />
                    <div class="text-2xl font-bold text-gray-900">IReserve</div>
                </Link>
            </div>

            <!-- Main Content Card -->
            <div class="w-full max-w-md">
                <div class="bg-white/80 backdrop-blur-sm border border-white/20 shadow-xl rounded-2xl p-8">
                    <slot />
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center">
                <p class="text-sm text-gray-600">
                    © 2024 IReserve. All rights reserved.
                </p>
            </div>
        </div>

        <!-- Decorative Elements -->
        <div class="fixed inset-0 -z-10 overflow-hidden">
            <div class="absolute -top-40 -right-32 h-80 w-80 rounded-full bg-gradient-to-br from-indigo-400 to-cyan-400 opacity-20 blur-3xl"></div>
            <div class="absolute -bottom-40 -left-32 h-80 w-80 rounded-full bg-gradient-to-br from-purple-400 to-pink-400 opacity-20 blur-3xl"></div>
        </div>
    </div>
</template>
