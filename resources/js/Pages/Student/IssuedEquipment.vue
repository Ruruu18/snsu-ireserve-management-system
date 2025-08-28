<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    issuedEquipment: {
        type: Object,
        required: true
    },
    stats: {
        type: Object,
        required: true
    }
});

// Get equipment image URL
const getEquipmentImageUrl = (imagePath) => {
    return imagePath ? `/storage/${imagePath}` : '/images/equipment.png';
};

// State
const searchTerm = ref('');
const selectedCategory = ref('');

// Computed properties
const categories = computed(() => {
    const cats = [...new Set(props.issuedEquipment.data.map(item => item.equipment_category))];
    return cats.sort();
});

const filteredEquipment = computed(() => {
    let filtered = props.issuedEquipment.data;

    // Filter by category
    if (selectedCategory.value) {
        filtered = filtered.filter(item => item.equipment_category === selectedCategory.value);
    }

    // Filter by search term
    if (searchTerm.value) {
        filtered = filtered.filter(item =>
            item.equipment_name.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
            item.purpose.toLowerCase().includes(searchTerm.value.toLowerCase())
        );
    }

    return filtered;
});

// Methods
const requestReturn = (equipmentId) => {
    if (confirm('Are you sure you want to request the return of this equipment?')) {
        // For now, we'll just show an alert. This could be extended to actually process the return request
        alert('Return request submitted! Please contact the lab staff to complete the return process.');
    }
};

const clearFilters = () => {
    searchTerm.value = '';
    selectedCategory.value = '';
};

const formatDateTime = (date, time) => {
    return `${date} at ${time}`;
};
</script>

<template>
    <Head title="My Issued Equipment" />

    <StudentLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-900">
                        My Issued Equipment
                    </h2>
                    <p class="text-gray-600 mt-1">Equipment currently issued to you and ready for use.</p>
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
                                    placeholder="Search issued equipment..."
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
                            Showing {{ filteredEquipment.length }} of {{ issuedEquipment.data.length }} issued equipment items
                        </span>
                    </div>
                </div>

                <!-- Equipment List -->
                <div v-if="filteredEquipment.length > 0" class="space-y-4">
                    <div
                        v-for="equipment in filteredEquipment"
                        :key="equipment.id"
                        class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200"
                    >
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-start space-x-4">
                                        <!-- Equipment Image -->
                                        <div class="flex-shrink-0 w-20 h-20 bg-gray-50 rounded-lg flex items-center justify-center border border-gray-200">
                                            <img
                                                v-if="equipment.equipment_image"
                                                :src="getEquipmentImageUrl(equipment.equipment_image)"
                                                :alt="equipment.equipment_name"
                                                class="max-w-full max-h-full object-contain"
                                            />
                                            <div v-else class="text-gray-400">
                                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                                </svg>
                                            </div>
                                        </div>

                                        <!-- Equipment Details -->
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-3 mb-2">
                                                <h3 class="text-lg font-semibold text-gray-900">{{ equipment.equipment_name }}</h3>
                                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full border border-green-200">
                                                    Currently Issued
                                                </span>
                                            </div>

                                            <div class="space-y-2 text-sm text-gray-600">
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div>
                                                        <p><span class="font-medium text-gray-900">Category:</span> {{ equipment.equipment_category }}</p>
                                                        <p v-if="equipment.equipment_location">
                                                            <span class="font-medium text-gray-900">Location:</span> {{ equipment.equipment_location }}
                                                        </p>
                                                        <p><span class="font-medium text-gray-900">Issued Date:</span> {{ equipment.issued_date }}</p>
                                                    </div>
                                                    <div>
                                                        <p><span class="font-medium text-gray-900">Reservation Date:</span> {{ equipment.date }}</p>
                                                        <p><span class="font-medium text-gray-900">Time Slot:</span> {{ equipment.start_time }} - {{ equipment.end_time }}</p>
                                                        <p><span class="font-medium text-gray-900">Purpose:</span> {{ equipment.purpose }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex-shrink-0 ml-6">
                                    <div class="flex flex-col space-y-2">
                                        <button
                                            v-if="equipment.can_return"
                                            @click="requestReturn(equipment.id)"
                                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition ease-in-out duration-150"
                                        >
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                            </svg>
                                            Request Return
                                        </button>
                                        <Link
                                            :href="route('student.equipment.catalog')"
                                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150"
                                        >
                                            Browse More
                                        </Link>
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
                        {{ searchTerm || selectedCategory ? 'No matching equipment found' : 'No equipment currently issued' }}
                    </h3>
                    <p class="text-gray-500 mb-6">
                        {{ searchTerm || selectedCategory
                            ? 'Try adjusting your search or filter criteria.'
                            : 'You don\'t have any equipment currently issued to you. Browse the catalog to make a new request.'
                        }}
                    </p>
                    <div class="flex justify-center space-x-4">
                        <button
                            v-if="searchTerm || selectedCategory"
                            @click="clearFilters"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-blue-700 transition ease-in-out duration-150"
                        >
                            Clear Filters
                        </button>
                        <Link
                            :href="route('student.equipment.catalog')"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-blue-700 transition ease-in-out duration-150"
                        >
                            Browse Equipment
                        </Link>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="issuedEquipment.links && issuedEquipment.links.length > 3" class="mt-8">
                    <nav class="flex items-center justify-between">
                        <div class="flex-1 flex justify-between sm:hidden">
                            <Link
                                v-if="issuedEquipment.prev_page_url"
                                :href="issuedEquipment.prev_page_url"
                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Previous
                            </Link>
                            <Link
                                v-if="issuedEquipment.next_page_url"
                                :href="issuedEquipment.next_page_url"
                                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Next
                            </Link>
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Showing
                                    <span class="font-medium">{{ issuedEquipment.from }}</span>
                                    to
                                    <span class="font-medium">{{ issuedEquipment.to }}</span>
                                    of
                                    <span class="font-medium">{{ issuedEquipment.total }}</span>
                                    results
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                    <Link
                                        v-for="link in issuedEquipment.links"
                                        :key="link.label"
                                        :href="link.url"
                                        :class="[
                                            link.active
                                                ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                                                : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                                            link.url
                                                ? 'cursor-pointer'
                                                : 'cursor-not-allowed opacity-50',
                                            'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                                        ]"
                                        v-html="link.label"
                                    />
                                </nav>
                            </div>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </StudentLayout>
</template>
