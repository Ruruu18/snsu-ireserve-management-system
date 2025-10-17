<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import ReservationModal from '@/Components/ReservationModal.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    reservations: {
        type: Object,
        required: true
    }
});

// Modal state
const showReservationModal = ref(false);
const selectedEquipment = ref(null);
const editingReservation = ref(null);

// Status colors for reservations
const getStatusColor = (status) => {
    switch (status) {
        case 'approved':
            return 'bg-green-100 text-green-800 border-green-200';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800 border-yellow-200';
        case 'issued':
            return 'bg-purple-100 text-purple-800 border-purple-200';
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
        case 'issued':
            return '📦';
        case 'completed':
            return '🎉';
        case 'cancelled':
            return '🚫';
        default:
            return '❓';
    }
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

// Create new reservation
const createNewReservation = () => {
    showReservationModal.value = true;
    selectedEquipment.value = null;
    editingReservation.value = null;
};

// Handle reservation creation success
const handleReservationCreated = () => {
    showReservationModal.value = false;
    selectedEquipment.value = null;
    editingReservation.value = null;
    router.reload();
};

// Group reservations by status
const groupedReservations = computed(() => {
    const groups = {
        pending: [],
        approved: [],
        issued: [],
        completed: [],
        cancelled: []
    };

    props.reservations.data.forEach(reservation => {
        if (groups[reservation.status]) {
            groups[reservation.status].push(reservation);
        }
    });

    return groups;
});

const statusTabs = [
    { key: 'all', label: 'All Reservations', count: props.reservations.data.length },
    { key: 'pending', label: 'Pending', count: groupedReservations.value.pending.length },
    { key: 'approved', label: 'Approved', count: groupedReservations.value.approved.length },
    { key: 'issued', label: 'Issued', count: groupedReservations.value.issued.length },
    { key: 'completed', label: 'Completed', count: groupedReservations.value.completed.length },
    { key: 'cancelled', label: 'Cancelled', count: groupedReservations.value.cancelled.length },
];

const activeTab = ref('all');

const filteredReservations = computed(() => {
    if (activeTab.value === 'all') {
        return props.reservations.data;
    }
    return groupedReservations.value[activeTab.value] || [];
});
</script>

<template>
    <Head title="My Reservations" />

    <StudentLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold leading-tight text-gray-900">
                        My Reservations
                    </h2>
                    <p class="text-gray-600 mt-1 text-sm sm:text-base">View and manage all your equipment reservations.</p>
                </div>
                <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
                    <Link
                        :href="route('student.dashboard')"
                        class="inline-flex items-center px-3 sm:px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 focus:bg-gray-600 active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 w-full sm:w-auto justify-center"
                    >
                        Back to Dashboard
                    </Link>
                    <button
                        @click="createNewReservation"
                        class="inline-flex items-center px-3 sm:px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 w-full sm:w-auto justify-center"
                    >
                        New Reservation
                    </button>
                </div>
            </div>
        </template>

        <div class="py-4 sm:py-6 pb-8 sm:pb-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Status Filter Tabs -->
                <div class="border-b border-gray-200 mb-6">
                    <nav class="-mb-px flex space-x-8">
                        <button
                            v-for="tab in statusTabs"
                            :key="tab.key"
                            @click="activeTab = tab.key"
                            :class="[
                                activeTab === tab.key
                                    ? 'border-blue-500 text-blue-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm'
                            ]"
                        >
                            {{ tab.label }}
                            <span
                                :class="[
                                    activeTab === tab.key
                                        ? 'bg-blue-100 text-blue-600'
                                        : 'bg-gray-100 text-gray-900',
                                    'ml-2 py-0.5 px-2.5 rounded-full text-xs font-medium'
                                ]"
                            >
                                {{ tab.count }}
                            </span>
                        </button>
                    </nav>
                </div>

                <!-- Reservations List -->
                <div v-if="filteredReservations.length > 0" class="space-y-4">
                    <div
                        v-for="reservation in filteredReservations"
                        :key="reservation.id"
                        class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200"
                    >
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3">
                                        <span class="text-2xl">{{ getStatusIcon(reservation.status) }}</span>
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900">{{ reservation.equipment_name || 'Multiple Items' }}</h3>
                                            <p class="text-sm text-gray-600">
                                                {{ reservation.date || 'N/A' }} • {{ reservation.start_time || 'N/A' }} - {{ reservation.end_time || 'N/A' }}
                                            </p>
                                            <p class="text-sm text-gray-500 mt-1">
                                                <strong>Purpose:</strong> {{ reservation.purpose || 'N/A' }}
                                            </p>
                                            <p v-if="reservation.admin_notes" class="text-sm text-gray-500 mt-1">
                                                <strong>Admin Notes:</strong> {{ reservation.admin_notes }}
                                            </p>
                                            <p class="text-xs text-gray-400 mt-2">
                                                Created: {{ reservation.created_at }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center space-x-3">
                                    <!-- Status Badge -->
                                    <span
                                        :class="getStatusColor(reservation.status)"
                                        class="px-3 py-1 rounded-full text-sm font-medium capitalize border"
                                    >
                                        {{ reservation.status }}
                                    </span>

                                    <!-- Action Buttons -->
                                    <div class="flex items-center space-x-2">
                                        <button
                                            v-if="reservation.can_cancel"
                                            @click="cancelReservation(reservation.id)"
                                            class="text-red-600 hover:text-red-800 text-sm font-medium transition duration-200 hover:underline"
                                        >
                                            Cancel
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
                        {{ activeTab === 'all' ? 'No Reservations Found' : `No ${activeTab} reservations` }}
                    </h3>
                    <p class="text-gray-500 mb-6">
                        {{ activeTab === 'all' ? 'Start by making your first reservation!' : `You don't have any ${activeTab} reservations.` }}
                    </p>
                    <button
                        @click="createNewReservation"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-blue-700 transition ease-in-out duration-150"
                    >
                        Create New Reservation
                    </button>
                </div>

                <!-- Pagination -->
                <div v-if="reservations.links && reservations.links.length > 3" class="mt-8">
                    <nav class="flex items-center justify-between">
                        <div class="flex-1 flex justify-between sm:hidden">
                            <Link
                                v-if="reservations.prev_page_url"
                                :href="reservations.prev_page_url"
                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Previous
                            </Link>
                            <Link
                                v-if="reservations.next_page_url"
                                :href="reservations.next_page_url"
                                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Next
                            </Link>
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Showing
                                    <span class="font-medium">{{ reservations.from }}</span>
                                    to
                                    <span class="font-medium">{{ reservations.to }}</span>
                                    of
                                    <span class="font-medium">{{ reservations.total }}</span>
                                    results
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                    <Link
                                        v-for="link in reservations.links"
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

        <!-- Reservation Modal -->
        <ReservationModal
            :show="showReservationModal"
            :equipment="selectedEquipment"
            :availableEquipment="[]"
            @close="showReservationModal = false"
            @created="handleReservationCreated"
        />
    </StudentLayout>
</template>
