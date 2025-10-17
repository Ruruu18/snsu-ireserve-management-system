<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';

const props = defineProps({
    requestedEquipment: {
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
const selectedStatus = ref('');
const selectedCategory = ref('');

// Dropdown states
const showStatusDropdown = ref(false);
const showCategoryDropdown = ref(false);
const statusButtonRef = ref(null);
const categoryButtonRef = ref(null);
const statusDropdownPos = ref({ top: 0, left: 0, width: 0 });
const categoryDropdownPos = ref({ top: 0, left: 0, width: 0 });
const windowHeight = ref(0);
const windowWidth = ref(0);

// Close dropdowns when clicking outside
const closeDropdowns = () => {
    showStatusDropdown.value = false;
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

// Show status dropdown
const toggleStatusDropdown = async () => {
    showCategoryDropdown.value = false;
    showStatusDropdown.value = !showStatusDropdown.value;

    if (showStatusDropdown.value) {
        await nextTick();
        statusDropdownPos.value = calculateDropdownPosition(statusButtonRef);
    }
};

// Show category dropdown
const toggleCategoryDropdown = async () => {
    showStatusDropdown.value = false;
    showCategoryDropdown.value = !showCategoryDropdown.value;

    if (showCategoryDropdown.value) {
        await nextTick();
        categoryDropdownPos.value = calculateDropdownPosition(categoryButtonRef);
    }
};

// Status colors and icons
const getStatusColor = (status) => {
    switch (status) {
        case 'approved':
            return 'bg-green-100 text-green-800 border-green-200';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800 border-yellow-200';
        case 'issued':
            return 'bg-purple-100 text-purple-800 border-purple-200';
        case 'return_requested':
            return 'bg-orange-100 text-orange-800 border-orange-200';
        case 'completed':
            return 'bg-blue-100 text-blue-800 border-blue-200';
        case 'cancelled':
            return 'bg-gray-100 text-gray-800 border-gray-200';
        default:
            return 'bg-gray-100 text-gray-800 border-gray-200';
    }
};

const getStatusIcon = (status) => {
    switch (status) {
        case 'approved':
            return '✅';
        case 'pending':
            return '⏳';
        case 'issued':
            return '📦';
        case 'return_requested':
            return '🔄';
        case 'completed':
            return '🎉';
        case 'cancelled':
            return '🚫';
        default:
            return '❓';
    }
};

// Computed properties
const statuses = ['pending', 'approved', 'issued', 'return_requested', 'completed', 'cancelled'];

const categories = computed(() => {
    const cats = [...new Set(props.requestedEquipment.data
        .map(item => item.equipment_category)
        .filter(category => category != null)
    )];
    return cats.sort();
});

const filteredEquipment = computed(() => {
    let filtered = props.requestedEquipment.data;

    // Filter by status
    if (selectedStatus.value) {
        filtered = filtered.filter(item => item.status === selectedStatus.value);
    }

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

const statusCounts = computed(() => {
    const counts = {};
    statuses.forEach(status => {
        counts[status] = props.requestedEquipment.data.filter(item => item.status === status).length;
    });
    return counts;
});

// Methods
const cancelReservation = (reservationId) => {
    if (confirm('Are you sure you want to cancel this reservation?')) {
        router.patch(route('reservations.cancel', reservationId), {}, {
            onSuccess: () => {
                // Page will reload automatically with updated data
            },
            onError: (errors) => {
                alert('Error canceling reservation: ' + (errors.status || 'Unknown error'));
            }
        });
    }
};

const clearFilters = () => {
    searchTerm.value = '';
    selectedStatus.value = '';
    selectedCategory.value = '';
};

// Click outside to close dropdowns
const handleClickOutside = (event) => {
    if (!event.target.closest('.dropdown-container')) {
        closeDropdowns();
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
    if (showStatusDropdown.value) {
        statusDropdownPos.value = calculateDropdownPosition(statusButtonRef);
    }
    if (showCategoryDropdown.value) {
        categoryDropdownPos.value = calculateDropdownPosition(categoryButtonRef);
    }
};

const handleScroll = () => {
    // Close dropdowns on scroll to prevent positioning issues
    closeDropdowns();
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

const getStatusDescription = (status) => {
    switch (status) {
        case 'pending':
            return 'Waiting for admin approval';
        case 'approved':
            return 'Approved and ready for pickup';
        case 'issued':
            return 'Equipment has been issued to you';
        case 'return_requested':
            return 'Return request submitted, waiting for processing';
        case 'completed':
            return 'Equipment returned successfully';
        case 'cancelled':
            return 'Request was cancelled';
        default:
            return 'Unknown status';
    }
};

const getStatusDisplayName = (status) => {
    switch (status) {
        case 'return_requested':
            return 'Return Request';
        case 'pending':
            return 'Pending';
        case 'approved':
            return 'Approved';
        case 'issued':
            return 'Issued';
        case 'completed':
            return 'Completed';
        case 'cancelled':
            return 'Cancelled';
        default:
            return status;
    }
};
</script>

<template>
    <Head title="Requested Equipment" />

    <StudentLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold leading-tight text-gray-900">
                        Requested Equipment
                    </h2>
                    <p class="text-gray-600 mt-1 text-sm sm:text-base">Track the status of all your equipment requests and reservations.</p>
                </div>
                <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
                    <Link
                        :href="route('student.dashboard')"
                        class="inline-flex items-center px-3 sm:px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 focus:bg-gray-600 active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 w-full sm:w-auto justify-center"
                    >
                        Back to Dashboard
                    </Link>
                    <Link
                        :href="route('student.equipment.catalog')"
                        class="inline-flex items-center px-3 sm:px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 w-full sm:w-auto justify-center"
                    >
                        New Request
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-4 sm:py-6 pb-8 sm:pb-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Status Overview Cards -->
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                    <div
                        v-for="status in statuses"
                        :key="status"
                        @click="selectedStatus = selectedStatus === status ? '' : status"
                        :class="[
                            'bg-white rounded-lg shadow-sm border-2 p-4 cursor-pointer transition-all duration-200 hover:shadow-md',
                            selectedStatus === status ? 'border-blue-500 bg-blue-50' : 'border-gray-200'
                        ]"
                    >
                        <div class="text-center">
                            <div class="text-2xl mb-1">{{ getStatusIcon(status) }}</div>
                            <div class="text-2xl font-bold text-gray-900">{{ statusCounts[status] }}</div>
                            <div class="text-sm font-medium text-gray-700">{{ getStatusDisplayName(status) }}</div>
                        </div>
                    </div>
                </div>

                <!-- Search and Filter Bar -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6 relative">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0 lg:space-x-4">
                        <!-- Search Input -->
                        <div class="flex-1">
                            <label for="search" class="sr-only">Search requests</label>
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
                                    placeholder="Search by equipment name or purpose..."
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>
                        </div>

                        <!-- Filters -->
                        <div class="flex flex-col sm:flex-row items-center space-y-2 sm:space-y-0 sm:space-x-4">
                            <!-- Status Dropdown -->
                            <div class="relative w-full sm:w-48 dropdown-container">
                                <button
                                    ref="statusButtonRef"
                                    @click="toggleStatusDropdown"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 bg-white text-left flex items-center justify-between"
                                >
                                    <span>{{ selectedStatus ? getStatusDisplayName(selectedStatus) : 'All Statuses' }}</span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Category Dropdown -->
                            <div class="relative w-full sm:w-48 dropdown-container">
                                <button
                                    ref="categoryButtonRef"
                                    @click="toggleCategoryDropdown"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 bg-white text-left flex items-center justify-between"
                                >
                                    <span>{{ selectedCategory || 'All Categories' }}</span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </div>

                            <button
                                v-if="searchTerm || selectedStatus || selectedCategory"
                                @click="clearFilters"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                Clear
                            </button>
                        </div>
                    </div>

                    <!-- Results Count -->
                    <div class="mt-4 flex items-center justify-between text-xs sm:text-sm text-gray-600 px-1">
                        <span class="truncate">
                            Showing {{ filteredEquipment.length }} of {{ requestedEquipment.data.length }} requests
                        </span>
                    </div>
                </div>

                <!-- Equipment Requests List -->
                <div v-if="filteredEquipment.length > 0" class="space-y-4">
                    <div
                        v-for="request in filteredEquipment"
                        :key="request.id"
                        class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200"
                    >
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-start space-x-4">
                                        <!-- Equipment Image -->
                                        <div class="flex-shrink-0 w-16 h-16 bg-gray-50 rounded-lg flex items-center justify-center border border-gray-200">
                                            <img
                                                v-if="request.equipment_image"
                                                :src="getEquipmentImageUrl(request.equipment_image)"
                                                :alt="request.equipment_name || 'Equipment'"
                                                class="max-w-full max-h-full object-contain"
                                            />
                                            <div v-else class="text-gray-400">
                                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                                </svg>
                                            </div>
                                        </div>

                                        <!-- Request Details -->
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-3 mb-2">
                                                <span class="text-2xl">{{ getStatusIcon(request.status) }}</span>
                                                <h3 class="text-lg font-semibold text-gray-900">{{ request.equipment_name || 'Multiple Items' }}</h3>
                                                <span
                                                    :class="getStatusColor(request.status)"
                                                    class="px-3 py-1 rounded-full text-sm font-medium border"
                                                >
                                                    {{ getStatusDisplayName(request.status) }}
                                                </span>
                                            </div>

                                            <div class="space-y-2 text-sm text-gray-600">
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div>
                                                        <p><span class="font-medium text-gray-900">Category:</span> {{ request.equipment_category }}</p>
                                                        <p><span class="font-medium text-gray-900">Date:</span> {{ request.date }}</p>
                                                        <p><span class="font-medium text-gray-900">Time:</span> {{ request.start_time }} - {{ request.end_time }}</p>
                                                        <p><span class="font-medium text-gray-900">Purpose:</span> {{ request.purpose }}</p>
                                                    </div>
                                                    <div>
                                                        <p><span class="font-medium text-gray-900">Status:</span> {{ getStatusDescription(request.status) }}</p>
                                                        <p><span class="font-medium text-gray-900">Requested:</span> {{ request.created_at }}</p>
                                                        <p v-if="request.updated_at !== request.created_at">
                                                            <span class="font-medium text-gray-900">Last Updated:</span> {{ request.updated_at }}
                                                        </p>
                                                        <p v-if="request.admin_notes" class="mt-2">
                                                            <span class="font-medium text-gray-900">Admin Notes:</span>
                                                            <span class="block text-sm text-gray-600 mt-1">{{ request.admin_notes }}</span>
                                                        </p>
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
                                            @click="$inertia.visit(route('student.reservation.qr', request.id))"
                                            class="text-blue-600 hover:text-blue-800 text-sm font-medium transition duration-200 hover:underline flex items-center"
                                        >
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M0 0h6v6H0V0zm2 2v2h2V2H2zM0 8h6v6H0V8zm2 2v2h2v-2H2zM8 0h6v6H8V0zm2 2v2h2V2h-2zM8 8h2v2H8V8zM8 12h2v2H8v-2zM12 8h2v2h-2V8zM8 16h6v2H8v-2zM16 16h2v2h-2v-2zM18 8h2v6h-2V8zM16 0h2v2h-2V0zM20 0h2v2h-2V0zM16 4h6v2h-6V4zM20 6h2v2h-2V6z"/>
                                            </svg>
                                            View QR
                                        </button>
                                        <button
                                            v-if="request.can_cancel"
                                            @click="cancelReservation(request.id)"
                                            class="text-red-600 hover:text-red-800 text-sm font-medium transition duration-200 hover:underline"
                                        >
                                            Cancel Request
                                        </button>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">
                        {{ searchTerm || selectedStatus || selectedCategory ? 'No matching requests found' : 'No equipment requests yet' }}
                    </h3>
                    <p class="text-gray-500 mb-6">
                        {{ searchTerm || selectedStatus || selectedCategory
                            ? 'Try adjusting your search or filter criteria.'
                            : 'You haven\'t made any equipment requests yet. Browse the catalog to get started.'
                        }}
                    </p>
                    <div class="flex justify-center space-x-4">
                        <button
                            v-if="searchTerm || selectedStatus || selectedCategory"
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
                <div v-if="requestedEquipment.links && requestedEquipment.links.length > 3" class="mt-8">
                    <nav class="flex items-center justify-between">
                        <div class="flex-1 flex justify-between sm:hidden">
                            <Link
                                v-if="requestedEquipment.prev_page_url"
                                :href="requestedEquipment.prev_page_url"
                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Previous
                            </Link>
                            <Link
                                v-if="requestedEquipment.next_page_url"
                                :href="requestedEquipment.next_page_url"
                                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Next
                            </Link>
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Showing
                                    <span class="font-medium">{{ requestedEquipment.from }}</span>
                                    to
                                    <span class="font-medium">{{ requestedEquipment.to }}</span>
                                    of
                                    <span class="font-medium">{{ requestedEquipment.total }}</span>
                                    results
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                    <Link
                                        v-for="link in requestedEquipment.links"
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
        <!-- Status Dropdown -->
        <div
            v-show="showStatusDropdown"
            class="fixed z-[9999] bg-white border border-gray-300 rounded-md shadow-lg overflow-y-auto"
            :style="{
                top: statusDropdownPos.top + 'px',
                left: statusDropdownPos.left + 'px',
                width: statusDropdownPos.width + 'px',
                maxHeight: Math.min(240, (windowHeight || 600) - statusDropdownPos.top - 20) + 'px'
            }"
        >
            <div
                @click="selectedStatus = ''; showStatusDropdown = false"
                class="px-3 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100"
            >
                All Statuses
            </div>
            <div
                v-for="status in statuses"
                :key="status"
                @click="selectedStatus = status; showStatusDropdown = false"
                class="px-3 py-2 hover:bg-gray-100 cursor-pointer"
            >
                {{ getStatusDisplayName(status) }}
            </div>
        </div>

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
