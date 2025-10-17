<script setup>
import FacultyLayout from '@/Layouts/FacultyLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { computed, ref, onMounted } from 'vue';
import axios from 'axios';

// Reactive data for dashboard stats
const stats = ref([
    { name: 'Equipments', value: '0', icon: '📅', change: '', trend: 'up', color: 'bg-gradient-to-br from-blue-600 to-blue-700 border-blue-800' },
    { name: 'Requested Equipments', value: '0', icon: '✅', change: '', trend: 'up', color: 'bg-gradient-to-br from-amber-600 to-amber-700 border-amber-800' },
    { name: 'Issued Equipments', value: '0', icon: '🎉', change: '', trend: 'up', color: 'bg-gradient-to-br from-emerald-600 to-emerald-700 border-emerald-800' },
    { name: 'Students', value: '0', icon: '📊', change: '', trend: 'up', color: 'bg-gradient-to-br from-slate-600 to-slate-700 border-slate-800' },
]);

const recentReservations = ref([]);
const loading = ref(true);

// No modal states needed for faculty - only reservation management

// Fetch dashboard data from API
const fetchDashboardData = async () => {
    try {
        loading.value = true;
        const response = await axios.get('/faculty-dashboard/stats');
        const data = response.data;

        // Update stats with real data
        stats.value[0].value = data.stats.equipments.toString();
        stats.value[1].value = data.stats.requested_equipments.toString();
        stats.value[2].value = data.stats.issued_equipments.toString();
        stats.value[3].value = data.stats.students.toString();

        // Update recent reservations
        recentReservations.value = data.recent_reservations;
    } catch (error) {
        console.error('Error fetching dashboard data:', error);
    } finally {
        loading.value = false;
    }
};

// Fetch data when component mounts
onMounted(() => {
    fetchDashboardData();

    // Refresh data every 30 seconds for real-time updates
    setInterval(fetchDashboardData, 30000);
});

// Faculty Quick actions for this dashboard - essential tools only
const quickActions = computed(() => [
    { name: 'QR Scanner', description: 'Scan QR codes for quick processing', icon: '📱', route: 'faculty.qr-scanner' },
]);

const getStatusColor = (status) => {
    switch (status) {
        case 'approved':
            return 'bg-green-100 text-green-800';
        case 'issued':
            return 'bg-blue-100 text-blue-800';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'completed':
            return 'bg-gray-100 text-gray-800';
        case 'cancelled':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const handleQuickAction = (action) => {
    // Handle quick actions for faculty reservation management
    if (action.route) {
        router.visit(route(action.route));
    }
};

// Navigate to faculty reservations page when clicking on a reservation
const viewReservationDetails = (reservation) => {
    // Navigate to faculty reservations page where this reservation can be managed
    router.visit(route('faculty.reservations.index'));
};
</script>

<template>
    <Head title="Faculty Dashboard" />

    <FacultyLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-900">
                        Faculty Dashboard
                    </h2>
                    <p class="text-gray-600 mt-1">Welcome back! Here's what's happening.</p>
                </div>
            </div>
        </template>

        <div class="h-full flex flex-col overflow-hidden">
            <div class="px-6 lg:px-8 py-6 flex-1 overflow-y-auto">
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
                    <div
                        v-for="stat in stats"
                        :key="stat.name"
                        :class="['overflow-hidden shadow-sm rounded-xl border hover:shadow-md transition duration-200', stat.color]"
                    >
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <span class="text-2xl text-white">{{ stat.icon }}</span>
                                </div>
                                <div class="ml-4 w-full">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-medium text-white truncate">{{ stat.name }}</p>
                                        <span v-if="stat.change" class="text-yellow-200 text-xs font-medium">{{ stat.change }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <p v-if="!loading" class="text-2xl font-bold text-white">{{ stat.value }}</p>
                                        <div v-else class="flex items-center">
                                            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-white"></div>
                                            <span class="ml-2 text-lg text-white">Loading...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Recent Reservations -->
                    <div class="lg:col-span-2">
                        <div class="bg-white border-gray-200 shadow-lg rounded-xl border hover:shadow-xl transition-all duration-300 h-[400px] flex flex-col">
                            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-slate-50 to-gray-50">
                                <h3 class="text-lg font-semibold text-slate-800">Recent Reservations</h3>
                            </div>
                            <div class="p-6 flex-1 overflow-y-auto">
                                <div class="space-y-4">
                                    <div v-if="loading" class="text-center py-8 text-gray-500">
                                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                                        <p class="mt-2">Loading reservations...</p>
                                    </div>
                                    <div v-else-if="recentReservations.length > 0">
                                        <div
                                            v-for="reservation in recentReservations"
                                            :key="reservation.id"
                                            @click="viewReservationDetails(reservation)"
                                            class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-blue-50 hover:border-blue-200 hover:shadow-lg transition-all duration-200 border border-gray-200 cursor-pointer group"
                                        >
                                            <div class="flex-1">
                                                <h4 class="font-medium text-gray-900 group-hover:text-blue-700 transition-colors duration-200">{{ reservation.name }}</h4>
                                                <p class="text-sm text-gray-600">by {{ reservation.user }}</p>
                                                <p class="text-sm text-gray-600">{{ reservation.date }} at {{ reservation.time }}</p>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <span
                                                    :class="getStatusColor(reservation.status)"
                                                    class="px-3 py-1 rounded-full text-xs font-medium capitalize"
                                                >
                                                    {{ reservation.status }}
                                                </span>
                                                <!-- Click indicator -->
                                                <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-500 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="text-center py-8 text-gray-500">
                                        <p>No recent reservations found.</p>
                                    </div>
                                </div>
                                <div class="mt-6 text-center">
                                    <Link :href="route('faculty.reservations.index')" class="text-slate-600 hover:text-slate-800 font-medium transition duration-200 hover:underline">
                                        View All Reservations
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div>
                        <div class="bg-white border-gray-200 shadow-lg rounded-xl border hover:shadow-xl transition-all duration-300 h-[400px] flex flex-col">
                            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
                                <h3 class="text-lg font-semibold text-slate-800">Quick Actions</h3>
                            </div>
                            <div class="p-4 flex-1">
                                <div class="space-y-2">
                                    <button
                                        v-for="action in quickActions"
                                        :key="action.name"
                                        @click="handleQuickAction(action)"
                                        class="w-full text-left p-3 rounded-lg border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-all duration-200 group bg-white hover:shadow-sm"
                                    >
                                        <div class="flex items-center">
                                            <img v-if="action.isImage" :src="action.icon" :alt="action.name" class="w-6 h-6 mr-3" />
                                            <span v-else class="text-xl mr-3">{{ action.icon }}</span>
                                            <div>
                                                <h4 class="font-medium text-gray-900 group-hover:text-blue-700">{{ action.name }}</h4>
                                                <p class="text-sm text-gray-600">{{ action.description }}</p>
                                            </div>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </FacultyLayout>
</template>
