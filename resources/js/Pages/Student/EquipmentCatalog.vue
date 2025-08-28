<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import ReservationModal from '@/Components/ReservationModal.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    availableEquipment: {
        type: Array,
        required: true
    },
    stats: {
        type: Object,
        required: true
    }
});

// Search and filter state
const searchTerm = ref('');
const selectedCategory = ref('');

// Modal state
const showReservationModal = ref(false);
const selectedEquipment = ref(null);

// Computed properties
const categories = computed(() => {
    return props.availableEquipment.map(cat => cat.category);
});

const filteredEquipment = computed(() => {
    let filtered = props.availableEquipment;

    // Filter by category
    if (selectedCategory.value) {
        filtered = filtered.filter(cat => cat.category === selectedCategory.value);
    }

    // Filter by search term
    if (searchTerm.value) {
        filtered = filtered.map(category => ({
            ...category,
            equipment: category.equipment.filter(item =>
                item.name.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
                item.description?.toLowerCase().includes(searchTerm.value.toLowerCase())
            )
        })).filter(category => category.equipment.length > 0);
    }

    return filtered;
});

const totalEquipmentCount = computed(() => {
    return props.availableEquipment.reduce((total, category) => total + category.equipment.length, 0);
});

// Get equipment image URL
const getEquipmentImageUrl = (imagePath) => {
    return imagePath ? `/storage/${imagePath}` : '/images/equipment.png';
};

// Methods
const requestEquipment = (equipment) => {
    selectedEquipment.value = equipment;
    showReservationModal.value = true;
};

const handleReservationCreated = () => {
    showReservationModal.value = false;
    selectedEquipment.value = null;
};

const clearFilters = () => {
    searchTerm.value = '';
    selectedCategory.value = '';
};
</script>

<template>
    <Head title="Equipment Catalog" />

    <StudentLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-900">
                        Equipment Catalog
                    </h2>
                    <p class="text-gray-600 mt-1">Browse and request available equipment for your needs.</p>
                </div>
                <Link
                    :href="route('student.dashboard')"
                    class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 focus:bg-gray-600 active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    Back to Dashboard
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Search and Filter Bar -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0 md:space-x-4">
                        <!-- Search Input -->
                        <div class="flex-1">
                            <label for="search" class="sr-only">Search equipment</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input
                                    id="search"
                                    v-model="searchTerm"
                                    type="text"
                                    placeholder="Search equipment by name or description..."
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>
                        </div>

                        <!-- Category Filter -->
                        <div class="flex items-center space-x-4">
                            <select
                                v-model="selectedCategory"
                                class="block w-full md:w-48 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="">All Categories</option>
                                <option v-for="category in categories" :key="category" :value="category">
                                    {{ category }}
                                </option>
                            </select>

                            <button
                                v-if="searchTerm || selectedCategory"
                                @click="clearFilters"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                Clear
                            </button>
                        </div>
                    </div>

                    <!-- Results Count -->
                    <div class="mt-4 flex items-center justify-between text-sm text-gray-600">
                        <span>
                            Showing {{ filteredEquipment.reduce((total, cat) => total + cat.equipment.length, 0) }} of {{ totalEquipmentCount }} equipment items
                        </span>
                    </div>
                </div>

                <!-- Equipment Grid -->
                <div v-if="filteredEquipment.length > 0" class="space-y-8">
                    <div v-for="category in filteredEquipment" :key="category.category" class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <!-- Category Header -->
                        <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-slate-800">{{ category.category }}</h3>
                            <p class="text-sm text-gray-600 mt-1">{{ category.equipment.length }} items available</p>
                        </div>

                        <!-- Equipment Grid -->
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                                <div
                                    v-for="equipment in category.equipment"
                                    :key="equipment.id"
                                    class="bg-gray-50 rounded-lg border border-gray-200 overflow-hidden hover:shadow-md transition-all duration-200 group"
                                >
                                    <!-- Equipment Image -->
                                    <div class="h-48 bg-white flex items-center justify-center">
                                        <img
                                            v-if="equipment.image"
                                            :src="getEquipmentImageUrl(equipment.image)"
                                            :alt="equipment.name"
                                            class="max-h-full max-w-full object-contain p-4"
                                        />
                                        <div v-else class="text-gray-400">
                                            <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- Equipment Info -->
                                    <div class="p-4">
                                        <h4 class="text-lg font-semibold text-gray-900 group-hover:text-blue-600 transition-colors duration-200">
                                            {{ equipment.name }}
                                        </h4>
                                        <p v-if="equipment.description" class="text-sm text-gray-600 mt-1 line-clamp-2">
                                            {{ equipment.description }}
                                        </p>
                                        <div class="mt-3 space-y-1">
                                            <p v-if="equipment.location" class="text-xs text-gray-500">
                                                <span class="font-medium">Location:</span> {{ equipment.location }}
                                            </p>
                                            <p v-if="equipment.serial_number" class="text-xs text-gray-500">
                                                <span class="font-medium">Serial:</span> {{ equipment.serial_number }}
                                            </p>
                                        </div>

                                        <!-- Action Button -->
                                        <div class="mt-4">
                                            <button
                                                @click="requestEquipment(equipment)"
                                                class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150"
                                            >
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                </svg>
                                                Request Equipment
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12">
                    <div class="mb-4">
                        <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">
                        {{ searchTerm || selectedCategory ? 'No equipment found' : 'No equipment available' }}
                    </h3>
                    <p class="text-gray-500 mb-6">
                        {{ searchTerm || selectedCategory ? 'Try adjusting your search or filter criteria.' : 'There is currently no equipment available for reservation.' }}
                    </p>
                    <button
                        v-if="searchTerm || selectedCategory"
                        @click="clearFilters"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-blue-700 transition ease-in-out duration-150"
                    >
                        Clear Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Reservation Modal -->
        <ReservationModal
            :show="showReservationModal"
            :equipment="selectedEquipment"
            :availableEquipment="[]"
            @close="showReservationModal = false"
            @created="handleReservationCreated"
        />
    </StudentLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
