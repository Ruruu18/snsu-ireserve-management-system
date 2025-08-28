<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import ReservationModal from '@/Components/ReservationModal.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

// Props for dynamic data
const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            total_reservations: 0,
            pending_reservations: 0,
            active_reservations: 0,
            completed_reservations: 0,
        })
    },
    recentReservations: {
        type: Array,
        default: () => []
    },
    upcomingReservations: {
        type: Array,
        default: () => []
    },
    availableEquipment: {
        type: Array,
        default: () => []
    }
});

// Reservation modal state
const showReservationModal = ref(false);
const selectedEquipment = ref(null);

// Handle reservation creation success
const handleReservationCreated = () => {
    showReservationModal.value = false;
    selectedEquipment.value = null;
    // Refresh the page to show updated data
    router.reload();
};

// Handle reservation cancellation
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

// Navigate to reservations page
const viewAllReservations = () => {
    router.visit(route('reservations.index'));
};

// Navigate to reservations page to view details when clicking on a reservation
const viewReservationDetails = (reservation) => {
    // Navigate to student reservations page where they can see all their reservations
    router.visit(route('reservations.index'));
};

// Navigate to create reservation page
const createNewReservation = () => {
    showReservationModal.value = true;
    selectedEquipment.value = null;
};

// Quick actions for students
const quickActions = computed(() => [
    {
        name: 'New Reservation',
        description: 'Book equipment for your project',
        icon: '➕',
        action: 'create',
        onClick: createNewReservation,
        color: 'bg-gradient-to-br from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800'
    },
    {
        name: 'My Reservations',
        description: 'View and manage your bookings',
        icon: '📋',
        action: 'view',
        onClick: viewAllReservations,
        color: 'bg-gradient-to-br from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800'
    },
    {
        name: 'Equipment Catalog',
        description: 'Browse available equipment',
        icon: '🔍',
        action: 'catalog',
        onClick: () => {
            // Scroll to equipment catalog section
            document.getElementById('equipment-catalog')?.scrollIntoView({ behavior: 'smooth' });
        },
        color: 'bg-gradient-to-br from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800'
    },
    {
        name: 'Help & Support',
        description: 'Get assistance with reservations',
        icon: '❓',
        action: 'help',
        onClick: () => {
            alert('For help with reservations, please contact the administrator or visit the office during business hours.');
        },
        color: 'bg-gradient-to-br from-amber-600 to-amber-700 hover:from-amber-700 hover:to-amber-800'
    },
]);

// Status colors for reservations
const getStatusColor = (status) => {
    switch (status) {
        case 'approved':
            return 'bg-green-100 text-green-800 border-green-200';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800 border-yellow-200';
        case 'rejected':
            return 'bg-red-100 text-red-800 border-red-200';
        case 'completed':
            return 'bg-blue-100 text-blue-800 border-blue-200';
        case 'cancelled':
            return 'bg-gray-100 text-gray-800 border-gray-200';
        default:
            return 'bg-gray-100 text-gray-800 border-gray-200';
    }
};

// Status icons for reservations
const getStatusIcon = (status) => {
    switch (status) {
        case 'approved':
            return '✅';
        case 'pending':
            return '⏳';
        case 'rejected':
            return '❌';
        case 'completed':
            return '🎉';
        case 'cancelled':
            return '🚫';
        default:
            return '❓';
    }
};

// Format time for display
const formatTime = (time) => {
    return time.replace(':', '');
};

// Get equipment image URL
const getEquipmentImageUrl = (imagePath) => {
    return imagePath ? `/storage/${imagePath}` : '/images/equipment.png';
};

// Handle equipment reservation
const reserveEquipment = (equipment) => {
    console.log('Reserve equipment clicked:', equipment);
    selectedEquipment.value = equipment;
    showReservationModal.value = true;
    console.log('Modal should show:', showReservationModal.value);
};
</script>

<template>
    <Head title="Student Dashboard" />

    <StudentLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-900">
                        Student Dashboard
                    </h2>
                    <p class="text-gray-600 mt-1">Manage your equipment reservations and track your bookings.</p>
                </div>
                <div class="flex space-x-3">
                    <button
                        @click="createNewReservation"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        New Reservation
                    </button>
                </div>
            </div>
        </template>

        <div class="h-full flex flex-col overflow-hidden">
            <div class="px-6 lg:px-8 py-6 flex-1 overflow-y-auto">
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
                    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200 hover:shadow-md transition duration-200">
                        <div class="p-6 bg-gradient-to-br from-blue-600 to-blue-700">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <span class="text-2xl text-white">📊</span>
                                </div>
                                <div class="ml-4 w-full">
                                    <p class="text-sm font-medium text-blue-100 truncate">Total Reservations</p>
                                    <p class="text-2xl font-bold text-white">{{ props.stats.total_reservations }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200 hover:shadow-md transition duration-200">
                        <div class="p-6 bg-gradient-to-br from-yellow-600 to-yellow-700">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <span class="text-2xl text-white">⏳</span>
                                </div>
                                <div class="ml-4 w-full">
                                    <p class="text-sm font-medium text-yellow-100 truncate">Pending</p>
                                    <p class="text-2xl font-bold text-white">{{ props.stats.pending_reservations }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200 hover:shadow-md transition duration-200">
                        <div class="p-6 bg-gradient-to-br from-green-600 to-green-700">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <span class="text-2xl text-white">✅</span>
                                </div>
                                <div class="ml-4 w-full">
                                    <p class="text-sm font-medium text-green-100 truncate">Active</p>
                                    <p class="text-2xl font-bold text-white">{{ props.stats.active_reservations }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200 hover:shadow-md transition duration-200">
                        <div class="p-6 bg-gradient-to-br from-purple-600 to-purple-700">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <span class="text-2xl text-white">🎉</span>
                                </div>
                                <div class="ml-4 w-full">
                                    <p class="text-sm font-medium text-purple-100 truncate">Completed</p>
                                    <p class="text-2xl font-bold text-white">{{ props.stats.completed_reservations }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    <!-- Recent Reservations -->
                    <div class="lg:col-span-2">
                        <div class="bg-white border-gray-200 shadow-lg rounded-xl border hover:shadow-xl transition-all duration-300 h-[400px] flex flex-col">
                            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-slate-50 to-gray-50">
                                <h3 class="text-lg font-semibold text-slate-800">Recent Reservations</h3>
                            </div>
                            <div class="p-6 flex-1 overflow-y-auto">
                                <div class="space-y-3">
                                    <div v-if="props.recentReservations.length > 0">
                                        <div
                                            v-for="reservation in props.recentReservations"
                                            :key="reservation.id"
                                            @click="viewReservationDetails(reservation)"
                                            class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-blue-50 hover:border-blue-200 hover:shadow-lg transition-all duration-200 border border-gray-200 cursor-pointer group"
                                        >
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-3">
                                                    <span class="text-lg">{{ getStatusIcon(reservation.status) }}</span>
                                                    <div>
                                                        <h4 class="font-medium text-gray-900 group-hover:text-blue-700 text-sm transition-colors duration-200">{{ reservation.equipment_name }}</h4>
                                                        <p class="text-xs text-gray-600">{{ reservation.date }} • {{ reservation.start_time }} - {{ reservation.end_time }}</p>
                                                        <p v-if="reservation.purpose" class="text-xs text-gray-500 mt-1 truncate">{{ reservation.purpose }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <span
                                                    :class="getStatusColor(reservation.status)"
                                                    class="px-2 py-1 rounded-full text-xs font-medium capitalize border"
                                                >
                                                    {{ reservation.status }}
                                                </span>
                                                <button
                                                    v-if="reservation.can_cancel"
                                                    @click.stop="cancelReservation(reservation.id)"
                                                    class="text-red-600 hover:text-red-800 text-xs font-medium transition duration-200 hover:underline"
                                                >
                                                    Cancel
                                                </button>
                                                <!-- Click indicator -->
                                                <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-500 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="text-center py-8 text-gray-500">
                                        <p>No reservations found. Start by making your first reservation!</p>
                                    </div>
                                </div>
                            </div>
                            <div v-if="props.recentReservations.length > 4" class="px-6 pb-4 text-center border-t border-gray-200 pt-4">
                                <button
                                    @click="viewAllReservations"
                                    class="text-slate-600 hover:text-slate-800 font-medium transition duration-200 hover:underline text-sm"
                                >
                                    View All Reservations
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div>
                        <div class="bg-white border-gray-200 shadow-lg rounded-xl border hover:shadow-xl transition-all duration-300 h-[400px] flex flex-col">
                            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
                                <h3 class="text-lg font-semibold text-slate-800">Quick Actions</h3>
                            </div>
                            <div class="p-6 flex-1 overflow-y-auto">
                                <div class="space-y-3">
                                    <button
                                        v-for="action in quickActions"
                                        :key="action.name"
                                        @click="action.onClick"
                                        class="block w-full text-left p-3 rounded-lg border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-all duration-200 group bg-white hover:shadow-sm"
                                    >
                                        <div class="flex items-center">
                                            <span class="text-lg mr-3">{{ action.icon }}</span>
                                            <div>
                                                <h4 class="font-medium text-gray-900 group-hover:text-blue-700 text-sm">{{ action.name }}</h4>
                                                <p class="text-xs text-gray-600">{{ action.description }}</p>
                                            </div>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Upcoming Reservations -->
                    <div>
                        <div class="bg-white border-gray-200 shadow-lg rounded-xl border hover:shadow-xl transition-all duration-300 h-[400px] flex flex-col">
                            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-green-50 to-emerald-50">
                                <h3 class="text-lg font-semibold text-slate-800">Upcoming</h3>
                            </div>
                            <div class="p-6 flex-1 overflow-y-auto">
                                <div class="space-y-3">
                                    <div v-if="props.upcomingReservations.length > 0">
                                        <div
                                            v-for="reservation in props.upcomingReservations"
                                            :key="reservation.id"
                                            class="p-3 bg-green-50 rounded-lg border border-green-200"
                                        >
                                            <div class="flex items-center space-x-3">
                                                <span class="text-green-600 text-sm">📅</span>
                                                <div class="flex-1">
                                                    <h4 class="font-medium text-gray-900 text-xs truncate">{{ reservation.equipment.name }}</h4>
                                                    <p class="text-xs text-gray-600">{{ reservation.reservation_date }}</p>
                                                    <p class="text-xs text-gray-500">{{ reservation.start_time }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="text-center py-8 text-gray-500">
                                        <p class="text-sm">No upcoming reservations</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Equipment Catalog -->
                <div id="equipment-catalog" class="mt-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
                            <h3 class="text-lg font-semibold text-slate-800">Available Equipment</h3>
                            <p class="text-sm text-gray-600 mt-1">Browse equipment available for reservation</p>
                        </div>

                        <div v-if="props.availableEquipment.length > 0" class="p-6">
                            <!-- Equipment Categories -->
                            <div class="space-y-6">
                                <div v-for="category in props.availableEquipment" :key="category.category">
                                    <h4 class="text-md font-medium text-gray-900 mb-3 border-b border-gray-200 pb-2">
                                        {{ category.category }}
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                        <div
                                            v-for="equipment in category.equipment"
                                            :key="equipment.id"
                                            class="bg-gray-50 rounded-lg p-4 hover:bg-white hover:shadow-md transition-all duration-200 border border-gray-200 cursor-pointer"
                                        >
                                            <div class="flex flex-col h-full">
                                                <!-- Equipment Image -->
                                                <div class="mb-3">
                                                    <img
                                                        :src="getEquipmentImageUrl(equipment.image)"
                                                        :alt="equipment.name"
                                                        class="w-full h-32 object-cover rounded-md"
                                                        @error="$event.target.src = '/images/equipment.png'"
                                                    />
                                                </div>

                                                <!-- Equipment Details -->
                                                <div class="flex-1">
                                                    <h5 class="font-semibold text-gray-900 text-sm mb-1">{{ equipment.name }}</h5>
                                                    <p class="text-xs text-gray-600 mb-2 line-clamp-2">
                                                        {{ equipment.description || 'No description available' }}
                                                    </p>
                                                    <p v-if="equipment.location" class="text-xs text-gray-500">
                                                        📍 {{ equipment.location }}
                                                    </p>
                                                </div>

                                                <!-- Reserve Button -->
                                                <div class="mt-3">
                                                    <button
                                                        class="w-full bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium py-2 px-3 rounded-md transition-colors duration-200"
                                                        @click="reserveEquipment(equipment)"
                                                    >
                                                        Reserve
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-else class="p-12 text-center">
                            <div class="mb-4">
                                <img src="/images/equipment.png" alt="Equipment" class="w-16 h-16 mx-auto opacity-30" />
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Equipment Available</h3>
                            <p class="text-gray-500">There is currently no equipment available for reservation.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reservation Modal -->
        <ReservationModal
            :show="showReservationModal"
            :equipment="selectedEquipment"
            :availableEquipment="availableEquipment"
            @close="showReservationModal = false"
            @created="handleReservationCreated"
        />
    </StudentLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    line-clamp: 2;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
