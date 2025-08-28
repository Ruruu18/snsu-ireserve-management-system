<script setup>
import FacultyLayout from '@/Layouts/FacultyLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    reservations: {
        type: Object,
        required: true
    }
});

// Modal states
const showReturnModal = ref(false);
const selectedReservation = ref(null);

// Form
const returnForm = useForm({
    admin_notes: ''
});

// Status utilities
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

// Actions
const openReturnModal = (reservation) => {
    selectedReservation.value = reservation;
    returnForm.reset();
    showReturnModal.value = true;
};

const markAsReturned = () => {
    returnForm.patch(route('faculty.reservations.return', selectedReservation.value.id), {
        onSuccess: () => {
            showReturnModal.value = false;
            selectedReservation.value = null;
        }
    });
};

const closeModal = () => {
    showReturnModal.value = false;
    selectedReservation.value = null;
    returnForm.reset();
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const isOverdue = (reservation) => {
    const endDateTime = new Date(`${reservation.reservation_date} ${reservation.end_time}`);
    return new Date() > endDateTime;
};

const getDaysIssued = (approvedAt) => {
    const approved = new Date(approvedAt);
    const now = new Date();
    const diffTime = Math.abs(now - approved);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
};
</script>

<template>
    <Head title="Issued Equipment" />

    <FacultyLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-900">
                        📋 Issued Equipment
                    </h2>
                    <p class="text-gray-600 mt-1">Track and manage currently issued equipment.</p>
                </div>
                <div class="flex items-center space-x-2 text-sm text-gray-600">
                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full font-medium">
                        {{ reservations.total }} Active Issues
                    </span>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Reservations List -->
                <div v-if="reservations.data.length > 0" class="space-y-4">
                    <div
                        v-for="reservation in reservations.data"
                        :key="reservation.id"
                        :class="[
                            'bg-white border rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200',
                            isOverdue(reservation) ? 'border-red-300 bg-red-50' : 'border-gray-200'
                        ]"
                    >
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3 mb-3">
                                        <span class="text-2xl">{{ getStatusIcon(reservation.status) }}</span>
                                        <div>
                                            <div class="flex items-center space-x-2">
                                                <h3 class="text-lg font-semibold text-gray-900">{{ reservation.equipment.name }}</h3>
                                                <span v-if="isOverdue(reservation)" class="px-2 py-1 bg-red-100 text-red-800 text-xs font-medium rounded-full border border-red-200">
                                                    ⚠️ OVERDUE
                                                </span>
                                            </div>
                                            <p class="text-sm text-gray-600">
                                                Issued to: <strong>{{ reservation.user.name }}</strong> ({{ reservation.user.email }})
                                            </p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                                        <div>
                                            <span class="font-medium text-gray-700">Reservation Date:</span>
                                            <p class="text-gray-900">{{ new Date(reservation.reservation_date).toLocaleDateString() }}</p>
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-700">Time Slot:</span>
                                            <p class="text-gray-900">{{ reservation.start_time }} - {{ reservation.end_time }}</p>
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-700">Approved:</span>
                                            <p class="text-gray-900">{{ formatDate(reservation.approved_at) }}</p>
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-700">Days Issued:</span>
                                            <p :class="[
                                                'font-medium',
                                                getDaysIssued(reservation.approved_at) > 3 ? 'text-red-600' : 'text-gray-900'
                                            ]">
                                                {{ getDaysIssued(reservation.approved_at) }} day{{ getDaysIssued(reservation.approved_at) !== 1 ? 's' : '' }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <span class="font-medium text-gray-700">Purpose:</span>
                                        <p class="text-gray-900 mt-1">{{ reservation.purpose }}</p>
                                    </div>

                                    <div v-if="reservation.admin_notes" class="mt-4">
                                        <span class="font-medium text-gray-700">Admin Notes:</span>
                                        <p class="text-gray-600 mt-1">{{ reservation.admin_notes }}</p>
                                    </div>
                                </div>

                                <div class="ml-6 flex flex-col items-end space-y-3">
                                    <!-- Status Badge -->
                                    <span
                                        :class="getStatusColor(reservation.status)"
                                        class="px-3 py-1 rounded-full text-sm font-medium capitalize border"
                                    >
                                        {{ reservation.status }}
                                    </span>

                                    <!-- Action Buttons -->
                                    <div class="flex flex-col space-y-2">
                                        <PrimaryButton
                                            @click="openReturnModal(reservation)"
                                            class="text-sm px-4 py-2 bg-blue-600 hover:bg-blue-700"
                                        >
                                            📦 Mark as Returned
                                        </PrimaryButton>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12">
                    <div class="mb-4">
                        <span class="text-6xl">📋</span>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Equipment Currently Issued</h3>
                    <p class="text-gray-500">There is currently no equipment checked out to students.</p>
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

        <!-- Return Modal -->
        <Modal :show="showReturnModal" @close="closeModal" max-width="md">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Mark Equipment as Returned</h2>
                <div v-if="selectedReservation" class="mb-6">
                    <p class="text-gray-600 mb-4">
                        Marking <strong>{{ selectedReservation.equipment.name }}</strong>
                        as returned from <strong>{{ selectedReservation.user.name }}</strong>.
                    </p>
                    <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-600">
                            <strong>Originally scheduled:</strong> {{ new Date(selectedReservation.reservation_date).toLocaleDateString() }}<br>
                            <strong>Time:</strong> {{ selectedReservation.start_time }} - {{ selectedReservation.end_time }}<br>
                            <strong>Days issued:</strong> {{ getDaysIssued(selectedReservation.approved_at) }} day{{ getDaysIssued(selectedReservation.approved_at) !== 1 ? 's' : '' }}
                        </p>
                    </div>
                    <div>
                        <InputLabel for="admin_notes" value="Return Notes (Optional)" />
                        <textarea
                            id="admin_notes"
                            v-model="returnForm.admin_notes"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            rows="3"
                            placeholder="Add any notes about the condition of the equipment or return process..."
                        ></textarea>
                        <InputError class="mt-2" :message="returnForm.errors.admin_notes" />
                    </div>
                </div>
                <div class="flex items-center justify-end space-x-3">
                    <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                    <PrimaryButton
                        @click="markAsReturned"
                        :disabled="returnForm.processing"
                        class="bg-blue-600 hover:bg-blue-700"
                    >
                        <span v-if="returnForm.processing">Processing...</span>
                        <span v-else>Mark as Returned</span>
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </FacultyLayout>
</template>
