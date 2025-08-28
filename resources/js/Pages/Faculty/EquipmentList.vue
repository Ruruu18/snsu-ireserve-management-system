<script setup>
import FacultyLayout from '@/Layouts/FacultyLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    equipment: Array,
    showAddModal: {
        type: Boolean,
        default: false
    },
});

// Reactive data
const showAddModal = ref(props.showAddModal);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const selectedEquipment = ref(null);
const searchTerm = ref('');
const selectedCategory = ref('');
const selectedStatus = ref('');

// Forms
const addForm = useForm({
    name: '',
    description: '',
    category: '',
    status: 'available',
    location: '',
    serial_number: '',
    image: null,
});

const editForm = useForm({
    name: '',
    description: '',
    category: '',
    status: '',
    location: '',
    serial_number: '',
    image: null,
});

// Computed properties
const filteredEquipment = computed(() => {
    return props.equipment.filter(equipment => {
        const matchesSearch = equipment.name.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
                            equipment.description?.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
                            equipment.serial_number?.toLowerCase().includes(searchTerm.value.toLowerCase());
        const matchesCategory = !selectedCategory.value || equipment.category === selectedCategory.value;
        const matchesStatus = !selectedStatus.value || equipment.status === selectedStatus.value;

        return matchesSearch && matchesCategory && matchesStatus;
    });
});

const categories = computed(() => {
    const cats = [...new Set(props.equipment.map(eq => eq.category))];
    return cats.sort();
});

const statuses = ['available', 'unavailable', 'maintenance'];

// Methods
const openAddModal = () => {
    addForm.reset();
    showAddModal.value = true;
};

const openEditModal = (equipment) => {
    selectedEquipment.value = equipment;
    editForm.name = equipment.name;
    editForm.description = equipment.description || '';
    editForm.category = equipment.category;
    editForm.status = equipment.status;
    editForm.location = equipment.location || '';
    editForm.serial_number = equipment.serial_number || '';
    editForm.image = null;
    showEditModal.value = true;
};

const openDeleteModal = (equipment) => {
    selectedEquipment.value = equipment;
    showDeleteModal.value = true;
};

const handleImageUpload = (event, form) => {
    const file = event.target.files[0];
    if (file) {
        form.image = file;
    }
};

const submitAdd = () => {
    addForm.post(route('faculty.equipment.store'), {
        forceFormData: true,
        onSuccess: () => {
            showAddModal.value = false;
            addForm.reset();
        },
    });
};

const submitEdit = () => {
    editForm.post(route('faculty.equipment.update', selectedEquipment.value.id), {
        forceFormData: true,
        onSuccess: () => {
            showEditModal.value = false;
            editForm.reset();
        },
    });
};

const submitDelete = () => {
    router.delete(route('faculty.equipment.destroy', selectedEquipment.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
        },
    });
};

const getStatusColor = (status) => {
    switch (status) {
        case 'available': return 'bg-green-100 text-green-800';
        case 'unavailable': return 'bg-red-100 text-red-800';
        case 'maintenance': return 'bg-yellow-100 text-yellow-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};

const getImageUrl = (imagePath) => {
    return imagePath ? `/storage/${imagePath}` : '/images/equipment.png';
};
</script>

<template>
    <Head title="Equipment Management" />

    <FacultyLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-900">
                        Equipment Management
                    </h2>
                    <p class="text-gray-600 mt-1">Manage science lab equipment inventory</p>
                </div>
                <PrimaryButton @click="openAddModal">
                    <span class="mr-2">➕</span>
                    Add Equipment
                </PrimaryButton>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <InputLabel for="search" value="Search Equipment" />
                                <TextInput
                                    id="search"
                                    v-model="searchTerm"
                                    type="text"
                                    placeholder="Search by name, description, or serial..."
                                    class="mt-1 block w-full"
                                />
                            </div>
                            <div>
                                <InputLabel for="category" value="Category" />
                                <select
                                    id="category"
                                    v-model="selectedCategory"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                >
                                    <option value="">All Categories</option>
                                    <option v-for="category in categories" :key="category" :value="category">
                                        {{ category }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <InputLabel for="status" value="Status" />
                                <select
                                    id="status"
                                    v-model="selectedStatus"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                >
                                    <option value="">All Statuses</option>
                                    <option v-for="status in statuses" :key="status" :value="status">
                                        {{ status.charAt(0).toUpperCase() + status.slice(1) }}
                                    </option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <SecondaryButton @click="searchTerm = ''; selectedCategory = ''; selectedStatus = '';">
                                    Clear Filters
                                </SecondaryButton>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Equipment Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <div
                        v-for="equipment in filteredEquipment"
                        :key="equipment.id"
                        class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow duration-200"
                    >
                        <div class="relative">
                            <img
                                :src="getImageUrl(equipment.image)"
                                :alt="equipment.name"
                                class="w-full h-48 object-cover"
                                @error="$event.target.src = '/images/equipment.png'"
                            />
                            <div class="absolute top-2 right-2">
                                <span :class="['px-2 py-1 rounded-full text-xs font-medium', getStatusColor(equipment.status)]">
                                    {{ equipment.status.charAt(0).toUpperCase() + equipment.status.slice(1) }}
                                </span>
                            </div>
                        </div>

                        <div class="p-4">
                            <h3 class="font-semibold text-lg text-gray-900 mb-2">{{ equipment.name }}</h3>
                            <p class="text-sm text-gray-600 mb-2">{{ equipment.category }}</p>
                            <p class="text-sm text-gray-500 mb-3 line-clamp-2">{{ equipment.description || 'No description' }}</p>

                            <div class="flex flex-col gap-2 text-xs text-gray-500">
                                <div v-if="equipment.location">
                                    <span class="font-medium">Location:</span> {{ equipment.location }}
                                </div>
                                <div v-if="equipment.serial_number">
                                    <span class="font-medium">Serial:</span> {{ equipment.serial_number }}
                                </div>
                            </div>

                            <div class="flex justify-between mt-4">
                                <SecondaryButton @click="openEditModal(equipment)" class="text-xs px-3 py-1">
                                    Edit
                                </SecondaryButton>
                                <DangerButton @click="openDeleteModal(equipment)" class="text-xs px-3 py-1">
                                    Delete
                                </DangerButton>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="filteredEquipment.length === 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-12 text-center">
                        <div class="mb-4">
                            <img src="/images/equipment.png" alt="Equipment" class="w-16 h-16 mx-auto opacity-30" />
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No equipment found</h3>
                        <p class="text-gray-500 mb-4">Get started by adding your first piece of equipment.</p>
                        <PrimaryButton @click="openAddModal">Add Equipment</PrimaryButton>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Equipment Modal -->
        <Modal :show="showAddModal" @close="showAddModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Add New Equipment</h2>

                <form @submit.prevent="submitAdd" enctype="multipart/form-data">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="add_name" value="Equipment Name" />
                            <TextInput
                                id="add_name"
                                v-model="addForm.name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="addForm.errors.name" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="add_category" value="Category" />
                            <TextInput
                                id="add_category"
                                v-model="addForm.category"
                                type="text"
                                class="mt-1 block w-full"
                                placeholder="e.g., Microscope, Lab Equipment"
                                required
                            />
                            <InputError :message="addForm.errors.category" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="add_status" value="Status" />
                            <select
                                id="add_status"
                                v-model="addForm.status"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required
                            >
                                <option value="available">Available</option>
                                <option value="unavailable">Unavailable</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                            <InputError :message="addForm.errors.status" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="add_location" value="Location" />
                            <TextInput
                                id="add_location"
                                v-model="addForm.location"
                                type="text"
                                class="mt-1 block w-full"
                                placeholder="e.g., Lab Room 101"
                            />
                            <InputError :message="addForm.errors.location" class="mt-2" />
                        </div>

                        <div class="md:col-span-2">
                            <InputLabel for="add_serial" value="Serial Number" />
                            <TextInput
                                id="add_serial"
                                v-model="addForm.serial_number"
                                type="text"
                                class="mt-1 block w-full"
                                placeholder="Optional"
                            />
                            <InputError :message="addForm.errors.serial_number" class="mt-2" />
                        </div>

                        <div class="md:col-span-2">
                            <InputLabel for="add_description" value="Description" />
                            <textarea
                                id="add_description"
                                v-model="addForm.description"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                rows="3"
                                placeholder="Describe the equipment..."
                            ></textarea>
                            <InputError :message="addForm.errors.description" class="mt-2" />
                        </div>

                        <div class="md:col-span-2">
                            <InputLabel for="add_image" value="Equipment Image" />
                            <input
                                id="add_image"
                                type="file"
                                accept="image/*"
                                @change="handleImageUpload($event, addForm)"
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                            />
                            <InputError :message="addForm.errors.image" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex justify-end mt-6 space-x-3">
                        <SecondaryButton @click="showAddModal = false">Cancel</SecondaryButton>
                        <PrimaryButton type="submit" :disabled="addForm.processing">
                            {{ addForm.processing ? 'Adding...' : 'Add Equipment' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit Equipment Modal -->
        <Modal :show="showEditModal" @close="showEditModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Edit Equipment</h2>

                <form @submit.prevent="submitEdit" enctype="multipart/form-data">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="edit_name" value="Equipment Name" />
                            <TextInput
                                id="edit_name"
                                v-model="editForm.name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="editForm.errors.name" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="edit_category" value="Category" />
                            <TextInput
                                id="edit_category"
                                v-model="editForm.category"
                                type="text"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="editForm.errors.category" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="edit_status" value="Status" />
                            <select
                                id="edit_status"
                                v-model="editForm.status"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required
                            >
                                <option value="available">Available</option>
                                <option value="unavailable">Unavailable</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                            <InputError :message="editForm.errors.status" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="edit_location" value="Location" />
                            <TextInput
                                id="edit_location"
                                v-model="editForm.location"
                                type="text"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="editForm.errors.location" class="mt-2" />
                        </div>

                        <div class="md:col-span-2">
                            <InputLabel for="edit_serial" value="Serial Number" />
                            <TextInput
                                id="edit_serial"
                                v-model="editForm.serial_number"
                                type="text"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="editForm.errors.serial_number" class="mt-2" />
                        </div>

                        <div class="md:col-span-2">
                            <InputLabel for="edit_description" value="Description" />
                            <textarea
                                id="edit_description"
                                v-model="editForm.description"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                rows="3"
                            ></textarea>
                            <InputError :message="editForm.errors.description" class="mt-2" />
                        </div>

                        <div class="md:col-span-2">
                            <InputLabel for="edit_image" value="Equipment Image" />
                            <input
                                id="edit_image"
                                type="file"
                                accept="image/*"
                                @change="handleImageUpload($event, editForm)"
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                            />
                            <p class="text-sm text-gray-500 mt-1">Leave empty to keep current image</p>
                            <InputError :message="editForm.errors.image" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex justify-end mt-6 space-x-3">
                        <SecondaryButton @click="showEditModal = false">Cancel</SecondaryButton>
                        <PrimaryButton type="submit" :disabled="editForm.processing">
                            {{ editForm.processing ? 'Updating...' : 'Update Equipment' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Delete Equipment</h2>
                <p class="text-gray-600 mb-4">
                    Are you sure you want to delete "{{ selectedEquipment?.name }}"? This action cannot be undone.
                </p>
                <div class="flex justify-end space-x-3">
                    <SecondaryButton @click="showDeleteModal = false">Cancel</SecondaryButton>
                    <DangerButton @click="submitDelete">Delete Equipment</DangerButton>
                </div>
            </div>
        </Modal>
    </FacultyLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
