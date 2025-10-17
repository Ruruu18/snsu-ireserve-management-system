<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref, watch, onMounted } from 'vue';

const page = usePage();

defineProps({
    showMobileMenu: {
        type: Boolean,
        default: false
    }
});

defineEmits(['closeMobileMenu']);

// Get stats from page props if available
const stats = computed(() => {
    return page.props.stats || {
        total_reservations: 0,
        pending_reservations: 0,
        active_reservations: 0,
        completed_reservations: 0,
    };
});

const menuItems = [
    {
        name: 'Dashboard',
        icon: '🏠',
        href: route('student.dashboard'),
        active: route().current('student.dashboard'),
        description: 'View your dashboard overview'
    },
    {
        name: 'Equipments',
        icon: '/images/equipment.png',
        href: route('student.equipment.catalog'),
        active: route().current('student.equipment.catalog'),
        description: 'Browse available equipment',
        isImage: true
    },
    {
        name: 'My Cart',
        icon: '🛒',
        href: route('student.cart.index'),
        active: route().current('student.cart.index'),
        description: 'View and manage your cart'
    },
    {
        name: 'My Issued Equipment',
        icon: '🤲',
        href: route('student.equipment.issued'),
        active: route().current('student.equipment.issued'),
        description: 'View equipment currently issued to you'
    },
    {
        name: 'Requested Equipment',
        icon: '🏷️',
        href: route('student.equipment.requested'),
        active: route().current('student.equipment.requested'),
        description: 'Track your equipment requests'
    }
];

// Sidebar scroll container reference
const sidebarScrollContainer = ref(null);

// Watch for route changes and scroll sidebar to top
watch(() => page.url, () => {
    if (sidebarScrollContainer.value) {
        sidebarScrollContainer.value.scrollTo({ top: 0, behavior: 'smooth' });
    }
});

// Also scroll main content to top on route change
watch(() => page.url, () => {
    // Use nextTick to ensure DOM is updated
    setTimeout(() => {
        const mainContent = document.querySelector('main');
        if (mainContent) {
            mainContent.scrollTo({ top: 0, behavior: 'smooth' });
        }
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }, 100);
});
</script>

<template>
    <!-- Desktop Sidebar -->
    <div class="hidden md:flex w-64 bg-[#d6efd8] h-screen">
        <!-- Menu Items -->
        <div ref="sidebarScrollContainer" class="space-y-3 w-full p-4 overflow-y-auto">
            <div v-for="item in menuItems" :key="item.name" class="relative">
                <Link
                    :href="item.href"
                    :class="[
                        'flex items-center w-full px-4 py-4 text-sm font-bold rounded-lg transition-all duration-200 hover:bg-[#1a4a1a] group',
                        item.active ? 'bg-[#1a4a1a] border-l-4 border-white text-white' : 'text-black hover:text-white'
                    ]"
                >
                    <img v-if="item.isImage" :src="item.icon" :alt="item.name" class="w-6 h-6 mr-3 group-hover:scale-110 transition-transform duration-200 flex-shrink-0" />
                    <span v-else class="text-xl mr-3 group-hover:scale-110 transition-transform duration-200 flex-shrink-0">{{ item.icon }}</span>
                    <div class="flex-1 min-w-0">
                        <span class="block truncate">{{ item.name }}</span>
                        <span class="text-xs font-normal opacity-75 group-hover:opacity-100 transition-opacity duration-200 block truncate">
                            {{ item.description }}
                        </span>
                    </div>
                </Link>
            </div>

            <!-- Quick Stats Section -->
            <div class="mt-8 p-4 bg-white rounded-lg border border-gray-200">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Quick Stats</h3>
                <div class="space-y-2">
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-600">Active:</span>
                        <span class="font-medium text-green-600">{{ stats.active_reservations }}</span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-600">Pending:</span>
                        <span class="font-medium text-yellow-600">{{ stats.pending_reservations }}</span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-600">Completed:</span>
                        <span class="font-medium text-blue-600">{{ stats.completed_reservations }}</span>
                    </div>
                </div>
            </div>

            <!-- Help Section -->
            <div class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <div class="flex items-center space-x-2 mb-2">
                    <span class="text-blue-600">❓</span>
                    <span class="text-sm font-medium text-blue-800">Need Help?</span>
                </div>
                <p class="text-xs text-blue-600">
                    Contact lab staff for assistance with equipment reservations.
                </p>
            </div>
        </div>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div v-show="showMobileMenu" class="md:hidden fixed inset-0 z-40 flex">
        <!-- Overlay backdrop -->
        <div
            class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
            @click="$emit('closeMobileMenu')"
        ></div>

        <!-- Sidebar panel -->
        <div class="relative flex flex-col w-80 max-w-[85vw] bg-[#d6efd8] min-h-screen p-4">
            <!-- Close button -->
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-[#2F6C2F]">Student Menu</h2>
                <button
                    @click="$emit('closeMobileMenu')"
                    class="p-2 rounded-md text-[#2F6C2F] hover:bg-[#c8e6ca] focus:outline-none focus:ring-2 focus:ring-[#2F6C2F]"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Menu Items -->
            <div class="space-y-3 flex-1 overflow-y-auto">
                <div v-for="item in menuItems" :key="item.name" class="relative">
                    <Link
                        :href="item.href"
                        @click="$emit('closeMobileMenu')"
                        :class="[
                            'flex items-center w-full px-4 py-4 text-sm font-bold rounded-lg transition-all duration-200 hover:bg-[#1a4a1a] group',
                            item.active ? 'bg-[#1a4a1a] border-l-4 border-white text-white' : 'text-black hover:text-white'
                        ]"
                    >
                        <img v-if="item.isImage" :src="item.icon" :alt="item.name" class="w-6 h-6 mr-3 group-hover:scale-110 transition-transform duration-200 flex-shrink-0" />
                        <span v-else class="text-xl mr-3 group-hover:scale-110 transition-transform duration-200 flex-shrink-0">{{ item.icon }}</span>
                        <div class="flex-1 min-w-0">
                            <span class="block">{{ item.name }}</span>
                            <span class="text-xs font-normal opacity-75 group-hover:opacity-100 transition-opacity duration-200 block">
                                {{ item.description }}
                            </span>
                        </div>
                    </Link>
                </div>

                <!-- Quick Stats Section (Mobile) -->
                <div class="mt-6 p-4 bg-white rounded-lg border border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Quick Stats</h3>
                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div class="text-xs">
                            <div class="font-medium text-green-600 text-lg">{{ stats.active_reservations }}</div>
                            <div class="text-gray-600">Active</div>
                        </div>
                        <div class="text-xs">
                            <div class="font-medium text-yellow-600 text-lg">{{ stats.pending_reservations }}</div>
                            <div class="text-gray-600">Pending</div>
                        </div>
                        <div class="text-xs">
                            <div class="font-medium text-blue-600 text-lg">{{ stats.completed_reservations }}</div>
                            <div class="text-gray-600">Completed</div>
                        </div>
                    </div>
                </div>

                <!-- Help Section (Mobile) -->
                <div class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <div class="flex items-center space-x-2 mb-2">
                        <span class="text-blue-600">❓</span>
                        <span class="text-sm font-medium text-blue-800">Need Help?</span>
                    </div>
                    <p class="text-xs text-blue-600">
                        Contact lab staff for assistance with equipment reservations.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
