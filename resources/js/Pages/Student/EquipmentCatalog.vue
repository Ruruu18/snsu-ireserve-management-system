<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import ReservationModal from '@/Components/ReservationModal.vue';
import CartModal from '@/Components/CartModal.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue';
import { useCart } from '@/composables/useCart';
import { debounce } from 'lodash';

const props = defineProps({
    availableEquipment: {
        type: Array,
        required: true
    },
    categories: {
        type: Array,
        default: () => []
    },
    stats: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({})
    }
});

// Search and filter state
const searchTerm = ref(props.filters.search || '');
const selectedCategory = ref(props.filters.category || '');
const sortBy = ref(props.filters.sort_by || 'name');
const sortDirection = ref(props.filters.sort_direction || 'asc');

// Back to top button
const showBackToTop = ref(false);

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

    // Show/hide back to top button
    showBackToTop.value = window.scrollY > 300;
};

// Scroll to top function
const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

// Click outside to close dropdowns
const handleClickOutside = (event) => {
    if (!event.target.closest('.dropdown-container')) {
        closeDropdowns();
    }
};

onMounted(() => {
    // Scroll to top when page loads
    window.scrollTo({ top: 0, behavior: 'smooth' });

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

// Modal state
const showReservationModal = ref(false);
const selectedEquipment = ref(null);

// Cart functionality
const { addToCart, isInCart, getItemQuantity } = useCart();

// Server-side filtering
const updateFilters = debounce(() => {
    router.get(route('student.equipment.catalog'), {
        search: searchTerm.value || undefined,
        category: selectedCategory.value || undefined,
        sort_by: sortBy.value,
        sort_direction: sortDirection.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}, 300);

// Watch for filter changes
watch([searchTerm, selectedCategory, sortBy, sortDirection], () => {
    updateFilters();
});

// Computed properties
const availableCategories = computed(() => {
    return props.categories || [];
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

// Cart methods
const handleAddToCart = (equipment, quantity = 1) => {
    addToCart(equipment, quantity);

    // Show success feedback
    const toast = document.createElement('div');
    toast.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 transform transition-all duration-300';
    toast.textContent = `Added ${equipment.name} to cart!`;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-y-2');
        setTimeout(() => document.body.removeChild(toast), 300);
    }, 2000);
};

const clearFilters = () => {
    searchTerm.value = '';
    selectedCategory.value = '';
    sortBy.value = 'name';
    sortDirection.value = 'asc';
};

const toggleSort = (field) => {
    if (sortBy.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = field;
        sortDirection.value = 'asc';
    }
};
</script>

<template>
    <Head title="Equipment Catalog" />

    <StudentLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold leading-tight text-gray-900">
                        Equipment Catalog
                    </h2>
                    <p class="text-gray-600 mt-1 text-sm sm:text-base">Browse and request available equipment for your needs.</p>
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
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6 mb-4 sm:mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0 sm:space-x-4">
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

                        <!-- Category Filter and Sort Controls -->
                        <div class="flex flex-col space-y-3 lg:flex-row lg:items-center lg:space-y-0 lg:space-x-4">
                            <!-- Category and Clear Filters Row -->
                            <div class="flex flex-col xs:flex-row items-stretch xs:items-center space-y-2 xs:space-y-0 xs:space-x-3 flex-1">
                                <div class="relative w-full xs:flex-1 lg:w-48 lg:flex-none dropdown-container">
                                    <button
                                        ref="categoryButtonRef"
                                        @click="toggleCategoryDropdown"
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 bg-white text-left flex items-center justify-between text-sm"
                                    >
                                        <span class="truncate pr-2">{{ selectedCategory || 'All Categories' }}</span>
                                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </div>

                                <button
                                    v-if="searchTerm || selectedCategory || sortBy !== 'name' || sortDirection !== 'asc'"
                                    @click="clearFilters"
                                    class="inline-flex items-center justify-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 w-full xs:w-auto whitespace-nowrap"
                                >
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Clear
                                </button>
                            </div>

                            <!-- Sort Controls Row -->
                            <div class="flex items-center justify-center lg:justify-start space-x-1 bg-gray-50 rounded-lg p-1">
                                <span class="text-xs font-medium text-gray-600 px-2">Sort:</span>
                                <button
                                    @click="toggleSort('name')"
                                    :class="[
                                        'inline-flex items-center px-2 py-1 text-xs font-medium rounded-md transition-colors flex-1 justify-center lg:flex-none lg:justify-start',
                                        sortBy === 'name'
                                            ? 'bg-blue-600 text-white shadow-sm'
                                            : 'text-gray-700 hover:bg-white hover:shadow-sm'
                                    ]"
                                >
                                    Name
                                    <svg v-if="sortBy === 'name'" :class="['ml-1 w-3 h-3', sortDirection === 'desc' ? 'rotate-180' : '']" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <button
                                    @click="toggleSort('category')"
                                    :class="[
                                        'inline-flex items-center px-2 py-1 text-xs font-medium rounded-md transition-colors flex-1 justify-center lg:flex-none lg:justify-start',
                                        sortBy === 'category'
                                            ? 'bg-blue-600 text-white shadow-sm'
                                            : 'text-gray-700 hover:bg-white hover:shadow-sm'
                                    ]"
                                >
                                    Category
                                    <svg v-if="sortBy === 'category'" :class="['ml-1 w-3 h-3', sortDirection === 'desc' ? 'rotate-180' : '']" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Results Count -->
                    <div class="mt-3 sm:mt-4 flex items-center justify-between text-xs sm:text-sm text-gray-600 px-1">
                        <span class="truncate">
                            Showing {{ filteredEquipment.reduce((total, cat) => total + cat.equipment.length, 0) }} of {{ totalEquipmentCount }} equipment items
                        </span>
                    </div>
                </div>

                <!-- Equipment Grid -->
                <div v-if="filteredEquipment.length > 0" class="space-y-6 sm:space-y-8">
                    <div v-for="category in filteredEquipment" :key="category.category" class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <!-- Category Header -->
                        <div class="px-4 sm:px-6 py-3 sm:py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
                            <h3 class="text-base sm:text-lg font-semibold text-slate-800">{{ category.category }}</h3>
                            <p class="text-xs sm:text-sm text-gray-600 mt-1">{{ category.equipment.length }} items available</p>
                        </div>

                        <!-- Equipment Grid -->
                        <div class="p-3 sm:p-6">
                            <div class="grid grid-cols-1 xs:grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-3 sm:gap-4 lg:gap-6">
                                <div
                                    v-for="equipment in category.equipment"
                                    :key="equipment.id"
                                    class="bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-lg hover:border-blue-300 transition-all duration-200 group flex flex-col relative"
                                >
                                    <!-- Equipment Image -->
                                    <div class="h-32 xs:h-36 sm:h-40 lg:h-48 bg-gray-50 flex items-center justify-center flex-shrink-0 relative">
                                        <img
                                            v-if="equipment.image"
                                            :src="getEquipmentImageUrl(equipment.image)"
                                            :alt="equipment.name"
                                            class="max-h-full max-w-full object-contain p-2 sm:p-3 lg:p-4"
                                        />
                                        <div v-else class="text-gray-400">
                                            <svg class="w-8 h-8 xs:w-10 xs:h-10 sm:w-12 sm:h-12 lg:w-16 lg:h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>

                                        <!-- Cart Status Indicator -->
                                        <div v-if="isInCart(equipment.id)" class="absolute top-2 right-2 bg-green-500 text-white rounded-full p-1 shadow-sm">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- Equipment Info -->
                                    <div class="p-3 sm:p-4 flex-1 flex flex-col">
                                        <h4 class="text-sm sm:text-base lg:text-lg font-semibold text-gray-900 group-hover:text-blue-600 transition-colors duration-200 line-clamp-2 mb-1">
                                            {{ equipment.name }}
                                        </h4>
                                        <p v-if="equipment.description" class="text-xs sm:text-sm text-gray-600 line-clamp-2 flex-1 mb-2">
                                            {{ equipment.description }}
                                        </p>

                                        <!-- Equipment Details (collapsible on mobile) -->
                                        <div class="space-y-1 mb-3">
                                            <p v-if="equipment.location" class="text-xs text-gray-500 truncate">
                                                <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                {{ equipment.location }}
                                            </p>
                                            <p v-if="equipment.serial_number" class="text-xs text-gray-500 truncate hidden sm:block">
                                                <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2m0 0V3a1 1 0 011 1v1a1 1 0 01-1 1H8a1 1 0 01-1-1V4a1 1 0 011-1v0z" />
                                                </svg>
                                                {{ equipment.serial_number }}
                                            </p>
                                            <p v-if="equipment.available_quantity !== undefined" class="text-xs font-medium" :class="equipment.available_quantity > 0 ? 'text-green-600' : 'text-red-600'">
                                                <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                </svg>
                                                <template v-if="equipment.available_quantity > 0">
                                                    {{ equipment.available_quantity }} available
                                                    <span v-if="equipment.total_quantity > equipment.available_quantity" class="text-gray-500">
                                                        ({{ equipment.total_quantity }} total)
                                                    </span>
                                                </template>
                                                <template v-else>
                                                    Out of stock
                                                    <span v-if="equipment.currently_issued > 0" class="text-gray-500">
                                                        ({{ equipment.currently_issued }} in use)
                                                    </span>
                                                </template>
                                            </p>
                                        </div>

                                        <!-- Action Buttons -->
                                        <div class="space-y-2">
                                            <!-- Add to Cart Button -->
                                            <button
                                                @click="handleAddToCart(equipment)"
                                                :disabled="isInCart(equipment.id) || equipment.available_quantity <= 0"
                                                class="w-full inline-flex items-center justify-center px-3 py-2 text-xs sm:text-sm font-medium rounded-lg transition ease-in-out duration-150 shadow-sm"
                                                :class="isInCart(equipment.id)
                                                    ? 'text-green-700 bg-green-50 border border-green-200 cursor-not-allowed'
                                                    : equipment.available_quantity <= 0
                                                    ? 'text-gray-500 bg-gray-100 border border-gray-200 cursor-not-allowed'
                                                    : 'text-white bg-blue-600 hover:bg-blue-700 border border-transparent hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500'"
                                            >
                                                <template v-if="isInCart(equipment.id)">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    <span class="hidden xs:inline">In Cart</span>
                                                    <span class="xs:hidden">✓</span>
                                                    <span class="ml-1 hidden sm:inline">({{ getItemQuantity(equipment.id) }})</span>
                                                </template>
                                                <template v-else-if="equipment.available_quantity <= 0">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18 12M6 6l12 12" />
                                                    </svg>
                                                    <span class="hidden xs:inline">Out of Stock</span>
                                                    <span class="xs:hidden">N/A</span>
                                                </template>
                                                <template v-else>
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.1 5H17M9 19.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM20.5 19.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                                                    </svg>
                                                    <span class="hidden xs:inline">Add to Cart</span>
                                                    <span class="xs:hidden">Add</span>
                                                </template>
                                            </button>

                                            <!-- Quick Request Button (Individual) -->
                                            <button
                                                @click="requestEquipment(equipment)"
                                                :disabled="equipment.available_quantity <= 0"
                                                class="w-full inline-flex items-center justify-center px-3 py-1.5 border text-xs sm:text-sm font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150"
                                                :class="equipment.available_quantity <= 0
                                                    ? 'border-gray-200 text-gray-400 bg-gray-50 cursor-not-allowed'
                                                    : 'border-gray-300 text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400'"
                                            >
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                </svg>
                                                <span class="hidden xs:inline">Quick Request</span>
                                                <span class="xs:hidden">Request</span>
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

        <!-- Cart Modal -->
        <CartModal />
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
                v-for="category in availableCategories"
                :key="category"
                @click="selectedCategory = category; showCategoryDropdown = false"
                class="px-3 py-2 hover:bg-gray-100 cursor-pointer"
            >
                {{ category }}
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Custom responsive breakpoints */
@media (min-width: 475px) {
    .xs\:grid-cols-2 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .xs\:flex-row {
        flex-direction: row;
    }
    .xs\:items-center {
        align-items: center;
    }
    .xs\:space-y-0 > :not([hidden]) ~ :not([hidden]) {
        margin-top: 0;
    }
    .xs\:space-x-3 > :not([hidden]) ~ :not([hidden]) {
        margin-left: 0.75rem;
    }
    .xs\:flex-1 {
        flex: 1 1 0%;
    }
    .xs\:w-auto {
        width: auto;
    }
    .xs\:h-36 {
        height: 9rem;
    }
    .xs\:w-10 {
        width: 2.5rem;
    }
    .xs\:h-10 {
        height: 2.5rem;
    }
    .xs\:inline {
        display: inline;
    }
    .xs\:hidden {
        display: none;
    }
}

/* Mobile-first responsive grid improvements */
@media (max-width: 474px) {
    .mobile-stack {
        flex-direction: column;
        gap: 0.5rem;
    }
}

/* Enhanced hover effects for larger screens */
@media (hover: hover) {
    .group:hover .group-hover\:scale-105 {
        transform: scale(1.05);
    }
}

/* Touch-friendly sizing for mobile */
@media (max-width: 640px) {
    .touch-target {
        min-height: 44px;
        min-width: 44px;
    }
}
</style>
