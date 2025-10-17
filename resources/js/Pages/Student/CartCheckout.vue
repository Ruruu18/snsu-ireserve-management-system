<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { useCart } from '@/composables/useCart';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    cartItems: {
        type: Array,
        default: () => []
    }
});

const { cartItems, clearCart, isEmpty } = useCart();

// Form data
const form = useForm({
    purpose: '',
    date: '',
    start_time: '',
    end_time: '',
    notes: '',
    items: []
});

// Watch for cart changes and update form
watch(cartItems, (newItems) => {
    form.items = newItems;
}, { immediate: true });

// Redirect if cart is empty
watch(isEmpty, (empty) => {
    if (empty) {
        window.location.href = route('student.equipment.catalog');
    }
}, { immediate: true });

// Get equipment image URL
const getEquipmentImageUrl = (imagePath) => {
    return imagePath ? `/storage/${imagePath}` : '/images/equipment.png';
};

// Computed
const totalItems = computed(() => {
    return form.items.reduce((total, item) => total + item.quantity, 0);
});

const isValidTimeRange = computed(() => {
    if (!form.start_time || !form.end_time) return true;
    return form.start_time < form.end_time;
});

// Methods
const submitReservation = () => {
    if (!isValidTimeRange.value) {
        alert('End time must be after start time.');
        return;
    }

    form.post(route('student.cart.process'), {
        onSuccess: () => {
            // Clear cart after successful checkout
            clearCart();
        },
        onError: (errors) => {
            console.error('Checkout errors:', errors);
        }
    });
};

// Set minimum date to today
const today = new Date().toISOString().split('T')[0];
</script>

<template>
    <Head title="Cart Checkout" />

    <StudentLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold leading-tight text-gray-900">
                        Cart Checkout
                    </h2>
                    <p class="text-gray-600 mt-1 text-sm sm:text-base">Review and confirm your equipment reservation.</p>
                </div>
            </div>
        </template>

        <div class="py-4 sm:py-6 pb-8 sm:pb-12">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <form @submit.prevent="submitReservation" class="space-y-6">
                    <!-- Cart Items Review -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            Selected Equipment ({{ totalItems }} items)
                        </h3>

                        <div v-if="!isEmpty" class="space-y-4">
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
                                    <h4 class="text-sm font-medium text-gray-900">{{ item.name }}</h4>
                                    <p v-if="item.description" class="text-sm text-gray-600 mt-1">{{ item.description }}</p>
                                    <div class="flex items-center space-x-4 mt-2">
                                        <span class="text-xs text-gray-500">{{ item.category }}</span>
                                        <span v-if="item.location" class="text-xs text-gray-500">📍 {{ item.location }}</span>
                                        <span class="text-sm font-medium text-blue-600">Qty: {{ item.quantity }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Empty Cart State -->
                        <div v-else class="text-center py-8">
                            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.1 5H17M9 19.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM20.5 19.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Your cart is empty</h3>
                            <p class="text-gray-500">Add some equipment from the catalog to proceed with checkout.</p>
                        </div>
                    </div>

                    <!-- Reservation Details Form -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Reservation Details</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Purpose -->
                            <div class="md:col-span-2">
                                <InputLabel for="purpose" value="Purpose of Use *" />
                                <TextInput
                                    id="purpose"
                                    v-model="form.purpose"
                                    type="text"
                                    class="mt-1 block w-full"
                                    placeholder="e.g., Chemistry Lab Experiment, Physics Practical"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.purpose" />
                            </div>

                            <!-- Date -->
                            <div>
                                <InputLabel for="date" value="Reservation Date *" />
                                <TextInput
                                    id="date"
                                    v-model="form.date"
                                    type="date"
                                    :min="today"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.date" />
                            </div>

                            <!-- Time Range -->
                            <div class="space-y-4">
                                <div>
                                    <InputLabel for="start_time" value="Start Time *" />
                                    <TextInput
                                        id="start_time"
                                        v-model="form.start_time"
                                        type="time"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.start_time" />
                                </div>

                                <div>
                                    <InputLabel for="end_time" value="End Time *" />
                                    <TextInput
                                        id="end_time"
                                        v-model="form.end_time"
                                        type="time"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.end_time" />
                                    <p v-if="!isValidTimeRange" class="mt-1 text-sm text-red-600">
                                        End time must be after start time.
                                    </p>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="md:col-span-2">
                                <InputLabel for="notes" value="Additional Notes (Optional)" />
                                <textarea
                                    id="notes"
                                    v-model="form.notes"
                                    rows="3"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="Any special requirements or additional information..."
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.notes" />
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row items-center justify-between space-y-3 sm:space-y-0 sm:space-x-4">
                        <SecondaryButton
                            type="button"
                            @click="$inertia.visit(route('student.equipment.catalog'))"
                            class="w-full sm:w-auto"
                        >
                            Back to Catalog
                        </SecondaryButton>

                        <PrimaryButton
                            type="submit"
                            :disabled="form.processing || !isValidTimeRange"
                            class="w-full sm:w-auto"
                        >
                            <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ form.processing ? 'Processing...' : 'Confirm Reservation & Generate QR' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </StudentLayout>
</template>