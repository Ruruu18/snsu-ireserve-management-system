<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

// Sample data - in a real app, this would come from props or API
const stats = ref([
    { name: 'Total Reservations', value: '12', icon: '📅', change: '+2', trend: 'up' },
    { name: 'Active Bookings', value: '3', icon: '✅', change: '+1', trend: 'up' },
    { name: 'Completed', value: '9', icon: '🎉', change: '+1', trend: 'up' },
    { name: 'This Month', value: '5', icon: '📊', change: '+3', trend: 'up' },
]);

const recentReservations = ref([
    { id: 1, name: 'Conference Room A', date: '2024-01-15', time: '10:00 AM', status: 'confirmed' },
    { id: 2, name: 'Meeting Room B', date: '2024-01-16', time: '2:00 PM', status: 'pending' },
    { id: 3, name: 'Event Hall', date: '2024-01-18', time: '6:00 PM', status: 'confirmed' },
]);

const quickActions = ref([
    { name: 'New Reservation', description: 'Book a new space or service', icon: '➕', action: 'create' },
    { name: 'My Bookings', description: 'View and manage your reservations', icon: '📋', action: 'view' },
    { name: 'Calendar', description: 'See your schedule overview', icon: '📅', action: 'calendar' },
    { name: 'Profile', description: 'Update your account settings', icon: '👤', action: 'profile' },
]);

const getStatusColor = (status) => {
    switch (status) {
        case 'confirmed':
            return 'bg-green-100 text-green-800';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'cancelled':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};
</script>

<template>
    <Head title="Dashboard - IReserve" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-900">
                        Dashboard
                    </h2>
                    <p class="text-gray-600 mt-1">Welcome back! Here's what's happening.</p>
                </div>
                <div class="hidden sm:block">
                    <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-200">
                        + New Reservation
                    </button>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                    <div 
                        v-for="stat in stats" 
                        :key="stat.name"
                        class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200 hover:shadow-md transition duration-200"
                    >
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <span class="text-2xl">{{ stat.icon }}</span>
                                </div>
                                <div class="ml-4 w-full">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-medium text-gray-600 truncate">{{ stat.name }}</p>
                                        <span class="text-green-600 text-xs font-medium">{{ stat.change }}</span>
                                    </div>
                                    <p class="text-2xl font-bold text-gray-900">{{ stat.value }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Recent Reservations -->
                    <div class="lg:col-span-2">
                        <div class="bg-white shadow-sm rounded-xl border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Recent Reservations</h3>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div 
                                        v-for="reservation in recentReservations" 
                                        :key="reservation.id"
                                        class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200"
                                    >
                                        <div class="flex-1">
                                            <h4 class="font-medium text-gray-900">{{ reservation.name }}</h4>
                                            <p class="text-sm text-gray-600">{{ reservation.date }} at {{ reservation.time }}</p>
                                        </div>
                                        <span 
                                            :class="getStatusColor(reservation.status)"
                                            class="px-3 py-1 rounded-full text-xs font-medium capitalize"
                                        >
                                            {{ reservation.status }}
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-6 text-center">
                                    <button class="text-indigo-600 hover:text-indigo-500 font-medium transition duration-200">
                                        View All Reservations
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div>
                        <div class="bg-white shadow-sm rounded-xl border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                            </div>
                            <div class="p-6">
                                <div class="space-y-3">
                                    <button 
                                        v-for="action in quickActions" 
                                        :key="action.name"
                                        class="w-full text-left p-4 rounded-lg border border-gray-200 hover:border-indigo-300 hover:bg-indigo-50 transition duration-200 group"
                                    >
                                        <div class="flex items-center">
                                            <span class="text-xl mr-3">{{ action.icon }}</span>
                                            <div>
                                                <h4 class="font-medium text-gray-900 group-hover:text-indigo-700">{{ action.name }}</h4>
                                                <p class="text-sm text-gray-600">{{ action.description }}</p>
                                            </div>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Welcome Message -->
                        <div class="mt-6 bg-gradient-to-br from-indigo-500 to-purple-600 shadow-sm rounded-xl text-white">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold mb-2">Welcome to IReserve! 🎉</h3>
                                <p class="text-indigo-100 text-sm mb-4">
                                    Start by creating your first reservation or explore our features.
                                </p>
                                <button class="bg-white text-indigo-600 px-4 py-2 rounded-lg font-medium hover:bg-gray-100 transition duration-200">
                                    Get Started
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
