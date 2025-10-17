<script setup>
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();

defineProps({
    showMobileMenu: {
        type: Boolean,
        default: false
    }
});

defineEmits(['closeMobileMenu']);



const menuItems = [
    {
        name: 'Dashboard',
        icon: '🏠',
        href: route('dashboard'),
        active: page.url === '/admin-dashboard'
    },
    {
        name: 'Student Management',
        icon: '🖥️',
        href: route('admin.students.index'),
        active: page.url === '/admin/students',
        showForAdmin: true
    },
    {
        name: 'Department Management',
        icon: '🖥️',
        href: route('admin.departments.index'),
        active: page.url === '/admin/departments',
        showForAdmin: true
    },
    {
        name: 'Equipment Management',
        icon: '/images/equipment.png',
        href: route('admin.equipment.index'),
        active: page.url === '/admin/equipment',
        showForAdmin: true,
        isImage: true
    },
    {
        name: 'Manage All Users',
        icon: '👥',
        href: route('admin.users.index'),
        active: page.url === '/admin/users',
        showForAdmin: true
    },
    {
        name: 'Reservation Management',
        icon: '📝',
        href: route('admin.reservations.index'),
        active: page.url === '/admin/reservations',
        showForAdmin: true
    }
];
</script>

<template>
    <!-- Desktop Sidebar -->
    <div class="hidden md:flex w-64 bg-[#d6efd8] min-h-screen p-4">
        <div class="space-y-2 w-full">
            <div v-for="item in menuItems" :key="item.name" class="relative" v-show="!item.showForAdmin || page?.props?.auth?.user?.role === 'admin'">
                <!-- Menu Item -->
                <Link
                    :href="item.href"
                    :class="[
                        'flex items-center w-full px-4 py-3 text-sm font-bold rounded-lg transition-all duration-200 hover:bg-[#1a4a1a] border-l-4',
                        item.active ? 'bg-[#1a4a1a] border-white text-white' : 'text-black border-transparent'
                    ]"
                >
                    <img v-if="item.isImage" :src="item.icon" :alt="item.name" class="w-5 h-5 mr-3 flex-shrink-0" />
                    <span v-else class="text-lg mr-3 flex-shrink-0">{{ item.icon }}</span>
                    <span class="truncate">{{ item.name }}</span>
                </Link>
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
        <div class="relative flex flex-col w-64 bg-[#d6efd8] min-h-screen p-4">
            <!-- Close button -->
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-[#2F6C2F]">Menu</h2>
                <button
                    @click="$emit('closeMobileMenu')"
                    class="p-2 rounded-md text-[#2F6C2F] hover:bg-[#c8e6ca] focus:outline-none focus:ring-2 focus:ring-[#2F6C2F]"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="space-y-2 flex-1">
                <div v-for="item in menuItems" :key="item.name" class="relative" v-show="!item.showForAdmin || page?.props?.auth?.user?.role === 'admin'">
                    <!-- Menu Item -->
                    <Link
                        :href="item.href"
                        @click="$emit('closeMobileMenu')"
                        :class="[
                            'flex items-center w-full px-4 py-3 text-sm font-bold rounded-lg transition-all duration-200 hover:bg-[#1a4a1a] border-l-4',
                            item.active ? 'bg-[#1a4a1a] border-white text-white' : 'text-black border-transparent'
                        ]"
                    >
                        <img v-if="item.isImage" :src="item.icon" :alt="item.name" class="w-5 h-5 mr-3 flex-shrink-0" />
                        <span v-else class="text-lg mr-3 flex-shrink-0">{{ item.icon }}</span>
                        <span class="truncate">{{ item.name }}</span>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
