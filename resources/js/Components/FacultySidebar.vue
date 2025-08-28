<script setup>
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();

const menuItems = [
    {
        name: 'Dashboard',
        icon: '🏠',
        href: route('faculty.dashboard'),
        active: page.url === '/faculty-dashboard'
    },
    {
        name: 'Student Management',
        icon: '👥',
        href: route('faculty.students.index'),
        active: page.url === '/faculty/students'
    },
    {
        name: 'Equipment Management',
        icon: '/images/equipment.png',
        href: route('faculty.equipment.index'),
        active: page.url === '/faculty/equipment',
        isImage: true
    },
    {
        name: 'Issue Equipment',
        icon: '🤲',
        href: route('faculty.issue-equipment'),
        active: page.url === '/faculty/issue-equipment'
    },
    {
        name: 'Issued Equipment',
        icon: '📋',
        href: route('faculty.issued-equipment'),
        active: page.url === '/faculty/issued-equipment'
    },
    {
        name: 'Requested Equipment',
        icon: '🏷️',
        href: route('faculty.requested-equipment'),
        active: page.url === '/faculty/requested-equipment'
    }
];
</script>

<template>
    <div class="w-64 bg-[#d6efd8] min-h-screen p-4">
        <div class="space-y-2">
            <div v-for="item in menuItems" :key="item.name" class="relative">
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
