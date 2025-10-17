<script setup>
import FacultyLayout from '@/Layouts/FacultyLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import jsQR from 'jsqr';

const props = defineProps({
    recentScans: {
        type: Array,
        default: () => []
    },
    reservation: {
        type: Object,
        default: null
    },
    message: {
        type: String,
        default: null
    },
    uploaded_qr: {
        type: Object,
        default: null
    },
    errors: {
        type: Object,
        default: () => ({})
    }
});

// State
const video = ref(null);
const canvas = ref(null);
const stream = ref(null);
const isScanning = ref(false);
const isDetecting = ref(false);
const scanResult = ref(null);
const error = ref(null);
const selectedReservation = ref(null);
const scanningInterval = ref(null);

// Form for QR scanning
const scanForm = useForm({
    qr_data: ''
});

// Form for file upload
const uploadForm = useForm({
    qr_image: null
});

// Camera functions
const startCamera = async () => {
    try {
        error.value = null;
        const constraints = {
            video: {
                facingMode: 'environment',
                width: { ideal: 640 },
                height: { ideal: 480 }
            }
        };

        stream.value = await navigator.mediaDevices.getUserMedia(constraints);
        if (video.value) {
            video.value.srcObject = stream.value;
            video.value.play();
            isScanning.value = true;
            startDetection();
        }
    } catch (err) {
        error.value = 'Failed to start camera: ' + err.message;
        console.error('Camera error:', err);
    }
};

const stopCamera = () => {
    if (stream.value) {
        stream.value.getTracks().forEach(track => track.stop());
        stream.value = null;
    }

    if (scanningInterval.value) {
        clearInterval(scanningInterval.value);
        scanningInterval.value = null;
    }

    isScanning.value = false;
    isDetecting.value = false;
    scanResult.value = null;
};

const startDetection = () => {
    if (!video.value || !canvas.value) return;

    const ctx = canvas.value.getContext('2d');
    isDetecting.value = true;

    scanningInterval.value = setInterval(() => {
        if (video.value.readyState === video.value.HAVE_ENOUGH_DATA) {
            canvas.value.width = video.value.videoWidth;
            canvas.value.height = video.value.videoHeight;

            ctx.drawImage(video.value, 0, 0, canvas.value.width, canvas.value.height);

            const imageData = ctx.getImageData(0, 0, canvas.value.width, canvas.value.height);
            const code = jsQR(imageData.data, imageData.width, imageData.height);

            if (code) {
                scanResult.value = code.data;
                processQRCode(code.data);
                stopCamera();
            }
        }
    }, 100);
};

const processQRCode = (qrData) => {
    scanForm.qr_data = qrData;
    scanForm.post(route('faculty.qr-scanner.scan'), {
        preserveState: true,
        onSuccess: () => {
            scanForm.reset();
        },
        onError: (errors) => {
            error.value = errors.message || 'Error processing QR code';
        }
    });
};

// File upload handler
const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        uploadForm.qr_image = file;
        uploadForm.post(route('faculty.qr-scanner.upload'), {
            preserveState: true,
            onSuccess: () => {
                uploadForm.reset();
                event.target.value = '';
            },
            onError: (errors) => {
                error.value = errors.message || 'Error processing uploaded image';
            }
        });
    }
};

// Action button methods
const loading = ref(false);

const approveReservation = () => {
    const reservation = props.reservation || selectedReservation.value;
    if (!reservation) return;

    loading.value = true;
    scanForm.post(route('faculty.reservations.approve', reservation.id), {
        preserveState: true,
        onSuccess: () => {
            selectedReservation.value = null;
        },
        onError: (errors) => {
            error.value = errors.message || 'Error approving reservation';
        },
        onFinish: () => {
            loading.value = false;
        }
    });
};

const issueEquipment = () => {
    const reservation = props.reservation || selectedReservation.value;
    if (!reservation) return;

    loading.value = true;
    scanForm.post(route('faculty.reservations.issue', reservation.id), {
        preserveState: true,
        onSuccess: () => {
            selectedReservation.value = null;
        },
        onError: (errors) => {
            error.value = errors.message || 'Error issuing equipment';
        },
        onFinish: () => {
            loading.value = false;
        }
    });
};

const returnEquipment = () => {
    const reservation = props.reservation || selectedReservation.value;
    if (!reservation) return;

    loading.value = true;
    scanForm.patch(route('faculty.reservations.return', reservation.id), {}, {
        preserveState: true,
        onSuccess: () => {
            selectedReservation.value = null;
        },
        onError: (errors) => {
            error.value = errors.message || 'Error returning equipment';
        },
        onFinish: () => {
            loading.value = false;
        }
    });
};

const rejectReservation = () => {
    const reservation = props.reservation || selectedReservation.value;
    if (!reservation) return;

    loading.value = true;
    scanForm.post(route('faculty.reservations.reject', reservation.id), {
        preserveState: true,
        onSuccess: () => {
            selectedReservation.value = null;
        },
        onError: (errors) => {
            error.value = errors.message || 'Error rejecting reservation';
        },
        onFinish: () => {
            loading.value = false;
        }
    });
};

// Set initial state from props
onMounted(() => {
    if (props.reservation) {
        selectedReservation.value = props.reservation;
    }
});

// Cleanup on unmount
onUnmounted(() => {
    stopCamera();
});

const getStatusColor = (status) => {
    switch (status) {
        case 'pending':
            return 'text-yellow-600 bg-yellow-100';
        case 'approved':
            return 'text-green-600 bg-green-100';
        case 'issued':
            return 'text-blue-600 bg-blue-100';
        case 'completed':
            return 'text-gray-600 bg-gray-100';
        case 'cancelled':
            return 'text-red-600 bg-red-100';
        default:
            return 'text-gray-600 bg-gray-100';
    }
};
</script>

<template>
    <Head title="QR Code Scanner" />

    <FacultyLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                QR Code Scanner
            </h2>
            <p class="text-gray-600 mt-1">Scan student QR codes to issue or return equipment.</p>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Success Message -->
                <div v-if="props.message" class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ props.message }}
                </div>

                <!-- Error Message -->
                <div v-if="props.errors?.message || error" class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ props.errors?.message || error }}
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- QR Scanner Section -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">QR Code Scanner</h3>

                        <!-- Camera Controls -->
                        <div class="mb-4">
                            <PrimaryButton v-if="!isScanning" @click="startCamera" class="mr-3">
                                START CAMERA & DETECTION
                            </PrimaryButton>
                            <SecondaryButton v-if="isScanning" @click="stopCamera">
                                Stop Camera
                            </SecondaryButton>
                        </div>

                        <!-- Camera Preview -->
                        <div class="relative mb-4" v-if="isScanning">
                            <video ref="video" class="w-full max-w-md border rounded" autoplay playsinline></video>
                            <canvas ref="canvas" class="hidden"></canvas>
                            <div v-if="isDetecting" class="absolute top-2 left-2 bg-green-500 text-white px-2 py-1 rounded text-sm">
                                🔍 Scanning...
                            </div>
                        </div>

                        <!-- Manual QR Preview -->
                        <div v-if="!isScanning" class="bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg p-8 text-center mb-4">
                            <div class="text-gray-400 text-6xl mb-4">
                                <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-500">Click "Start Camera & Detection" to begin scanning</p>
                        </div>

                        <!-- File Upload Section -->
                        <div class="border-t pt-4">
                            <h4 class="font-medium text-gray-900 mb-3">Upload QR Code Image</h4>
                            <div class="flex items-center space-x-3">
                                <input
                                    type="file"
                                    accept="image/*"
                                    @change="handleFileUpload"
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                    :disabled="uploadForm.processing"
                                />
                                <PrimaryButton
                                    v-if="uploadForm.processing"
                                    disabled
                                    class="px-6"
                                >
                                    UPLOAD QR
                                </PrimaryButton>
                            </div>
                            <p class="text-sm text-gray-500 mt-2">Choose an image file containing a QR code</p>
                        </div>
                    </div>

                    <!-- Scanned Reservation Details -->
                    <div class="bg-white rounded-lg shadow p-6" v-if="props.reservation || selectedReservation">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Scanned Reservation</h3>

                        <div class="space-y-4">
                            <div class="border rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-medium">Reservation Code:</span>
                                    <code class="bg-gray-100 px-2 py-1 rounded text-sm">
                                        {{ (props.reservation || selectedReservation)?.reservation_code }}
                                    </code>
                                </div>

                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="font-medium text-gray-600">Student:</span>
                                        <p>{{ (props.reservation || selectedReservation)?.user?.name }}</p>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-600">Status:</span>
                                        <span :class="getStatusColor((props.reservation || selectedReservation)?.status)"
                                              class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                            {{ (props.reservation || selectedReservation)?.status?.toUpperCase() }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-600">Date:</span>
                                        <p>{{ (props.reservation || selectedReservation)?.reservation_date }}</p>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-600">Time:</span>
                                        <p>{{ (props.reservation || selectedReservation)?.start_time }} - {{ (props.reservation || selectedReservation)?.end_time }}</p>
                                    </div>
                                </div>

                                <!-- Equipment Items -->
                                <div v-if="(props.reservation || selectedReservation)?.items" class="mt-4">
                                    <span class="font-medium text-gray-600">Equipment:</span>
                                    <ul class="mt-2 space-y-1">
                                        <li v-for="item in (props.reservation || selectedReservation).items" :key="item.id"
                                            class="flex justify-between text-sm bg-gray-50 px-3 py-2 rounded">
                                            <span>{{ item.equipment?.name }}</span>
                                            <span class="text-gray-500">Qty: {{ item.quantity }}</span>
                                        </li>
                                    </ul>
                                </div>

                                <!-- Action Buttons based on status -->
                                <div class="mt-4 flex space-x-2">
                                    <PrimaryButton
                                        v-if="(props.reservation || selectedReservation)?.status === 'pending'"
                                        @click="approveReservation"
                                        :disabled="loading"
                                        class="flex-1 bg-green-600 hover:bg-green-700"
                                    >
                                        Approve Request
                                    </PrimaryButton>
                                    <PrimaryButton
                                        v-if="(props.reservation || selectedReservation)?.status === 'approved'"
                                        @click="issueEquipment"
                                        :disabled="loading"
                                        class="flex-1 bg-blue-600 hover:bg-blue-700"
                                    >
                                        Issue Equipment
                                    </PrimaryButton>
                                    <PrimaryButton
                                        v-if="(props.reservation || selectedReservation)?.status === 'issued'"
                                        @click="returnEquipment"
                                        :disabled="loading"
                                        class="flex-1 bg-orange-600 hover:bg-orange-700"
                                    >
                                        Mark as Returned
                                    </PrimaryButton>
                                    <PrimaryButton
                                        v-if="(props.reservation || selectedReservation)?.status === 'pending'"
                                        @click="rejectReservation"
                                        :disabled="loading"
                                        class="flex-1 bg-red-600 hover:bg-red-700 border-red-600 hover:border-red-700"
                                    >
                                        Reject Request
                                    </PrimaryButton>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Equipment Issues -->
                <div class="mt-8 bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Recent Equipment Issues</h3>
                    </div>
                    <div class="p-6">
                        <div v-if="props.recentScans && props.recentScans.length > 0" class="space-y-3">
                            <div v-for="scan in props.recentScans" :key="scan.id"
                                 class="flex items-center justify-between p-3 bg-gray-50 rounded">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ scan.user }}</p>
                                            <p class="text-sm text-gray-600">{{ scan.equipment }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <div class="text-right">
                                        <p class="text-sm text-gray-900">{{ scan.date }}</p>
                                        <p class="text-xs text-gray-500">{{ scan.action }}</p>
                                    </div>
                                    <span :class="getStatusColor(scan.status)"
                                          class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                        {{ scan.status.toUpperCase() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-gray-500">
                            <div class="text-4xl mb-2">📱</div>
                            <p>No recent equipment issues found.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </FacultyLayout>
</template>