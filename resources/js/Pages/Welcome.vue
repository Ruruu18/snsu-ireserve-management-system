<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Head title="Welcome to SNSU iReserve">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    </Head>

    <div class="min-h-screen relative overflow-hidden">
        <!-- Background Image -->
        <div
            class="absolute inset-0 bg-cover bg-center bg-no-repeat"
            style="background-image: url('/images/Backdrop.png')"
        ></div>

        <!-- Background Overlay for better readability -->
        <div class="absolute inset-0 bg-gradient-to-br from-green-50/40 via-green-100/30 to-green-200/40"></div>

        <!-- Decorative Plus Signs -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <!-- Top Left Plus -->
            <div class="absolute top-16 left-12 text-green-600/30 text-4xl font-light transform rotate-45">+</div>
            <div class="absolute top-32 left-32 text-green-700/20 text-2xl font-light transform rotate-45">+</div>

            <!-- Top Right Plus -->
            <div class="absolute top-20 right-16 text-green-600/40 text-5xl font-light transform rotate-45">+</div>
            <div class="absolute top-48 right-32 text-green-500/25 text-3xl font-light transform rotate-45">+</div>

            <!-- Bottom Left Plus -->
            <div class="absolute bottom-24 left-16 text-green-700/30 text-4xl font-light transform rotate-45">+</div>
            <div class="absolute bottom-48 left-8 text-green-600/20 text-2xl font-light transform rotate-45">+</div>

            <!-- Bottom Right Plus -->
            <div class="absolute bottom-16 right-20 text-green-600/35 text-6xl font-light transform rotate-45">+</div>
            <div class="absolute bottom-32 right-8 text-green-500/25 text-3xl font-light transform rotate-45">+</div>

            <!-- Center scattered plus signs -->
            <div class="absolute top-1/4 left-1/4 text-green-600/20 text-2xl font-light transform rotate-45">+</div>
            <div class="absolute top-3/4 right-1/4 text-green-700/25 text-3xl font-light transform rotate-45">+</div>
            <div class="absolute top-1/2 left-3/4 text-green-500/30 text-4xl font-light transform rotate-45">+</div>
        </div>

        <!-- Main Content Container -->
        <div class="relative z-10 min-h-screen flex items-center justify-center">
            <div class="px-4 sm:px-6 lg:px-8 max-w-6xl mx-auto">
                <!-- Logo and Title Container - Horizontal Layout -->
                <div class="flex flex-col md:flex-row items-center justify-center gap-4 md:gap-4 mb-12">
                    <!-- Logo -->
                    <div class="flex-shrink-0">
                        <img
                            src="/images/logo.png"
                            alt="SNSU Logo"
                            class="h-40 w-auto md:h-48"
                        />
                    </div>

                    <!-- Text Content -->
                    <div class="text-center md:text-left">
                        <h1 class="text-4xl md:text-6xl lg:text-7xl font-black text-green-800 mb-1 leading-none tracking-tight" style="font-family: 'Poppins', sans-serif;">
                            <div>SNSU</div>
                            <div>iReserve</div>
                        </h1>
                        <p class="text-lg md:text-xl lg:text-2xl text-green-700 font-bold leading-tight tracking-normal" style="font-family: 'Poppins', sans-serif;">
                            Science Laboratory Equipment
                        </p>
                        <p class="text-lg md:text-xl lg:text-2xl text-green-700 font-bold tracking-normal" style="font-family: 'Poppins', sans-serif;">
                            Management System
                        </p>
                    </div>
                </div>

                <!-- Get Started Button -->
                <div class="text-center">
                    <Link
                        v-if="canRegister && !$page.props.auth.user"
                        :href="route('register')"
                        class="inline-block bg-green-600 hover:bg-green-700 text-white px-12 py-4 rounded-full font-semibold text-lg transition duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl"
                    >
                        Get started
                    </Link>
                    <Link
                        v-else-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="inline-block bg-green-600 hover:bg-green-700 text-white px-12 py-4 rounded-full font-semibold text-lg transition duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl"
                    >
                        Go to Dashboard
                    </Link>
                    <Link
                        v-else
                        :href="route('login')"
                        class="inline-block bg-green-600 hover:bg-green-700 text-white px-12 py-4 rounded-full font-semibold text-lg transition duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl"
                    >
                        Get started
                    </Link>
                </div>

            </div>
        </div>

        <!-- Organic Curved Shapes -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <!-- Top curved shape -->
            <svg class="absolute -top-32 -left-32 w-96 h-96 text-green-400/20" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                <path fill="currentColor" d="M44.7,-76.4C58.8,-69.2,71.8,-59.1,79.6,-45.8C87.4,-32.6,90,-16.3,89.8,-0.2C89.6,15.9,86.6,31.8,78.9,45.1C71.2,58.4,58.8,69.1,44.7,76.4C30.6,83.7,15.3,87.6,-0.2,87.9C-15.7,88.2,-31.4,84.9,-45.6,77.6C-59.8,70.3,-72.5,59,-79.8,45.1C-87.1,31.2,-89,14.6,-88.4,-1.8C-87.8,-18.2,-84.7,-36.4,-77.4,-50.7C-70.1,-65,-58.6,-75.4,-44.7,-76.4Z" transform="translate(100 100)" />
            </svg>

            <!-- Bottom curved shape -->
            <svg class="absolute -bottom-32 -right-32 w-96 h-96 text-green-500/15" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                <path fill="currentColor" d="M37.1,-64.1C48.8,-56.5,59.1,-46.7,66.8,-34.9C74.5,-23.1,79.6,-9.3,80.2,5.1C80.8,19.5,76.9,34.5,69.1,47.8C61.3,61.1,49.6,72.7,36.2,79.9C22.8,87.1,7.7,89.9,-7.9,90.3C-23.5,90.7,-39.5,88.7,-53.1,82.1C-66.7,75.5,-77.9,64.3,-84.7,50.9C-91.5,37.5,-93.9,21.9,-92.8,6.9C-91.7,-8.1,-87.1,-22.5,-79.2,-35.1C-71.3,-47.7,-60.1,-58.5,-47.1,-65.7C-34.1,-72.9,-19.3,-76.5,-4.8,-76C9.7,-75.5,25.4,-71.7,37.1,-64.1Z" transform="translate(100 100)" />
            </svg>

            <!-- Side curved elements -->
            <svg class="absolute top-1/2 -left-16 w-64 h-64 text-green-600/10 transform -translate-y-1/2" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                <path fill="currentColor" d="M42.8,-73.9C55.3,-66.8,65.5,-54.4,71.8,-40.1C78.1,-25.8,80.5,-9.6,79.7,6.2C78.9,22,74.9,37.4,67.2,50.7C59.5,64,48.1,75.2,34.8,81.8C21.5,88.4,6.3,90.4,-8.7,89.8C-23.7,89.2,-38.5,86,-51.2,79.1C-63.9,72.2,-74.5,61.6,-80.9,48.6C-87.3,35.6,-89.5,20.2,-88.2,5.2C-86.9,-9.8,-82.1,-24.4,-74.1,-37.1C-66.1,-49.8,-54.9,-60.6,-42.1,-67.4C-29.3,-74.2,-14.7,-77,-0.2,-76.7C14.3,-76.4,30.3,-81,42.8,-73.9Z" transform="translate(100 100)" />
            </svg>

            <svg class="absolute top-1/2 -right-16 w-64 h-64 text-green-700/10 transform -translate-y-1/2" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                <path fill="currentColor" d="M39.2,-67.8C51.1,-60.3,61.2,-50.1,68.7,-37.8C76.2,-25.5,81.1,-11.1,81.4,3.7C81.7,18.5,77.4,33.7,69.8,46.9C62.2,60.1,51.3,71.3,38.4,78.1C25.5,84.9,10.6,87.3,-4.7,87.6C-20,87.9,-35.7,86.1,-49.4,79.8C-63.1,73.5,-74.8,62.7,-81.8,49.4C-88.8,36.1,-91.1,20.3,-89.9,4.9C-88.7,-10.5,-83.9,-25.5,-76.4,-38.7C-68.9,-51.9,-58.7,-63.3,-46.8,-70.8C-34.9,-78.3,-21.3,-81.9,-7.2,-81.2C6.9,-80.5,27.3,-75.3,39.2,-67.8Z" transform="translate(100 100)" />
            </svg>
        </div>
    </div>
</template>
