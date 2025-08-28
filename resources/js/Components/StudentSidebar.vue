<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

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
</script>

<template>
    <div class="w-64 bg-[#d6efd8] min-h-screen p-4">
        <!-- Menu Items -->
        <div class="space-y-3">
            <div v-for="item in menuItems" :key="item.name" class="relative">
                <Link
                    :href="item.href"
                    :class="[
                        'flex items-center w-full px-4 py-4 text-sm font-bold rounded-lg transition-all duration-200 hover:bg-[#1a4a1a] group',
                        item.active ? 'bg-[#1a4a1a] border-l-4 border-white text-white' : 'text-black hover:text-white'
                    ]"
                >
                    <img v-if="item.isImage" :src="item.icon" :alt="item.name" class="w-6 h-6 mr-3 group-hover:scale-110 transition-transform duration-200" />
                    <span v-else class="text-xl mr-3 group-hover:scale-110 transition-transform duration-200">{{ item.icon }}</span>
                    <div class="flex-1">
                        <span class="block">{{ item.name }}</span>
                        <span class="text-xs font-normal opacity-75 group-hover:opacity-100 transition-opacity duration-200">
                            {{ item.description }}
                        </span>
                    </div>
                </Link>
            </div>
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
</template>
