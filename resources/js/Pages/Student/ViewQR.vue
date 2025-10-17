<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import { Head } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    reservation: {
        type: Object,
        required: true
    },
    qrCode: {
        type: String,
        required: true
    }
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

// Download QR code (already PNG format from server)
const downloadQR = () => {
    const link = document.createElement('a');
    link.href = props.qrCode;
    link.download = `reservation-${props.reservation.reservation_code}-qr.png`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

// Print reservation details
const printReservation = () => {
    window.print();
};
</script>

<template>
    <Head :title="`Reservation ${reservation.reservation_code} - QR Code`" />

    <StudentLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold leading-tight text-gray-900">
                        Reservation QR Code
                    </h2>
                    <p class="text-gray-600 mt-1 text-sm sm:text-base">View your reservation details and QR code.</p>
                </div>
            </div>
        </template>

        <div class="py-4 sm:py-6 pb-8 sm:pb-12">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Back Navigation -->
                <div class="mb-6">
                    <SecondaryButton @click="$inertia.visit(route('student.equipment.requested'))" class="w-full sm:w-auto">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Requested Equipment
                    </SecondaryButton>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- QR Code Section -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 text-center">QR Code</h3>

                        <div class="mb-6 text-center">
                            <img
                                :src="qrCode"
                                :alt="`QR Code for reservation ${reservation.reservation_code}`"
                                class="mx-auto rounded-lg shadow-sm"
                                style="max-width: 250px; max-height: 250px;"
                            />
                        </div>

                        <div class="text-center mb-4">
                            <p class="text-sm text-gray-600 mb-2">
                                Reservation Code: <strong>{{ reservation.reservation_code }}</strong>
                            </p>
                            <span :class="getStatusColor(reservation.status)" class="px-3 py-1 rounded-full text-sm font-medium">
                                {{ reservation.status.charAt(0).toUpperCase() + reservation.status.slice(1) }}
                            </span>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3 justify-center">
                            <PrimaryButton @click="downloadQR" class="w-full sm:w-auto">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Download QR
                            </PrimaryButton>
                            <SecondaryButton @click="printReservation" class="w-full sm:w-auto no-print">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Print
                            </SecondaryButton>
                        </div>
                    </div>

                    <!-- Reservation Details -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Reservation Details</h3>

                        <div class="space-y-3 mb-6">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Student:</span>
                                <span class="font-medium">{{ reservation.student_name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Purpose:</span>
                                <span class="font-medium">{{ reservation.purpose }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Date:</span>
                                <span class="font-medium">{{ reservation.date }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Time:</span>
                                <span class="font-medium">{{ reservation.start_time }} - {{ reservation.end_time }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Created:</span>
                                <span class="font-medium">{{ reservation.created_at }}</span>
                            </div>
                            <div v-if="reservation.issued_at" class="flex justify-between">
                                <span class="text-gray-600">Issued:</span>
                                <span class="font-medium text-green-600">{{ reservation.issued_at }}</span>
                            </div>
                            <div v-if="reservation.returned_at" class="flex justify-between">
                                <span class="text-gray-600">Returned:</span>
                                <span class="font-medium text-blue-600">{{ reservation.returned_at }}</span>
                            </div>
                            <div v-if="reservation.notes" class="border-t pt-3">
                                <span class="text-gray-600 block mb-1">Notes:</span>
                                <span class="font-medium">{{ reservation.notes }}</span>
                            </div>
                        </div>

                        <!-- Equipment List -->
                        <div>
                            <h4 class="font-medium text-gray-900 mb-3">
                                Equipment ({{ reservation.total_items }} items)
                            </h4>
                            <div class="space-y-3 max-h-64 overflow-y-auto">
                                <div
                                    v-for="item in reservation.items"
                                    :key="item.id"
                                    class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg"
                                >
                                    <!-- Equipment Image -->
                                    <div class="flex-shrink-0 w-12 h-12 bg-white rounded-lg flex items-center justify-center border border-gray-200">
                                        <img
                                            v-if="item.equipment_image"
                                            :src="getEquipmentImageUrl(item.equipment_image)"
                                            :alt="item.equipment_name"
                                            class="max-w-full max-h-full object-contain"
                                        />
                                        <div v-else class="text-gray-400">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- Item Details -->
                                    <div class="flex-1 min-w-0">
                                        <h5 class="text-sm font-medium text-gray-900 truncate">{{ item.equipment_name }}</h5>
                                        <div class="flex items-center space-x-4 mt-1">
                                            <span class="text-sm text-blue-600">Qty: {{ item.quantity }}</span>
                                            <span :class="getStatusColor(item.status)" class="px-2 py-1 rounded-full text-xs font-medium">
                                                {{ item.status }}
                                            </span>
                                        </div>
                                        <div v-if="item.issued_at || item.returned_at" class="text-xs text-gray-500 mt-1">
                                            <span v-if="item.issued_at">Issued: {{ item.issued_at }}</span>
                                            <span v-if="item.returned_at"> • Returned: {{ item.returned_at }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Important Instructions -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mt-6">
                    <h3 class="text-lg font-semibold text-blue-900 mb-3">QR Code Instructions</h3>
                    <ul class="space-y-2 text-blue-800">
                        <li class="flex items-start">
                            <span class="text-blue-600 mr-2">•</span>
                            <span>Show this QR code to the admin/faculty when collecting your equipment.</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-blue-600 mr-2">•</span>
                            <span>Keep this QR code safe - you'll need it for equipment pickup and return.</span>
                        </li>
                        <li class="flex items-start">
                            <span class="text-blue-600 mr-2">•</span>
                            <span>Download this QR code as PNG image or take a screenshot - admins need PNG format to scan properly.</span>
                        </li>
                        <li v-if="reservation.status === 'pending'" class="flex items-start">
                            <span class="text-blue-600 mr-2">•</span>
                            <span>Your reservation is still pending approval. Wait for admin approval before pickup.</span>
                        </li>
                        <li v-if="reservation.status === 'issued'" class="flex items-start">
                            <span class="text-green-600 mr-2">•</span>
                            <span>Your equipment has been issued. Use this QR code when returning the equipment.</span>
                        </li>
                    </ul>
                </div>

                <!-- Navigation Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center mt-8 no-print">
                    <SecondaryButton @click="$inertia.visit(route('student.equipment.catalog'))" class="w-full sm:w-auto">
                        Browse Equipment
                    </SecondaryButton>
                    <PrimaryButton @click="$inertia.visit(route('student.dashboard'))" class="w-full sm:w-auto">
                        Go to Dashboard
                    </PrimaryButton>
                </div>

            </div>
        </div>
    </StudentLayout>
</template>

<style>
@media print {
    .no-print {
        display: none !important;
    }

    body {
        background: white !important;
    }

    .bg-gray-50, .bg-blue-50, .bg-green-50 {
        background-color: #f9f9f9 !important;
        -webkit-print-color-adjust: exact;
    }

    .shadow-sm {
        box-shadow: none !important;
    }
}
</style>