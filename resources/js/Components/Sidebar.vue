<script setup>
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();



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
        name: 'Issue Equipment',
        icon: '🤲',
        href: route('admin.issue-equipment'),
        active: page.url === '/admin/issue-equipment',
        showForAdmin: true
    },
    {
        name: 'Issued Equipment',
        icon: '📋',
        href: route('admin.issued-equipment'),
        active: page.url === '/admin/issued-equipment',
        showForAdmin: true
    },
    {
        name: 'Requested Equipment',
        icon: '🏷️',
        href: route('admin.requested-equipment'),
        active: page.url === '/admin/requested-equipment',
        showForAdmin: true
    }
];
</script>

<template>
    <div class="w-64 bg-[#d6efd8] min-h-screen p-4">
        <div class="space-y-2">
            <div v-for="item in menuItems" :key="item.name" class="relative" v-show="!item.showForAdmin || page?.props?.auth?.user?.role === 'admin'">
                <!-- Menu Item -->
                <Link
                    :href="item.href"
                    :class="[
                        'flex items-center w-full px-4 py-3 text-sm font-bold rounded-lg transition-all duration-200 hover:bg-[#1a4a1a] border-l-4',
                        item.active ? 'bg-[#1a4a1a] border-white text-white' : 'text-black border-transparent'
                    ]"
                >
                    <img v-if="item.isImage" :src="item.icon" :alt="item.name" class="w-5 h-5 mr-3" />
                    <span v-else class="text-lg mr-3">{{ item.icon }}</span>
                    <span>{{ item.name }}</span>
                </Link>
            </div>
        </div>
    </div>
</template>
