<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage, useForm } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted, watch } from 'vue';
import axios from 'axios';
import { useRealTimeStats } from '@/composables/useRealTimeData';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const page = usePage();

// Reactive data for dashboard stats
const stats = ref([
    { name: 'Equipments', value: '0', icon: '📅', change: '', trend: 'up', color: 'bg-gradient-to-br from-blue-600 to-blue-700 border-blue-800' },
    { name: 'Requested Equipments', value: '0', icon: '✅', change: '', trend: 'up', color: 'bg-gradient-to-br from-amber-600 to-amber-700 border-amber-800' },
    { name: 'Issued Equipments', value: '0', icon: '🎉', change: '', trend: 'up', color: 'bg-gradient-to-br from-emerald-600 to-emerald-700 border-emerald-800' },
    { name: 'Students', value: '0', icon: '📊', change: '', trend: 'up', color: 'bg-gradient-to-br from-slate-600 to-slate-700 border-slate-800' },
]);

const recentReservations = ref([]);
const loading = ref(true);

// Modal states
const showStudentModal = ref(false);
const showDepartmentModal = ref(false);
const showEquipmentModal = ref(false);

// Department data for forms
const departments = ref([]);

// Forms for quick actions
const studentForm = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    department_id: '',
});

const departmentForm = useForm({
    name: '',
    code: '',
    description: '',
});

const equipmentForm = useForm({
    name: '',
    description: '',
    category: '',
    status: 'available',
    location: '',
    serial_number: '',
    image: null,
});

// Fetch dashboard data from API
const fetchDashboardData = async () => {
    try {
        loading.value = true;
        const response = await axios.get('/admin-dashboard/stats');
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

// Fetch departments for forms
const fetchDepartments = async () => {
    try {
        const response = await axios.get('/admin/departments/active');
        departments.value = response.data;
    } catch (error) {
        console.error('Error fetching departments:', error);
    }
};

// Real-time dashboard data
const { data: realTimeData } = useRealTimeStats(10000);

// Watch for real-time data changes and update stats
watch(realTimeData, (newData) => {
    if (newData && newData.stats) {
        stats.value[0].value = newData.stats.equipments.toString();
        stats.value[1].value = newData.stats.requested_equipments.toString();
        stats.value[2].value = newData.stats.issued_equipments.toString();
        stats.value[3].value = newData.stats.students.toString();

        if (newData.recent_reservations) {
            recentReservations.value = newData.recent_reservations;
        }

        loading.value = false;
    }
}, { immediate: false });

// Fetch data when component mounts
onMounted(() => {
    fetchDashboardData();
    fetchDepartments();
});

// Admin Quick actions for this dashboard
const quickActions = computed(() => [
    { name: 'QR Scanner', description: 'Scan QR codes for equipment management', icon: '📷', route: 'admin.qr-scanner' },
    { name: 'Add Student', description: 'Create new student account', icon: '👤', route: 'admin.students.create' },
    { name: 'Add Department', description: 'Create new department', icon: '🏢', route: 'admin.departments.create' },
    { name: 'Add Equipment', description: 'Add new equipment to inventory', icon: '/images/equipment.png', route: 'admin.equipment.create', isImage: true },
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

const handleQuickAction = (action) => {
    // Handle quick actions by opening modals or routing
    switch (action.route) {
        case 'admin.qr-scanner':
            router.visit(route('admin.qr-scanner'));
            break;
        case 'admin.students.create':
            openStudentModal();
            break;
        case 'admin.departments.create':
            openDepartmentModal();
            break;
        case 'admin.equipment.create':
            openEquipmentModal();
            break;
        default:
            // Fallback to routing for any other actions
            if (action.route) {
                router.visit(route(action.route));
            }
            break;
    }
};

// Modal handlers
const openStudentModal = () => {
    studentForm.reset();
    showStudentModal.value = true;
};

const openDepartmentModal = () => {
    departmentForm.reset();
    showDepartmentModal.value = true;
};

const openEquipmentModal = () => {
    equipmentForm.reset();
    showEquipmentModal.value = true;
};

const closeModals = () => {
    showStudentModal.value = false;
    showDepartmentModal.value = false;
    showEquipmentModal.value = false;
};

// Form submission handlers
const submitStudentForm = () => {
    studentForm.post(route('admin.students.store'), {
        onSuccess: () => {
            closeModals();
            studentForm.reset();
            fetchDashboardData(); // Refresh stats
        },
    });
};

const submitDepartmentForm = () => {
    departmentForm.post(route('admin.departments.store'), {
        onSuccess: () => {
            closeModals();
            departmentForm.reset();
            fetchDepartments(); // Refresh departments list
        },
    });
};

const submitEquipmentForm = () => {
    equipmentForm.post(route('admin.equipment.store'), {
        onSuccess: () => {
            closeModals();
            equipmentForm.reset();
            fetchDashboardData(); // Refresh stats
        },
    });
};

// Image upload handler for equipment
const handleImageUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        equipmentForm.image = file;
    }
};

// Navigate to admin reservations page when clicking on a reservation
const viewReservationDetails = (reservation) => {
    // Navigate to admin reservations page where this reservation can be managed
    router.visit(route('admin.reservations.index'));
};
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-900">
                        Admin Dashboard
                    </h2>
                    <p class="text-gray-600 mt-1">Welcome back! Here's what's happening.</p>
                </div>
            </div>
        </template>

        <div class="h-full flex flex-col overflow-hidden">
            <div class="px-4 sm:px-6 lg:px-8 py-4 sm:py-6 flex-1 overflow-y-auto">
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4 mb-4 sm:mb-6">
                    <div
                        v-for="stat in stats"
                        :key="stat.name"
                        :class="['overflow-hidden shadow-sm rounded-xl border hover:shadow-md transition duration-200', stat.color]"
                    >
                        <div class="p-4 sm:p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <span class="text-xl sm:text-2xl text-white">{{ stat.icon }}</span>
                                </div>
                                <div class="ml-3 sm:ml-4 w-full min-w-0">
                                    <div class="flex items-center justify-between">
                                        <p class="text-xs sm:text-sm font-medium text-white truncate">{{ stat.name }}</p>
                                        <span v-if="stat.change" class="text-yellow-200 text-xs font-medium">{{ stat.change }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <p v-if="!loading" class="text-xl sm:text-2xl font-bold text-white">{{ stat.value }}</p>
                                        <div v-else class="flex items-center">
                                            <div class="animate-spin rounded-full h-5 w-5 sm:h-6 sm:w-6 border-b-2 border-white"></div>
                                            <span class="ml-2 text-sm sm:text-lg text-white">Loading...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6">
                    <!-- Recent Reservations -->
                    <div class="lg:col-span-2">
                        <div class="bg-white border-gray-200 shadow-lg rounded-xl border hover:shadow-xl transition-all duration-300 h-[350px] sm:h-[400px] flex flex-col">
                            <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 bg-gradient-to-r from-slate-50 to-gray-50">
                                <h3 class="text-base sm:text-lg font-semibold text-slate-800">Recent Reservations</h3>
                            </div>
                            <div class="p-4 sm:p-6 flex-1 overflow-y-auto">
                                <div class="space-y-3 sm:space-y-4">
                                    <div v-if="loading" class="text-center py-6 sm:py-8 text-gray-500">
                                        <div class="animate-spin rounded-full h-6 w-6 sm:h-8 sm:w-8 border-b-2 border-blue-600 mx-auto"></div>
                                        <p class="mt-2 text-sm sm:text-base">Loading reservations...</p>
                                    </div>
                                    <div v-else-if="recentReservations.length > 0">
                                        <div
                                            v-for="reservation in recentReservations"
                                            :key="reservation.id"
                                            @click="viewReservationDetails(reservation)"
                                            class="flex items-center justify-between p-3 sm:p-4 bg-gray-50 rounded-lg hover:bg-blue-50 hover:border-blue-200 hover:shadow-lg transition-all duration-200 border border-gray-200 cursor-pointer group"
                                        >
                                            <div class="flex-1 min-w-0">
                                                <h4 class="font-medium text-gray-900 group-hover:text-blue-700 transition-colors duration-200 text-sm sm:text-base truncate">{{ reservation.name }}</h4>
                                                <p class="text-xs sm:text-sm text-gray-600 truncate">by {{ reservation.user }}</p>
                                                <p class="text-xs sm:text-sm text-gray-600">{{ reservation.date }} at {{ reservation.time }}</p>
                                            </div>
                                            <div class="flex items-center space-x-1 sm:space-x-2 flex-shrink-0 ml-2">
                                                <span
                                                    :class="getStatusColor(reservation.status)"
                                                    class="px-2 sm:px-3 py-1 rounded-full text-xs font-medium capitalize"
                                                >
                                                    {{ reservation.status }}
                                                </span>
                                                <!-- Click indicator -->
                                                <svg class="w-3 h-3 sm:w-4 sm:h-4 text-gray-400 group-hover:text-blue-500 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="text-center py-6 sm:py-8 text-gray-500">
                                        <p class="text-sm sm:text-base">No recent reservations found.</p>
                                    </div>
                                </div>
                                <div class="mt-4 sm:mt-6 text-center">
                                    <button
                                        @click="router.visit(route('admin.reservations.index'))"
                                        class="text-slate-600 hover:text-slate-800 font-medium transition duration-200 hover:underline text-sm sm:text-base"
                                    >
                                        View All Reservations
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div>
                        <div class="bg-white border-gray-200 shadow-lg rounded-xl border hover:shadow-xl transition-all duration-300 h-[350px] sm:h-[400px] flex flex-col">
                            <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
                                <h3 class="text-base sm:text-lg font-semibold text-slate-800">Quick Actions</h3>
                            </div>
                            <div class="p-3 sm:p-4 flex-1">
                                <div class="space-y-2">
                                    <button
                                        v-for="action in quickActions"
                                        :key="action.name"
                                        @click="handleQuickAction(action)"
                                        class="w-full text-left p-3 rounded-lg border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-all duration-200 group bg-white hover:shadow-sm"
                                    >
                                        <div class="flex items-center">
                                            <img v-if="action.isImage" :src="action.icon" :alt="action.name" class="w-5 h-5 sm:w-6 sm:h-6 mr-3 flex-shrink-0" />
                                            <span v-else class="text-lg sm:text-xl mr-3 flex-shrink-0">{{ action.icon }}</span>
                                            <div class="min-w-0 flex-1">
                                                <h4 class="font-medium text-gray-900 group-hover:text-blue-700 text-sm sm:text-base">{{ action.name }}</h4>
                                                <p class="text-xs sm:text-sm text-gray-600">{{ action.description }}</p>
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

        <!-- Add Student Modal -->
        <Modal :show="showStudentModal" @close="closeModals" max-width="lg">
            <div class="p-4 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-medium text-gray-900">Add New Student</h2>
                    <button
                        @click="closeModals"
                        class="md:hidden p-2 rounded-md text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="submitStudentForm">
                    <div class="space-y-3 sm:space-y-4 max-h-[60vh] overflow-y-auto">
                        <div>
                            <InputLabel for="name" value="Name" />
                            <TextInput
                                id="name"
                                v-model="studentForm.name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="studentForm.errors.name" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="email" value="Email" />
                            <TextInput
                                id="email"
                                v-model="studentForm.email"
                                type="email"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="studentForm.errors.email" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                            <div>
                                <InputLabel for="password" value="Password" />
                                <TextInput
                                    id="password"
                                    v-model="studentForm.password"
                                    type="password"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="studentForm.errors.password" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="password_confirmation" value="Confirm Password" />
                                <TextInput
                                    id="password_confirmation"
                                    v-model="studentForm.password_confirmation"
                                    type="password"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="studentForm.errors.password_confirmation" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <InputLabel for="department" value="Department (Optional)" />
                            <select
                                id="department"
                                v-model="studentForm.department_id"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">Select Department</option>
                                <option
                                    v-for="department in departments"
                                    :key="department.id"
                                    :value="department.id"
                                >
                                    {{ department.name }} {{ department.code ? `(${department.code})` : '' }}
                                </option>
                            </select>
                            <InputError :message="studentForm.errors.department_id" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-4 sm:mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                        <SecondaryButton type="button" @click="closeModals" class="w-full sm:w-auto">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton type="submit" :disabled="studentForm.processing" class="w-full sm:w-auto">
                            Create Student
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Add Department Modal -->
        <Modal :show="showDepartmentModal" @close="closeModals" max-width="md">
            <div class="p-4 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-medium text-gray-900">Add New Department</h2>
                    <button
                        @click="closeModals"
                        class="md:hidden p-2 rounded-md text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="submitDepartmentForm">
                    <div class="space-y-3 sm:space-y-4 max-h-[60vh] overflow-y-auto">
                        <div>
                            <InputLabel for="dept_name" value="Department Name" />
                            <TextInput
                                id="dept_name"
                                v-model="departmentForm.name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="departmentForm.errors.name" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="dept_code" value="Department Code (Optional)" />
                            <TextInput
                                id="dept_code"
                                v-model="departmentForm.code"
                                type="text"
                                class="mt-1 block w-full"
                                placeholder="e.g., CS, ENG, MATH"
                            />
                            <InputError :message="departmentForm.errors.code" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="dept_description" value="Description (Optional)" />
                            <textarea
                                id="dept_description"
                                v-model="departmentForm.description"
                                rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Brief description of the department"
                            ></textarea>
                            <InputError :message="departmentForm.errors.description" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-4 sm:mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                        <SecondaryButton type="button" @click="closeModals" class="w-full sm:w-auto">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton type="submit" :disabled="departmentForm.processing" class="w-full sm:w-auto">
                            Create Department
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Add Equipment Modal -->
        <Modal :show="showEquipmentModal" @close="closeModals" max-width="2xl">
            <div class="p-4 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-medium text-gray-900">Add New Equipment</h2>
                    <button
                        @click="closeModals"
                        class="md:hidden p-2 rounded-md text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="submitEquipmentForm" enctype="multipart/form-data">
                    <div class="space-y-4 max-h-[70vh] overflow-y-auto">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                            <div>
                                <InputLabel for="equip_name" value="Equipment Name" />
                                <TextInput
                                    id="equip_name"
                                    v-model="equipmentForm.name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="equipmentForm.errors.name" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="equip_category" value="Category" />
                                <TextInput
                                    id="equip_category"
                                    v-model="equipmentForm.category"
                                    type="text"
                                    class="mt-1 block w-full"
                                    placeholder="e.g., Microscope, Lab Equipment"
                                    required
                                />
                                <InputError :message="equipmentForm.errors.category" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="equip_status" value="Status" />
                                <select
                                    id="equip_status"
                                    v-model="equipmentForm.status"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="available">Available</option>
                                    <option value="unavailable">Unavailable</option>
                                    <option value="maintenance">Maintenance</option>
                                </select>
                                <InputError :message="equipmentForm.errors.status" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="equip_location" value="Location" />
                                <TextInput
                                    id="equip_location"
                                    v-model="equipmentForm.location"
                                    type="text"
                                    class="mt-1 block w-full"
                                    placeholder="e.g., Lab Room 101"
                                />
                                <InputError :message="equipmentForm.errors.location" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <InputLabel for="equip_serial" value="Serial Number" />
                            <TextInput
                                id="equip_serial"
                                v-model="equipmentForm.serial_number"
                                type="text"
                                class="mt-1 block w-full"
                                placeholder="Optional"
                            />
                            <InputError :message="equipmentForm.errors.serial_number" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="equip_description" value="Description" />
                            <textarea
                                id="equip_description"
                                v-model="equipmentForm.description"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                rows="3"
                                placeholder="Describe the equipment..."
                            ></textarea>
                            <InputError :message="equipmentForm.errors.description" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="equip_image" value="Equipment Image" />
                            <input
                                id="equip_image"
                                @change="handleImageUpload"
                                type="file"
                                accept="image/*"
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                            />
                            <p class="mt-1 text-sm text-gray-500">Choose an image file for the equipment (optional)</p>
                            <InputError :message="equipmentForm.errors.image" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-4 sm:mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                        <SecondaryButton type="button" @click="closeModals" class="w-full sm:w-auto">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton type="submit" :disabled="equipmentForm.processing" class="w-full sm:w-auto">
                            Add Equipment
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
