<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    equipment: {
        type: Object,
        default: null
    },
    availableEquipment: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['close', 'created']);

const form = useForm({
    equipment_id: '',
    reservation_date: '',
    start_time: '',
    end_time: '',
    purpose: ''
});

const reservedSlots = ref([]);
const isCheckingAvailability = ref(false);

// Reset form when modal closes
watch(() => props.show, (newValue) => {
    if (newValue) {
        // Pre-fill equipment if provided
        if (props.equipment) {
            form.equipment_id = props.equipment.id;
        }
        // Don't reset if equipment is pre-selected, just clear errors
        if (!props.equipment) {
            form.reset();
        }
        form.clearErrors();
        reservedSlots.value = [];
        console.log('Modal opened with equipment:', props.equipment);
    }
});

// Watch for equipment or date changes to check availability
watch([() => form.equipment_id, () => form.reservation_date], async ([equipmentId, date]) => {
    if (equipmentId && date) {
        await checkAvailability();
    } else {
        reservedSlots.value = [];
    }
});

const selectedEquipment = computed(() => {
    if (!form.equipment_id) return null;

    // Flatten all equipment from categories
    const allEquipment = props.availableEquipment.flatMap(category => category.equipment || []);
    return allEquipment.find(item => item.id == form.equipment_id);
});

const checkAvailability = async () => {
    if (!form.equipment_id || !form.reservation_date) return;

    isCheckingAvailability.value = true;
    try {
        const response = await fetch(route('reservations.availability'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                equipment_id: form.equipment_id,
                date: form.reservation_date
            })
        });

        if (response.ok) {
            const data = await response.json();
            reservedSlots.value = data.reserved_slots || [];
        }
    } catch (error) {
        console.error('Error checking availability:', error);
    } finally {
        isCheckingAvailability.value = false;
    }
};

const isTimeSlotAvailable = (startTime, endTime) => {
    if (!startTime || !endTime) return true;

    return !reservedSlots.value.some(slot => {
        return (startTime >= slot.start && startTime < slot.end) ||
               (endTime > slot.start && endTime <= slot.end) ||
               (startTime <= slot.start && endTime >= slot.end);
    });
};

const timeConflictMessage = computed(() => {
    if (!form.start_time || !form.end_time || isTimeSlotAvailable(form.start_time, form.end_time)) {
        return '';
    }
    return 'Selected time conflicts with existing reservations.';
});

const submitForm = () => {
    console.log('Submit form called');
    console.log('Form data:', form.data());

    if (!isTimeSlotAvailable(form.start_time, form.end_time)) {
        form.setError('start_time', 'Selected time conflicts with existing reservations.');
        return;
    }

    console.log('Submitting to route:', route('reservations.store'));
    form.post(route('reservations.store'), {
        onSuccess: () => {
            console.log('Reservation created successfully');
            emit('created');
            closeModal();
        },
        onError: (errors) => {
            console.error('Form submission errors:', errors);
        }
    });
};

const closeModal = () => {
    emit('close');
};

// Generate time options (8 AM to 6 PM in 30-minute intervals)
const timeOptions = computed(() => {
    const options = [];
    for (let hour = 8; hour <= 18; hour++) {
        for (let minute = 0; minute < 60; minute += 30) {
            const time = `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`;
            options.push(time);
        }
    }
    return options;
});

const minDate = computed(() => {
    const today = new Date();
    return today.toISOString().split('T')[0];
});
</script>

<template>
    <Modal :show="show" @close="closeModal" max-width="lg">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-900">
                    {{ equipment ? `Reserve ${equipment.name}` : 'Create New Reservation' }}
                </h2>
                <button
                    @click="closeModal"
                    class="text-gray-400 hover:text-gray-600 transition"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form @submit.prevent="submitForm" class="space-y-6">
                <!-- Equipment Selection -->
                <div v-if="!equipment">
                    <InputLabel for="equipment_id" value="Equipment" />
                    <select
                        id="equipment_id"
                        v-model="form.equipment_id"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        required
                    >
                        <option value="">Select Equipment</option>
                        <optgroup
                            v-for="category in availableEquipment"
                            :key="category.category"
                            :label="category.category"
                        >
                            <option
                                v-for="item in category.equipment"
                                :key="item.id"
                                :value="item.id"
                            >
                                {{ item.name }}
                            </option>
                        </optgroup>
                    </select>
                    <InputError class="mt-2" :message="form.errors.equipment_id" />
                </div>

                <!-- Selected Equipment Info -->
                <div v-if="selectedEquipment" class="bg-blue-50 p-4 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <img
                            :src="selectedEquipment.image ? `/storage/${selectedEquipment.image}` : '/images/equipment.png'"
                            :alt="selectedEquipment.name"
                            class="w-16 h-16 object-cover rounded-md"
                            @error="$event.target.src = '/images/equipment.png'"
                        />
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ selectedEquipment.name }}</h3>
                            <p class="text-sm text-gray-600">{{ selectedEquipment.description }}</p>
                            <p v-if="selectedEquipment.location" class="text-sm text-gray-500">
                                📍 {{ selectedEquipment.location }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Reservation Date -->
                <div>
                    <InputLabel for="reservation_date" value="Reservation Date" />
                    <TextInput
                        id="reservation_date"
                        type="date"
                        v-model="form.reservation_date"
                        :min="minDate"
                        class="mt-1 block w-full"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.reservation_date" />
                </div>

                <!-- Time Slots -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="start_time" value="Start Time" />
                        <select
                            id="start_time"
                            v-model="form.start_time"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            required
                        >
                            <option value="">Select Start Time</option>
                            <option
                                v-for="time in timeOptions"
                                :key="time"
                                :value="time"
                                :disabled="!isTimeSlotAvailable(time, form.end_time)"
                            >
                                {{ time }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.start_time" />
                    </div>

                    <div>
                        <InputLabel for="end_time" value="End Time" />
                        <select
                            id="end_time"
                            v-model="form.end_time"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            required
                        >
                            <option value="">Select End Time</option>
                            <option
                                v-for="time in timeOptions"
                                :key="time"
                                :value="time"
                                :disabled="!form.start_time || time <= form.start_time || !isTimeSlotAvailable(form.start_time, time)"
                            >
                                {{ time }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.end_time" />
                    </div>
                </div>

                <!-- Time Conflict Warning -->
                <div v-if="timeConflictMessage" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    <div class="flex">
                        <svg class="w-5 h-5 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm">{{ timeConflictMessage }}</span>
                    </div>
                </div>

                <!-- Reserved Time Slots -->
                <div v-if="reservedSlots.length > 0" class="bg-yellow-50 border border-yellow-200 p-4 rounded-lg">
                    <h4 class="text-sm font-medium text-yellow-800 mb-2">Already Reserved Times:</h4>
                    <div class="flex flex-wrap gap-2">
                        <span
                            v-for="slot in reservedSlots"
                            :key="`${slot.start}-${slot.end}`"
                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"
                        >
                            {{ slot.start }} - {{ slot.end }}
                        </span>
                    </div>
                </div>

                <!-- Purpose -->
                <div>
                    <InputLabel for="purpose" value="Purpose/Description" />
                    <textarea
                        id="purpose"
                        v-model="form.purpose"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        rows="3"
                        placeholder="Briefly describe what you'll use this equipment for..."
                        required
                    ></textarea>
                    <InputError class="mt-2" :message="form.errors.purpose" />
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                    <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                    <PrimaryButton
                        :disabled="form.processing || isCheckingAvailability || !!timeConflictMessage"
                        class="relative"
                    >
                        <span v-if="form.processing" class="inline-flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Creating...
                        </span>
                        <span v-else>Create Reservation</span>
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>
