<script setup>
import { useCart } from '@/composables/useCart';
import { router } from '@inertiajs/vue3';

const { totalItems } = useCart();

const goToCart = () => {
    router.visit(route('student.cart.index'));
};
</script>

<template>
    <button
        @click="goToCart"
        class="relative p-2 text-gray-600 hover:text-gray-900 transition-colors duration-200 rounded-lg hover:bg-gray-100"
        title="View Cart"
    >
        <!-- Cart Icon -->
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.1 5H17M9 19.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM20.5 19.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"
            />
        </svg>

        <!-- Cart Count Badge -->
        <span
            v-if="totalItems > 0"
            class="absolute -top-1 -right-1 bg-blue-600 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center min-w-[20px] animate-pulse"
        >
            {{ totalItems > 99 ? '99+' : totalItems }}
        </span>
    </button>
</template>