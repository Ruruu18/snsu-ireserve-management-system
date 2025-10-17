<script setup>
import { ref, computed, onMounted } from 'vue';
import FacultyLayout from '@/Layouts/FacultyLayout.vue';
import Modal from '@/Components/Modal.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    reservations: Object
});

const showRejectModal = ref(false);
const showReturnModal = ref(false);
const selectedReservation = ref(null);
const rejectNotes = ref('');
const returnNotes = ref('');
const loading = ref(false);

const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'approved':
            return 'bg-green-100 text-green-800';
        case 'issued':
            return 'bg-blue-100 text-blue-800';
        case 'completed':
            return 'bg-gray-100 text-gray-800';
        case 'cancelled':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const approveReservation = (reservation) => {
    if (confirm('Are you sure you want to approve this reservation?')) {
        loading.value = true;
        router.patch(route('faculty.reservations.approve', reservation.id), {}, {
            onSuccess: () => {
                loading.value = false;
            },
            onError: () => {
                loading.value = false;
            }
        });
    }
};

const openRejectModal = (reservation) => {
    selectedReservation.value = reservation;
    rejectNotes.value = '';
    showRejectModal.value = true;
};

const rejectReservation = () => {
    if (!selectedReservation.value) return;

    loading.value = true;
    router.patch(route('faculty.reservations.reject', selectedReservation.value.id), {
        admin_notes: rejectNotes.value
    }, {
        onSuccess: () => {
            loading.value = false;
            showRejectModal.value = false;
            selectedReservation.value = null;
            rejectNotes.value = '';
        },
        onError: () => {
            loading.value = false;
        }
    });
};

const openReturnModal = (reservation) => {
    selectedReservation.value = reservation;
    returnNotes.value = '';
    showReturnModal.value = true;
};

const markAsReturned = () => {
    if (!selectedReservation.value) return;

    loading.value = true;
    router.patch(route('faculty.reservations.return', selectedReservation.value.id), {
        admin_notes: returnNotes.value
    }, {
        onSuccess: () => {
            loading.value = false;
            showReturnModal.value = false;
            selectedReservation.value = null;
            returnNotes.value = '';
        },
        onError: () => {
            loading.value = false;
        }
    });
};

const closeModals = () => {
    showRejectModal.value = false;
    showReturnModal.value = false;
    selectedReservation.value = null;
    rejectNotes.value = '';
    returnNotes.value = '';
};
</script>

<template>
    <Head title="Reservation Management" />

    <FacultyLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Reservation Management
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- Header with stats -->
                        <div class="mb-6">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="bg-yellow-50 p-4 rounded-lg">
                                    <div class="text-2xl font-bold text-yellow-600">
                                        {{ reservations.data.filter(r => r.status === 'pending').length }}
                                    </div>
                                    <div class="text-sm text-yellow-800">Pending</div>
                                </div>
                                <div class="bg-green-50 p-4 rounded-lg">
                                    <div class="text-2xl font-bold text-green-600">
                                        {{ reservations.data.filter(r => r.status === 'approved').length }}
                                    </div>
                                    <div class="text-sm text-green-800">Approved</div>
                                </div>
                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <div class="text-2xl font-bold text-blue-600">
                                        {{ reservations.data.filter(r => r.status === 'issued').length }}
                                    </div>
                                    <div class="text-sm text-blue-800">Issued</div>
                                </div>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <div class="text-2xl font-bold text-gray-600">
                                        {{ reservations.data.filter(r => r.status === 'completed').length }}
                                    </div>
                                    <div class="text-sm text-gray-800">Completed</div>
                                </div>
                            </div>
                        </div>

                        <!-- Reservations Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Equipment
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Student
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date & Time
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
                                    <tr v-for="reservation in reservations.data" :key="reservation.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ reservation.equipment_name }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ reservation.total_items }} item(s) • {{ reservation.items_count }} type(s)
                                            </div>
                                            <div v-if="reservation.purpose" class="text-xs text-gray-400 mt-1">
                                                Purpose: {{ reservation.purpose }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ reservation.user_name }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ reservation.user_email }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ reservation.date }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ reservation.start_time }} - {{ reservation.end_time }}
                                            </div>
                                            <div class="text-xs text-gray-400">
                                                Created: {{ reservation.created_at }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="getStatusBadgeClass(reservation.status)"
                                                  class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                                {{ reservation.status.charAt(0).toUpperCase() + reservation.status.slice(1) }}
                                            </span>
                                            <div v-if="reservation.admin_notes" class="text-xs text-gray-500 mt-1">
                                                Notes: {{ reservation.admin_notes }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                            <!-- Approve Button -->
                                            <button v-if="reservation.status === 'pending'"
                                                    @click="approveReservation(reservation)"
                                                    :disabled="loading"
                                                    class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50">
                                                ✓ Approve
                                            </button>

                                            <!-- Reject Button -->
                                            <button v-if="reservation.status === 'pending'"
                                                    @click="openRejectModal(reservation)"
                                                    :disabled="loading"
                                                    class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50">
                                                ✗ Reject
                                            </button>

                                            <!-- Mark as Returned Button -->
                                            <button v-if="reservation.status === 'approved'"
                                                    @click="openReturnModal(reservation)"
                                                    :disabled="loading"
                                                    class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                                                📦 Mark Returned
                                            </button>

                                            <!-- Status indicator for completed/cancelled -->
                                            <span v-if="['completed', 'cancelled'].includes(reservation.status)"
                                                  class="text-xs text-gray-500">
                                                No actions available
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="reservations.links" class="mt-6">
                            <nav class="flex items-center justify-between">
                                <div class="flex justify-between flex-1 sm:hidden">
                                    <a v-if="reservations.prev_page_url"
                                       :href="reservations.prev_page_url"
                                       class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:text-gray-400">
                                        Previous
                                    </a>
                                    <a v-if="reservations.next_page_url"
                                       :href="reservations.next_page_url"
                                       class="relative ml-3 inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:text-gray-400">
                                        Next
                                    </a>
                                </div>
                                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm text-gray-700">
                                            Showing {{ reservations.from }} to {{ reservations.to }} of {{ reservations.total }} results
                                        </p>
                                    </div>
                                    <div class="flex space-x-1">
                                        <template v-for="link in reservations.links" :key="link.label">
                                            <a v-if="link.url && !link.active"
                                               :href="link.url"
                                               class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 hover:text-gray-400"
                                               v-html="link.label">
                                            </a>
                                            <span v-else-if="link.active"
                                                  class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-indigo-600"
                                                  v-html="link.label">
                                            </span>
                                            <span v-else
                                                  class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-300 bg-white border border-gray-300"
                                                  v-html="link.label">
                                            </span>
                                        </template>
                                    </div>
                                </div>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <Modal :show="showRejectModal" @close="closeModals">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Reject Reservation
                </h3>
                <p class="text-sm text-gray-600 mb-4">
                    Are you sure you want to reject this reservation? Please provide a reason.
                </p>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Rejection Reason (Optional)
                    </label>
                    <textarea v-model="rejectNotes"
                              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                              rows="3"
                              placeholder="Enter reason for rejection..."></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button @click="closeModals"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                        Cancel
                    </button>
                    <button @click="rejectReservation"
                            :disabled="loading"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 disabled:opacity-50">
                        Reject Reservation
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Return Modal -->
        <Modal :show="showReturnModal" @close="closeModals">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Mark Equipment as Returned
                </h3>
                <p class="text-sm text-gray-600 mb-4">
                    Mark this equipment as returned and complete the reservation.
                </p>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Return Notes (Optional)
                    </label>
                    <textarea v-model="returnNotes"
                              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              rows="3"
                              placeholder="Add any notes about the return condition..."></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button @click="closeModals"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                        Cancel
                    </button>
                    <button @click="markAsReturned"
                            :disabled="loading"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50">
                        Mark as Returned
                    </button>
                </div>
            </div>
        </Modal>
    </FacultyLayout>
</template>