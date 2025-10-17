<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { useFormatters } from '@/composables/useFormatters';

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

// Use formatters
const { formatStatus } = useFormatters();

// Get equipment image URL
const getEquipmentImageUrl = (imagePath) => {
    return imagePath ? `/storage/${imagePath}` : '/images/equipment.png';
};

// State
const searchTerm = ref('');
const selectedCategory = ref('');

// Dropdown states
const showCategoryDropdown = ref(false);
const categoryButtonRef = ref(null);
const categoryDropdownPos = ref({ top: 0, left: 0, width: 0 });
const windowHeight = ref(0);
const windowWidth = ref(0);

// Close dropdowns when clicking outside
const closeDropdowns = () => {
    showCategoryDropdown.value = false;
};

// Calculate dropdown position - always position directly under button
const calculateDropdownPosition = (buttonRef) => {
    if (!buttonRef.value || typeof window === 'undefined') return { top: 0, left: 0, width: 0 };

    const rect = buttonRef.value.getBoundingClientRect();
    const viewportWidth = windowWidth.value || window.innerWidth;

    let top = rect.bottom + window.scrollY + 2; // 2px gap
    let left = rect.left + window.scrollX;
    let width = rect.width;

    // Ensure dropdown doesn't go off-screen horizontally on mobile
    if (left + width > viewportWidth - 16) {
        left = Math.max(16, viewportWidth - width - 16); // Keep 16px margin from edges
    }
    if (left < 16) {
        left = 16;
        width = Math.min(width, viewportWidth - 32); // Adjust width if needed
    }

    return { top, left, width };
};

// Show category dropdown
const toggleCategoryDropdown = async () => {
    showCategoryDropdown.value = !showCategoryDropdown.value;

    if (showCategoryDropdown.value) {
        await nextTick();
        categoryDropdownPos.value = calculateDropdownPosition(categoryButtonRef);
    }
};

// Update window dimensions
const updateWindowSize = () => {
    if (typeof window !== 'undefined') {
        windowWidth.value = window.innerWidth;
        windowHeight.value = window.innerHeight;
    }
};

// Handle window resize and scroll to recalculate dropdown positions
const handleResize = () => {
    updateWindowSize();
    if (showCategoryDropdown.value) {
        categoryDropdownPos.value = calculateDropdownPosition(categoryButtonRef);
    }
};

const handleScroll = () => {
    // Close dropdowns on scroll to prevent positioning issues
    closeDropdowns();
};

// Click outside to close dropdowns
const handleClickOutside = (event) => {
    if (!event.target.closest('.dropdown-container')) {
        closeDropdowns();
    }
};

onMounted(() => {
    updateWindowSize();
    document.addEventListener('click', handleClickOutside);
    window.addEventListener('resize', handleResize);
    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    window.removeEventListener('resize', handleResize);
    window.removeEventListener('scroll', handleScroll);
});

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
            (item.equipment_name && item.equipment_name.toLowerCase().includes(searchTerm.value.toLowerCase())) ||
            (item.purpose && item.purpose.toLowerCase().includes(searchTerm.value.toLowerCase()))
        );
    }

    return filtered;
});

// Methods
const requestReturn = (reservationId) => {
    if (confirm('Are you sure you want to request the return of this equipment?')) {
        router.post(route('student.equipment.request-return', reservationId), {}, {
            onSuccess: () => {
                // Page will reload automatically with updated data
            },
            onError: (errors) => {
                alert('Error requesting return: ' + (errors.status || 'Unknown error'));
            }
        });
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
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold leading-tight text-gray-900">
                        My Issued Equipment
                    </h2>
                    <p class="text-gray-600 mt-1 text-sm sm:text-base">Equipment currently issued to you and ready for use.</p>
                </div>
                <Link
                    :href="route('student.dashboard')"
                    class="inline-flex items-center px-3 sm:px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 focus:bg-gray-600 active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 w-full sm:w-auto justify-center"
                >
                    Back to Dashboard
                </Link>
            </div>
        </template>

        <div class="py-4 sm:py-6 pb-8 sm:pb-12">
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
                            <div class="relative w-full md:w-48 dropdown-container">
                                <button
                                    ref="categoryButtonRef"
                                    @click="toggleCategoryDropdown"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 bg-white text-left flex items-center justify-between"
                                >
                                    <span>{{ selectedCategory || 'All Categories' }}</span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </div>

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
                    <div class="mt-3 sm:mt-4 flex items-center justify-between text-xs sm:text-sm text-gray-600 px-1">
                        <span class="truncate">
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
                        <div class="p-3 sm:p-6">
                            <!-- Mobile Layout: Stack Everything -->
                            <div class="flex flex-col space-y-3 sm:space-y-0 sm:flex-row sm:items-start sm:justify-between">
                                <!-- Main Content -->
                                <div class="flex-1 w-full">
                                    <div class="flex items-start space-x-3 sm:space-x-4">
                                        <!-- Equipment Image -->
                                        <div class="flex-shrink-0 w-16 h-16 sm:w-20 sm:h-20 bg-gray-50 rounded-lg flex items-center justify-center border border-gray-200">
                                            <img
                                                v-if="equipment.equipment_image"
                                                :src="getEquipmentImageUrl(equipment.equipment_image)"
                                                :alt="equipment.equipment_name || 'Equipment'"
                                                class="max-w-full max-h-full object-contain rounded"
                                            />
                                            <div v-else class="text-gray-400">
                                                <svg class="w-6 h-6 sm:w-8 sm:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                                </svg>
                                            </div>
                                        </div>

                                        <!-- Equipment Details -->
                                        <div class="flex-1 min-w-0">
                                            <!-- Title and Status -->
                                            <div class="flex flex-col space-y-1 sm:space-y-0 sm:flex-row sm:items-center sm:space-x-3 mb-2">
                                                <h3 class="text-base sm:text-lg font-semibold text-gray-900 truncate">
                                                    {{ equipment.equipment_name || 'Multiple Items' }}
                                                    <span v-if="equipment.items_count > 1" class="text-sm text-gray-600 font-normal">
                                                        (+ {{ equipment.items_count - 1 }} more {{ equipment.items_count - 1 === 1 ? 'item' : 'items' }})
                                                    </span>
                                                </h3>
                                                <span
                                                    v-if="equipment.status === 'issued'"
                                                    class="px-2 py-0.5 text-xs font-medium bg-green-100 text-green-800 rounded-full border border-green-200 self-start"
                                                >
                                                    Currently Issued
                                                </span>
                                                <span
                                                    v-else-if="equipment.status === 'return_requested'"
                                                    class="px-2 py-0.5 text-xs font-medium bg-orange-100 text-orange-800 rounded-full border border-orange-200 self-start"
                                                >
                                                    Return Requested
                                                </span>
                                            </div>

                                            <!-- Equipment Info -->
                                            <div class="space-y-1 sm:space-y-2 text-xs sm:text-sm text-gray-600">
                                                <p class="truncate">
                                                    <span class="font-medium text-gray-900">Category:</span>
                                                    {{ equipment.equipment_category }}
                                                </p>
                                                <p v-if="equipment.equipment_location" class="truncate">
                                                    <span class="font-medium text-gray-900">Location:</span>
                                                    {{ equipment.equipment_location }}
                                                </p>
                                                <p class="truncate">
                                                    <span class="font-medium text-gray-900">Issued:</span>
                                                    {{ equipment.issued_date }}
                                                </p>
                                            </div>

                                            <!-- Collapsible Details on Mobile -->
                                            <details class="mt-2 sm:hidden">
                                                <summary class="text-xs text-blue-600 cursor-pointer hover:text-blue-700">
                                                    View Details
                                                </summary>
                                                <div class="mt-2 space-y-1 text-xs text-gray-600 pl-2 border-l-2 border-gray-200">
                                                    <p><span class="font-medium text-gray-900">Reservation Date:</span> {{ equipment.date }}</p>
                                                    <p><span class="font-medium text-gray-900">Time Slot:</span> {{ equipment.start_time }} - {{ equipment.end_time }}</p>
                                                    <p class="break-words"><span class="font-medium text-gray-900">Purpose:</span> {{ equipment.purpose }}</p>
                                                </div>
                                            </details>

                                            <!-- Desktop View Details -->
                                            <div class="hidden sm:block mt-2 space-y-1 text-sm text-gray-600">
                                                <p><span class="font-medium text-gray-900">Reservation Date:</span> {{ equipment.date }}</p>
                                                <p><span class="font-medium text-gray-900">Time Slot:</span> {{ equipment.start_time }} - {{ equipment.end_time }}</p>
                                                <p class="line-clamp-2"><span class="font-medium text-gray-900">Purpose:</span> {{ equipment.purpose }}</p>
                                            </div>

                                            <!-- Show all items if more than 1 -->
                                            <div v-if="equipment.items && equipment.items.length > 1" class="mt-3 pt-3 border-t border-gray-200">
                                                <details class="group">
                                                    <summary class="text-sm font-medium text-blue-600 hover:text-blue-700 cursor-pointer flex items-center">
                                                        <svg class="w-4 h-4 mr-1 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                        </svg>
                                                        View All {{ equipment.items.length }} Items
                                                    </summary>
                                                    <div class="mt-3 space-y-2">
                                                        <div
                                                            v-for="(item, index) in equipment.items"
                                                            :key="item.id"
                                                            class="flex items-center space-x-3 p-2 bg-gray-50 rounded-lg"
                                                        >
                                                            <div class="flex-shrink-0 w-12 h-12 bg-white rounded flex items-center justify-center border border-gray-200">
                                                                <img
                                                                    v-if="item.image"
                                                                    :src="getEquipmentImageUrl(item.image)"
                                                                    :alt="item.name"
                                                                    class="max-w-full max-h-full object-contain"
                                                                />
                                                                <svg v-else class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                                                </svg>
                                                            </div>
                                                            <div class="flex-1 min-w-0">
                                                                <p class="text-sm font-medium text-gray-900 truncate">{{ item.name }}</p>
                                                                <p class="text-xs text-gray-600">{{ item.category }} • Qty: {{ item.quantity }}</p>
                                                            </div>
                                                            <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                                                {{ formatStatus(item.status) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </details>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="w-full sm:w-auto sm:flex-shrink-0 sm:ml-4">
                                    <div class="flex flex-row sm:flex-col gap-2">
                                        <!-- Request Return Button (for issued equipment) -->
                                        <button
                                            v-if="equipment.can_return"
                                            @click="requestReturn(equipment.id)"
                                            class="flex-1 sm:flex-initial inline-flex items-center justify-center px-3 sm:px-4 py-2 border border-transparent text-xs sm:text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition ease-in-out duration-150 whitespace-nowrap"
                                        >
                                            <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                            </svg>
                                            <span class="hidden xs:inline">Request </span>Return
                                        </button>

                                        <!-- Return Requested Status (for return_requested equipment) -->
                                        <div
                                            v-else-if="equipment.return_requested"
                                            class="flex-1 sm:flex-initial inline-flex items-center justify-center px-3 sm:px-4 py-2 border border-orange-300 text-xs sm:text-sm font-medium rounded-md text-orange-800 bg-orange-100"
                                        >
                                            <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span class="hidden xs:inline">Return </span>Requested
                                        </div>

                                        <Link
                                            :href="route('student.equipment.catalog')"
                                            class="flex-1 sm:flex-initial inline-flex items-center justify-center px-3 sm:px-4 py-2 border border-gray-300 text-xs sm:text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150 whitespace-nowrap"
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

    <!-- Portal Dropdowns -->
    <Teleport to="body">
        <!-- Category Dropdown -->
        <div
            v-show="showCategoryDropdown"
            class="fixed z-[9999] bg-white border border-gray-300 rounded-md shadow-lg overflow-y-auto"
            :style="{
                top: categoryDropdownPos.top + 'px',
                left: categoryDropdownPos.left + 'px',
                width: categoryDropdownPos.width + 'px',
                maxHeight: Math.min(240, (windowHeight || 600) - categoryDropdownPos.top - 20) + 'px'
            }"
        >
            <div
                @click="selectedCategory = ''; showCategoryDropdown = false"
                class="px-3 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100"
            >
                All Categories
            </div>
            <div
                v-for="category in categories"
                :key="category"
                @click="selectedCategory = category; showCategoryDropdown = false"
                class="px-3 py-2 hover:bg-gray-100 cursor-pointer"
            >
                {{ category }}
            </div>
        </div>
    </Teleport>
</template>
