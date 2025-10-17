<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Checkbox from '@/Components/Checkbox.vue';

const props = defineProps({
    departments: Object,
    filters: Object,
    showCreateModal: {
        type: Boolean,
        default: false
    },
});

const showCreateModal = ref(props.showCreateModal);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const selectedDepartment = ref(null);
const searchQuery = ref('');
const perPage = ref(10);

// Forms
const createForm = useForm({
    name: '',
    code: '',
    description: '',
});

const editForm = useForm({
    name: '',
    code: '',
    description: '',
    is_active: true,
});

const deleteForm = useForm({});

// Computed properties
const filteredDepartments = computed(() => {
    if (!props.departments?.data) return [];
    return props.departments.data;
});

// Methods
const openCreateModal = () => {
    showCreateModal.value = true;
    createForm.reset();
};

const openEditModal = (department) => {
    selectedDepartment.value = department;
    editForm.name = department.name;
    editForm.code = department.code;
    editForm.description = department.description;
    editForm.is_active = department.is_active;
    showEditModal.value = true;
};

const openDeleteModal = (department) => {
    selectedDepartment.value = department;
    showDeleteModal.value = true;
};

const closeModals = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    showDeleteModal.value = false;
    selectedDepartment.value = null;
};

const createDepartment = () => {
    createForm.post(route('admin.departments.store'), {
        onSuccess: () => {
            closeModals();
            createForm.reset();
        },
    });
};

const updateDepartment = () => {
    editForm.put(route('admin.departments.update', selectedDepartment.value.id), {
        onSuccess: () => {
            closeModals();
        },
    });
};

const deleteDepartment = () => {
    deleteForm.delete(route('admin.departments.destroy', selectedDepartment.value.id), {
        onSuccess: () => {
            closeModals();
        },
    });
};

const searchDepartments = () => {
    router.get(route('admin.departments.index'), {
        search: searchQuery.value,
        per_page: perPage.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const changePerPage = () => {
    router.get(route('admin.departments.index'), {
        search: searchQuery.value,
        per_page: perPage.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Watch for changes
watch(searchQuery, () => {
    searchDepartments();
});

watch(perPage, () => {
    changePerPage();
});
</script>

<template>
    <Head title="Department Management" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold leading-tight text-gray-900">
                        Department Management
                    </h2>
                    <p class="text-gray-600 mt-1 text-sm sm:text-base">Manage academic departments and faculty information</p>
                </div>
                <PrimaryButton
                    @click="openCreateModal"
                    class="w-full sm:w-auto justify-center"
                >
                    Add New Department
                </PrimaryButton>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Search and Filters -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="flex items-center space-x-2">
                                    <label for="per-page" class="text-sm font-medium text-gray-700">
                                        Records per page:
                                    </label>
                                    <select
                                        id="per-page"
                                        v-model="perPage"
                                        class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="5">5</option>
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <label for="search" class="text-sm font-medium text-gray-700">
                                    Search:
                                </label>
                                <TextInput
                                    id="search"
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Search departments..."
                                    class="w-64"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Departments Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            #
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Name
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Code
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Description
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Students
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Action
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="(department, index) in filteredDepartments" :key="department.id"
                                        :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ (props.departments.current_page - 1) * props.departments.per_page + index + 1 }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ department.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span v-if="department.code" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ department.code }}
                                            </span>
                                            <span v-else class="text-gray-400">-</span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            <div class="max-w-xs truncate" :title="department.description">
                                                {{ department.description || 'No description' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span
                                                :class="department.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            >
                                                {{ department.is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ department.users_count || 0 }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <SecondaryButton @click="openEditModal(department)">
                                                    Edit
                                                </SecondaryButton>
                                                <DangerButton @click="openDeleteModal(department)">
                                                    Delete
                                                </DangerButton>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="props.departments.last_page > 1" class="mt-6 flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Showing {{ props.departments.from }} to {{ props.departments.to }} of {{ props.departments.total }} entries
                            </div>
                            <div class="flex space-x-2">
                                <SecondaryButton
                                    v-if="props.departments.prev_page_url"
                                    @click="router.get(props.departments.prev_page_url)"
                                    :disabled="!props.departments.prev_page_url"
                                >
                                    Previous
                                </SecondaryButton>
                                <span
                                    v-for="page in props.departments.links"
                                    :key="page.label"
                                    v-show="page.url && !page.label.includes('Previous') && !page.label.includes('Next')"
                                >
                                    <button
                                        v-if="page.url"
                                        @click="router.get(page.url)"
                                        :class="[
                                            'px-3 py-2 text-sm font-medium rounded-md',
                                            page.active
                                                ? 'bg-indigo-600 text-white'
                                                : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'
                                        ]"
                                    >
                                        {{ page.label }}
                                    </button>
                                </span>
                                <SecondaryButton
                                    v-if="props.departments.next_page_url"
                                    @click="router.get(props.departments.next_page_url)"
                                    :disabled="!props.departments.next_page_url"
                                >
                                    Next
                                </SecondaryButton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Department Modal -->
        <Modal :show="showCreateModal" @close="closeModals">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Add New Department</h2>
                <form @submit.prevent="createDepartment">
                    <div class="space-y-4">
                        <div>
                            <InputLabel for="name" value="Department Name" />
                            <TextInput
                                id="name"
                                v-model="createForm.name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="createForm.errors.name" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="code" value="Department Code (Optional)" />
                            <TextInput
                                id="code"
                                v-model="createForm.code"
                                type="text"
                                class="mt-1 block w-full"
                                placeholder="e.g., CS, ENG, MATH"
                            />
                            <InputError :message="createForm.errors.code" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="description" value="Description (Optional)" />
                            <textarea
                                id="description"
                                v-model="createForm.description"
                                rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Brief description of the department"
                            ></textarea>
                            <InputError :message="createForm.errors.description" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <SecondaryButton type="button" @click="closeModals">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton type="submit" :disabled="createForm.processing">
                            Create Department
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit Department Modal -->
        <Modal :show="showEditModal" @close="closeModals">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Edit Department</h2>
                <form @submit.prevent="updateDepartment">
                    <div class="space-y-4">
                        <div>
                            <InputLabel for="edit_name" value="Department Name" />
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
                            <InputLabel for="edit_code" value="Department Code (Optional)" />
                            <TextInput
                                id="edit_code"
                                v-model="editForm.code"
                                type="text"
                                class="mt-1 block w-full"
                                placeholder="e.g., CS, ENG, MATH"
                            />
                            <InputError :message="editForm.errors.code" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="edit_description" value="Description (Optional)" />
                            <textarea
                                id="edit_description"
                                v-model="editForm.description"
                                rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Brief description of the department"
                            ></textarea>
                            <InputError :message="editForm.errors.description" class="mt-2" />
                        </div>

                        <div class="flex items-center">
                            <Checkbox
                                id="edit_is_active"
                                v-model:checked="editForm.is_active"
                                name="is_active"
                            />
                            <InputLabel for="edit_is_active" value="Active" class="ml-2" />
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <SecondaryButton type="button" @click="closeModals">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton type="submit" :disabled="editForm.processing">
                            Update Department
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="closeModals">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Delete Department</h2>
                <p class="text-sm text-gray-600 mb-4">
                    Are you sure you want to delete <strong>{{ selectedDepartment?.name }}</strong>? This action cannot be undone.
                </p>

                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton type="button" @click="closeModals">
                        Cancel
                    </SecondaryButton>
                    <DangerButton @click="deleteDepartment" :disabled="deleteForm.processing">
                        Delete Department
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
