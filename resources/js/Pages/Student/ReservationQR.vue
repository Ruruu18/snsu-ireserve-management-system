<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
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

// Download QR Code
const downloadQR = () => {
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    const img = new Image();

    img.onload = function() {
        canvas.width = img.width;
        canvas.height = img.height;
        ctx.drawImage(img, 0, 0);

        const link = document.createElement('a');
        link.download = `reservation-${props.reservation.id}-qr.png`;
        link.href = canvas.toDataURL();
        link.click();
    };

    img.src = props.qrCode;
};

// Print QR Code
const printQR = () => {
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Equipment Reservation QR Code</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        text-align: center;
                        padding: 20px;
                    }
                    .qr-container {
                        margin: 20px auto;
                        max-width: 400px;
                    }
                    .reservation-details {
                        text-align: left;
                        margin: 20px 0;
                        padding: 15px;
                        border: 1px solid #ddd;
                        border-radius: 8px;
                    }
                    .detail-row {
                        margin: 8px 0;
                    }
                    .label {
                        font-weight: bold;
                        display: inline-block;
                        width: 120px;
                    }
                </style>
            </head>
            <body>
                <h2>SNSU iReserve - Equipment Reservation</h2>
                <div class="qr-container">
                    <img src="${props.qrCode}" alt="Reservation QR Code" style="max-width: 100%; height: auto;">
                </div>
                <div class="reservation-details">
                    <div class="detail-row">
                        <span class="label">Reservation ID:</span>
                        <span>${props.reservation.id}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Student:</span>
                        <span>${props.reservation.student_name}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Purpose:</span>
                        <span>${props.reservation.purpose}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Date & Time:</span>
                        <span>${props.reservation.date}, ${props.reservation.start_time} - ${props.reservation.end_time}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Items:</span>
                        <span>${props.reservation.items.length} equipment items</span>
                    </div>
                </div>
                <p style="font-size: 12px; color: #666; margin-top: 30px;">
                    Present this QR code to the lab administrator for equipment collection.
                </p>
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
};
</script>

<template>
    <Head title="Reservation Confirmed" />

    <StudentLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold leading-tight text-gray-900">
                        ✅ Reservation Confirmed
                    </h2>
                    <p class="text-gray-600 mt-1 text-sm sm:text-base">Your equipment reservation has been successfully created.</p>
                </div>
            </div>
        </template>

        <div class="py-4 sm:py-6 pb-8 sm:pb-12">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Success Message -->
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800">Reservation Created Successfully!</h3>
                            <p class="mt-1 text-sm text-green-700">
                                Your reservation #{{ reservation.id }} has been submitted. Present the QR code below to collect your equipment.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- QR Code Section -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 text-center">Your QR Code</h3>

                        <div class="text-center">
                            <div class="inline-block p-6 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                                <img
                                    :src="qrCode"
                                    alt="Reservation QR Code"
                                    class="w-48 h-48 mx-auto"
                                />
                            </div>

                            <p class="text-sm text-gray-600 mt-4 mb-6">
                                Present this QR code to the lab administrator for equipment collection.
                            </p>

                            <!-- QR Actions -->
                            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3 justify-center">
                                <SecondaryButton @click="downloadQR" class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                                    </svg>
                                    Download QR
                                </SecondaryButton>

                                <SecondaryButton @click="printQR" class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                    </svg>
                                    Print QR
                                </SecondaryButton>
                            </div>
                        </div>
                    </div>

                    <!-- Reservation Details -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Reservation Details</h3>

                        <div class="space-y-4">
                            <div class="flex justify-between items-start">
                                <span class="text-sm font-medium text-gray-600">Reservation ID:</span>
                                <span class="text-sm text-gray-900 font-mono">#{{ reservation.id }}</span>
                            </div>

                            <div class="flex justify-between items-start">
                                <span class="text-sm font-medium text-gray-600">Status:</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Pending Approval
                                </span>
                            </div>

                            <div class="flex justify-between items-start">
                                <span class="text-sm font-medium text-gray-600">Purpose:</span>
                                <span class="text-sm text-gray-900 text-right">{{ reservation.purpose }}</span>
                            </div>

                            <div class="flex justify-between items-start">
                                <span class="text-sm font-medium text-gray-600">Date:</span>
                                <span class="text-sm text-gray-900">{{ reservation.date }}</span>
                            </div>

                            <div class="flex justify-between items-start">
                                <span class="text-sm font-medium text-gray-600">Time:</span>
                                <span class="text-sm text-gray-900">{{ reservation.start_time }} - {{ reservation.end_time }}</span>
                            </div>

                            <div v-if="reservation.notes" class="flex justify-between items-start">
                                <span class="text-sm font-medium text-gray-600">Notes:</span>
                                <span class="text-sm text-gray-900 text-right">{{ reservation.notes }}</span>
                            </div>
                        </div>

                        <!-- Equipment Items -->
                        <div class="mt-6">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Equipment Items ({{ reservation.items.length }})</h4>
                            <div class="space-y-3">
                                <div
                                    v-for="item in reservation.items"
                                    :key="item.id"
                                    class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg"
                                >
                                    <div class="flex-shrink-0 w-10 h-10 bg-white rounded border border-gray-200 flex items-center justify-center">
                                        <img
                                            v-if="item.equipment_image"
                                            :src="getEquipmentImageUrl(item.equipment_image)"
                                            :alt="item.equipment_name"
                                            class="max-w-full max-h-full object-contain"
                                        />
                                        <div v-else class="text-gray-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ item.equipment_name }}</p>
                                        <p class="text-xs text-gray-500">Qty: {{ item.quantity }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Next Steps -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">What happens next?</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ol class="list-decimal list-inside space-y-1">
                                    <li>Lab administrator will review and approve your reservation</li>
                                    <li>Present this QR code at the lab to collect your equipment</li>
                                    <li>Equipment must be returned by the specified end time</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row items-center justify-center space-y-3 sm:space-y-0 sm:space-x-4 mt-8">
                    <Link
                        :href="route('student.equipment.requested')"
                        class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-sm text-gray-700 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition ease-in-out duration-150 w-full sm:w-auto justify-center"
                    >
                        View My Reservations
                    </Link>

                    <Link
                        :href="route('student.equipment.catalog')"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150 w-full sm:w-auto justify-center"
                    >
                        Browse More Equipment
                    </Link>
                </div>
            </div>
        </div>
    </StudentLayout>
</template>