<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    cartItems: {
        type: Array,
        required: true
    },
    totalItems: {
        type: Number,
        required: true
    }
});

// Get equipment image URL
const getEquipmentImageUrl = (imagePath) => {
    return imagePath ? `/storage/${imagePath}` : '/images/equipment.png';
};

// Update quantity
const updateQuantity = (equipmentId, newQuantity) => {
    if (newQuantity < 1) {
        removeFromCart(equipmentId);
        return;
    }

    router.post(route('student.cart.update', equipmentId), {
        quantity: newQuantity
    }, {
        preserveScroll: true,
        onSuccess: () => {
            // Cart will be updated automatically
        }
    });
};

// Remove from cart
const removeFromCart = (equipmentId) => {
    if (confirm('Are you sure you want to remove this item from your cart?')) {
        router.delete(route('student.cart.remove', equipmentId), {
            preserveScroll: true
        });
    }
};

// Clear cart
const clearCart = () => {
    if (confirm('Are you sure you want to clear your entire cart?')) {
        router.delete(route('student.cart.clear'), {
            preserveScroll: true
        });
    }
};

// Proceed to checkout
const proceedToCheckout = () => {
    router.visit(route('student.cart.checkout'));
};
</script>

<template>
    <Head title="Shopping Cart" />

    <StudentLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold leading-tight text-gray-900">
                        Shopping Cart
                    </h2>
                    <p class="text-gray-600 mt-1 text-sm sm:text-base">Review and manage your cart items before checkout.</p>
                </div>
                <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
                    <Link
                        :href="route('student.equipment.catalog')"
                        class="inline-flex items-center px-3 sm:px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 focus:bg-gray-600 active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 w-full sm:w-auto justify-center"
                    >
                        Continue Shopping
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6 sm:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Cart Items -->
                <div v-if="cartItems.length > 0" class="space-y-6">
                    <!-- Cart Header -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">
                                    Cart Items ({{ totalItems }} {{ totalItems === 1 ? 'item' : 'items' }})
                                </h3>
                                <p class="text-sm text-gray-600 mt-1">Review your selected equipment</p>
                            </div>
                            <button
                                @click="clearCart"
                                class="text-red-600 hover:text-red-800 text-sm font-medium transition duration-200"
                            >
                                Clear Cart
                            </button>
                        </div>
                    </div>

                    <!-- Cart Items List -->
                    <div class="space-y-4">
                        <div
                            v-for="item in cartItems"
                            :key="item.id"
                            class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200"
                        >
                            <div class="p-4 sm:p-6">
                                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                                    <!-- Equipment Image -->
                                    <div class="flex-shrink-0 w-20 h-20 sm:w-24 sm:h-24 bg-gray-50 rounded-lg flex items-center justify-center border border-gray-200">
                                        <img
                                            :src="getEquipmentImageUrl(item.image)"
                                            :alt="item.name"
                                            class="max-w-full max-h-full object-contain"
                                        />
                                    </div>

                                    <!-- Item Details -->
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-lg font-semibold text-gray-900 mb-1">{{ item.name }}</h4>
                                        <p class="text-sm text-gray-600 mb-2">
                                            <span class="font-medium">Category:</span> {{ item.category }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            <span class="font-medium">Available:</span> {{ item.available_quantity }} units
                                        </p>
                                    </div>

                                    <!-- Quantity Controls -->
                                    <div class="flex items-center gap-4 w-full sm:w-auto">
                                        <div class="flex items-center gap-2">
                                            <button
                                                @click="updateQuantity(item.id, item.quantity - 1)"
                                                class="w-8 h-8 flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded-md transition duration-200"
                                            >
                                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                                </svg>
                                            </button>
                                            <span class="w-12 text-center font-semibold text-gray-900">{{ item.quantity }}</span>
                                            <button
                                                @click="updateQuantity(item.id, item.quantity + 1)"
                                                :disabled="item.quantity >= item.available_quantity"
                                                :class="[
                                                    'w-8 h-8 flex items-center justify-center rounded-md transition duration-200',
                                                    item.quantity >= item.available_quantity
                                                        ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                                        : 'bg-blue-100 hover:bg-blue-200 text-blue-600'
                                                ]"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                </svg>
                                            </button>
                                        </div>

                                        <button
                                            @click="removeFromCart(item.id)"
                                            class="p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-md transition duration-200"
                                            title="Remove from cart"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Checkout Section -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Ready to reserve?</h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    Total: {{ totalItems }} {{ totalItems === 1 ? 'item' : 'items' }}
                                </p>
                            </div>
                            <button
                                @click="proceedToCheckout"
                                class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                Proceed to Checkout
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Empty Cart State -->
                <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 sm:p-12 text-center">
                    <div class="mb-6">
                        <svg class="w-24 h-24 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-3">Your cart is empty</h3>
                    <p class="text-gray-600 mb-8 max-w-md mx-auto">
                        Start browsing our equipment catalog and add items to your cart to make a reservation.
                    </p>
                    <Link
                        :href="route('student.equipment.catalog')"
                        class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Browse Equipment
                    </Link>
                </div>
            </div>
        </div>
    </StudentLayout>
</template>
