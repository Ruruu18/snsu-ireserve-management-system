<script setup>
import { useCart } from '@/composables/useCart';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const {
    cartItems,
    isCartOpen,
    totalItems,
    isEmpty,
    removeFromCart,
    updateQuantity,
    clearCart,
    closeCart,
    proceedToCheckout
} = useCart();

// Get equipment image URL
const getEquipmentImageUrl = (imagePath) => {
    return imagePath ? `/storage/${imagePath}` : '/images/equipment.png';
};

// Handle quantity change
const handleQuantityChange = (equipmentId, event) => {
    const newQuantity = parseInt(event.target.value);
    updateQuantity(equipmentId, newQuantity);
};
</script>

<template>
    <Modal :show="isCartOpen" @close="closeCart" max-width="4xl">
        <div class="p-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.1 5H17M9 19.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM20.5 19.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Equipment Cart</h3>
                        <p class="text-sm text-gray-500">{{ totalItems }} item{{ totalItems !== 1 ? 's' : '' }} selected</p>
                    </div>
                </div>
                <button
                    @click="closeCart"
                    class="text-gray-400 hover:text-gray-600 transition-colors"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Empty Cart State -->
            <div v-if="isEmpty" class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.1 5H17M9 19.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM20.5 19.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Your cart is empty</h3>
                <p class="text-gray-500 mb-6">Add some equipment from the catalog to get started.</p>
                <SecondaryButton @click="closeCart">
                    Continue Browsing
                </SecondaryButton>
            </div>

            <!-- Cart Items -->
            <div v-else class="space-y-4 max-h-96 overflow-y-auto">
                <div
                    v-for="item in cartItems"
                    :key="item.id"
                    class="flex items-start space-x-4 p-4 bg-gray-50 rounded-lg"
                >
                    <!-- Equipment Image -->
                    <div class="flex-shrink-0 w-16 h-16 bg-white rounded-lg flex items-center justify-center border border-gray-200">
                        <img
                            v-if="item.image"
                            :src="getEquipmentImageUrl(item.image)"
                            :alt="item.name"
                            class="max-w-full max-h-full object-contain"
                        />
                        <div v-else class="text-gray-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                    </div>

                    <!-- Item Details -->
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-medium text-gray-900 truncate">{{ item.name }}</h4>
                        <p v-if="item.description" class="text-sm text-gray-600 mt-1 line-clamp-2">{{ item.description }}</p>
                        <div class="flex items-center space-x-4 mt-2">
                            <span class="text-xs text-gray-500">{{ item.category }}</span>
                            <span v-if="item.location" class="text-xs text-gray-500">📍 {{ item.location }}</span>
                        </div>
                    </div>

                    <!-- Quantity Controls -->
                    <div class="flex items-center space-x-2">
                        <label class="text-sm text-gray-700">Qty:</label>
                        <select
                            :value="item.quantity"
                            @change="handleQuantityChange(item.id, $event)"
                            class="border border-gray-300 rounded-md px-2 py-1 text-sm w-16 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option v-for="n in 10" :key="n" :value="n">{{ n }}</option>
                        </select>
                    </div>

                    <!-- Remove Button -->
                    <button
                        @click="removeFromCart(item.id)"
                        class="text-red-500 hover:text-red-700 p-1 transition-colors"
                        title="Remove from cart"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Cart Actions -->
            <div v-if="!isEmpty" class="pt-6 border-t border-gray-200 mt-6">
                <!-- Mobile: Stack vertically -->
                <div class="sm:hidden space-y-3">
                    <PrimaryButton @click="proceedToCheckout" class="w-full justify-center">
                        Proceed to Checkout
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </PrimaryButton>
                    <div class="flex space-x-3">
                        <SecondaryButton @click="closeCart" class="flex-1 justify-center">
                            Continue Browsing
                        </SecondaryButton>
                        <DangerButton @click="clearCart" class="flex-1 justify-center text-sm">
                            Clear Cart
                        </DangerButton>
                    </div>
                </div>

                <!-- Desktop: Original layout -->
                <div class="hidden sm:flex items-center justify-between">
                    <div class="flex space-x-3">
                        <SecondaryButton @click="closeCart">
                            Continue Browsing
                        </SecondaryButton>
                        <DangerButton @click="clearCart" class="text-sm">
                            Clear Cart
                        </DangerButton>
                    </div>

                    <PrimaryButton @click="proceedToCheckout" class="px-6 py-2">
                        Proceed to Checkout
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </PrimaryButton>
                </div>
            </div>
        </div>
    </Modal>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>