<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { useFormatters } from '@/composables/useFormatters';

const props = defineProps({
    reservations: {
        type: Object,
        required: true
    }
});

// Use formatters
const { formatStatus } = useFormatters();

// Selected reservation for viewing details
const selectedReservation = ref(null);
const showDetailsModal = ref(false);

// Status filter
const statusFilter = ref('all');

// Forms for actions
const approveForm = useForm({});
const rejectForm = useForm({
    reason: ''
});

// Get equipment image URL
const getEquipmentImageUrl = (imagePath) => {
    return imagePath ? `/storage/${imagePath}` : '/images/equipment.png';
};

// Get status color class
const getStatusColor = (status) => {
    const colors = {
        pending: 'text-yellow-600 bg-yellow-100',
        approved: 'text-blue-600 bg-blue-100',
        issued: 'text-green-600 bg-green-100',
        returned: 'text-gray-600 bg-gray-100',
        cancelled: 'text-red-600 bg-red-100'
    };
    return colors[status] || 'text-gray-600 bg-gray-100';
};

// Show reservation details
const showDetails = (reservation) => {
    selectedReservation.value = reservation;
    showDetailsModal.value = true;
};

// Close details modal
const closeDetails = () => {
    selectedReservation.value = null;
    showDetailsModal.value = false;
};

// Approve reservation
const approveReservation = (reservation) => {
    approveForm.patch(route('admin.reservations.approve', reservation.id), {
        onSuccess: () => {
            reservation.status = 'approved';
        },
        onError: (errors) => {
            console.error('Approval errors:', errors);
        }
    });
};

// Reject reservation
const rejectReservation = (reservation) => {
    if (!rejectForm.reason.trim()) {
        alert('Please provide a reason for rejection');
        return;
    }

    rejectForm.patch(route('admin.reservations.reject', reservation.id), {
        onSuccess: () => {
            reservation.status = 'cancelled';
            rejectForm.reason = '';
        },
        onError: (errors) => {
            console.error('Rejection errors:', errors);
        }
    });
};

// Format date
const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString();
};

// Format time from 24-hour to 12-hour format
const formatTime = (timeString) => {
    if (!timeString) return '';

    // Parse time string (HH:MM:SS or HH:MM)
    const [hours, minutes] = timeString.split(':');
    const hour = parseInt(hours);
    const min = minutes;

    // Convert to 12-hour format
    const period = hour >= 12 ? 'PM' : 'AM';
    const hour12 = hour % 12 || 12;

    return `${hour12}:${min} ${period}`;
};

// Filtered reservations based on status
const filteredReservations = computed(() => {
    if (statusFilter.value === 'all') {
        return props.reservations.data;
    }
    return props.reservations.data.filter(reservation => reservation.status === statusFilter.value);
});
</script>

<template>
    <Head title="Reservation Management" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold leading-tight text-gray-900">
                        Reservation Management
                    </h2>
                    <p class="text-gray-600 mt-1 text-sm sm:text-base">Review and manage equipment reservations.</p>
                </div>
                <div class="flex space-x-3">
                    <PrimaryButton @click="$inertia.visit(route('admin.qr-scanner'))" class="bg-blue-600 hover:bg-blue-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-2 0a2 2 0 01-2 2H8a2 2 0 01-2-2m0 0h2m10-10V3a2 2 0 00-2-2H8a2 2 0 00-2 2v1m10 0V4m0 0a2 2 0 00-2-2H8a2 2 0 00-2 2v0" />
                        </svg>
                        QR Scanner
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="py-4 sm:py-6 pb-8 sm:pb-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Status Filter Tabs -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
                    <div class="flex flex-wrap gap-2">
                        <button
                            @click="statusFilter = 'all'"
                            :class="[
                                'px-4 py-2 text-sm font-medium rounded-lg transition-colors',
                                statusFilter === 'all' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                            ]"
                        >
                            All Reservations
                        </button>
                        <button
                            @click="statusFilter = 'pending'"
                            :class="[
                                'px-4 py-2 text-sm font-medium rounded-lg transition-colors',
                                statusFilter === 'pending' ? 'bg-yellow-600 text-white' : 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200'
                            ]"
                        >
                            Pending ({{ reservations.data.filter(r => r.status === 'pending').length }})
                        </button>
                        <button
                            @click="statusFilter = 'approved'"
                            :class="[
                                'px-4 py-2 text-sm font-medium rounded-lg transition-colors',
                                statusFilter === 'approved' ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-700 hover:bg-blue-200'
                            ]"
                        >
                            Approved ({{ reservations.data.filter(r => r.status === 'approved').length }})
                        </button>
                        <button
                            @click="statusFilter = 'issued'"
                            :class="[
                                'px-4 py-2 text-sm font-medium rounded-lg transition-colors',
                                statusFilter === 'issued' ? 'bg-green-600 text-white' : 'bg-green-100 text-green-700 hover:bg-green-200'
                            ]"
                        >
                            Issued ({{ reservations.data.filter(r => r.status === 'issued').length }})
                        </button>
                        <button
                            @click="statusFilter = 'returned'"
                            :class="[
                                'px-4 py-2 text-sm font-medium rounded-lg transition-colors',
                                statusFilter === 'returned' ? 'bg-gray-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                            ]"
                        >
                            Returned ({{ reservations.data.filter(r => r.status === 'returned').length }})
                        </button>
                    </div>
                </div>

                <!-- Reservations Table -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">
                            {{ statusFilter === 'all' ? 'All Reservations' : statusFilter.charAt(0).toUpperCase() + statusFilter.slice(1) + ' Reservations' }}
                            <span class="text-sm font-normal text-gray-500 ml-2">({{ filteredReservations.length }})</span>
                        </h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Student & Code
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Equipment
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Schedule
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="reservation in filteredReservations" :key="reservation.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ reservation.user.name }}</div>
                                            <div class="text-sm text-gray-500">{{ reservation.user.email }}</div>
                                            <div class="text-xs text-gray-400 font-mono">{{ reservation.reservation_code }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            {{ reservation.items.length }} item(s)
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ reservation.items.map(item => `${item.equipment.name} (${item.quantity})`).join(', ') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ formatDate(reservation.reservation_date) }}</div>
                                        <div class="text-sm text-gray-500">{{ formatTime(reservation.start_time) }} - {{ formatTime(reservation.end_time) }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span :class="getStatusColor(reservation.status)" class="inline-flex px-2 py-1 text-xs font-medium rounded-full">
                                            {{ formatStatus(reservation.status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex space-x-2">
                                            <SecondaryButton @click="showDetails(reservation)" class="text-xs py-1 px-2">
                                                Details
                                            </SecondaryButton>

                                            <!-- Actions for pending reservations -->
                                            <template v-if="reservation.status === 'pending'">
                                                <PrimaryButton
                                                    @click="approveReservation(reservation)"
                                                    :disabled="approveForm.processing"
                                                    class="text-xs py-1 px-2 bg-green-600 hover:bg-green-700"
                                                >
                                                    Approve
                                                </PrimaryButton>
                                                <DangerButton
                                                    @click="rejectForm.reason = ''; selectedReservation = reservation"
                                                    class="text-xs py-1 px-2"
                                                >
                                                    Reject
                                                </DangerButton>
                                            </template>

                                            <!-- QR Scanner link for approved reservations -->
                                            <template v-if="reservation.status === 'approved'">
                                                <PrimaryButton
                                                    @click="$inertia.visit(route('admin.qr-scanner'))"
                                                    class="text-xs py-1 px-2 bg-blue-600 hover:bg-blue-700"
                                                >
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-2 0a2 2 0 01-2 2H8a2 2 0 01-2-2m0 0h2m10-10V3a2 2 0 00-2-2H8a2 2 0 00-2 2v1m10 0V4m0 0a2 2 0 00-2-2H8a2 2 0 00-2 2v0" />
                                                    </svg>
                                                    Scan QR
                                                </PrimaryButton>
                                            </template>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="reservations.links" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                        <nav class="flex justify-between items-center">
                            <div class="text-sm text-gray-700">
                                Showing {{ reservations.from }} to {{ reservations.to }} of {{ reservations.total }} results
                            </div>
                            <div class="flex space-x-1">
                                <template v-for="(link, index) in reservations.links" :key="index">
                                    <button
                                        v-if="link.url"
                                        @click="$inertia.get(link.url)"
                                        :class="[
                                            'px-3 py-1 text-sm rounded',
                                            link.active ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'
                                        ]"
                                        v-html="link.label"
                                    />
                                    <span v-else class="px-3 py-1 text-sm text-gray-400" v-html="link.label" />
                                </template>
                            </div>
                        </nav>
                    </div>
                </div>

                <!-- Details Modal -->
                <div v-if="showDetailsModal && selectedReservation" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeDetails"></div>

                        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="flex justify-between items-start mb-4">
                                    <h3 class="text-lg font-medium text-gray-900" id="modal-title">
                                        Reservation Details
                                    </h3>
                                    <button @click="closeDetails" class="text-gray-400 hover:text-gray-600">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Student Info -->
                                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                    <h4 class="font-medium text-gray-900 mb-2">Student Information</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                        <div><strong>Name:</strong> {{ selectedReservation.user.name }}</div>
                                        <div><strong>Email:</strong> {{ selectedReservation.user.email }}</div>
                                        <div><strong>Code:</strong> {{ selectedReservation.reservation_code }}</div>
                                        <div>
                                            <strong>Status:</strong>
                                            <span :class="getStatusColor(selectedReservation.status)" class="px-2 py-1 rounded-full text-xs font-medium ml-1">
                                                {{ selectedReservation.status }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Reservation Info -->
                                <div class="mb-4">
                                    <h4 class="font-medium text-gray-900 mb-2">Reservation Information</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                        <div><strong>Purpose:</strong> {{ selectedReservation.purpose }}</div>
                                        <div><strong>Date:</strong> {{ formatDate(selectedReservation.reservation_date) }}</div>
                                        <div><strong>Time:</strong> {{ formatTime(selectedReservation.start_time) }} - {{ formatTime(selectedReservation.end_time) }}</div>
                                        <div><strong>Created:</strong> {{ formatDate(selectedReservation.created_at) }}</div>
                                    </div>
                                    <div v-if="selectedReservation.notes" class="mt-2">
                                        <strong>Notes:</strong> {{ selectedReservation.notes }}
                                    </div>
                                </div>

                                <!-- Equipment Items -->
                                <div class="mb-4">
                                    <h4 class="font-medium text-gray-900 mb-2">Equipment Items</h4>
                                    <div class="space-y-2 max-h-40 overflow-y-auto">
                                        <div v-for="item in selectedReservation.items" :key="item.id" class="flex items-center space-x-3 p-2 bg-gray-50 rounded-lg">
                                            <div class="flex-shrink-0 w-12 h-12 bg-white rounded-lg flex items-center justify-center border">
                                                <img
                                                    v-if="item.equipment.image"
                                                    :src="getEquipmentImageUrl(item.equipment.image)"
                                                    :alt="item.equipment.name"
                                                    class="max-w-full max-h-full object-contain"
                                                />
                                                <div v-else class="text-gray-400">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <h5 class="text-sm font-medium text-gray-900">{{ item.equipment.name }}</h5>
                                                <p class="text-sm text-gray-600">Quantity: {{ item.quantity }}</p>
                                                <span :class="getStatusColor(item.status)" class="px-2 py-1 rounded-full text-xs font-medium">
                                                    {{ item.status }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <SecondaryButton @click="closeDetails">Close</SecondaryButton>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rejection Modal -->
                <div v-if="selectedReservation && rejectForm.errors.length === 0 && selectedReservation.status === 'pending'" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="reject-modal-title" role="dialog" aria-modal="true">
                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="selectedReservation = null"></div>

                        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <h3 class="text-lg font-medium text-gray-900" id="reject-modal-title">
                                    Reject Reservation
                                </h3>
                                <div class="mt-4">
                                    <label for="reject-reason" class="block text-sm font-medium text-gray-700 mb-2">
                                        Reason for rejection
                                    </label>
                                    <textarea
                                        id="reject-reason"
                                        v-model="rejectForm.reason"
                                        rows="3"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Please provide a reason for rejecting this reservation..."
                                    ></textarea>
                                </div>
                            </div>

                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse space-x-3">
                                <DangerButton
                                    @click="rejectReservation(selectedReservation)"
                                    :disabled="rejectForm.processing || !rejectForm.reason.trim()"
                                    class="ml-3"
                                >
                                    {{ rejectForm.processing ? 'Rejecting...' : 'Reject' }}
                                </DangerButton>
                                <SecondaryButton @click="selectedReservation = null; rejectForm.reason = ''">Cancel</SecondaryButton>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>