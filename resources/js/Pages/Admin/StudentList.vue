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

const props = defineProps({
    students: Object,
    filters: Object,
    departments: Array,
    showCreateModal: {
        type: Boolean,
        default: false
    },
});

const showCreateModal = ref(props.showCreateModal);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const selectedStudent = ref(null);
const searchQuery = ref('');
const perPage = ref(10);

// Forms
const createForm = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    department_id: '',
});

const editForm = useForm({
    name: '',
    email: '',
    department_id: '',
});

const deleteForm = useForm({});

// Computed properties
const filteredStudents = computed(() => {
    if (!props.students?.data) return [];
    return props.students.data;
});

// Methods
const openCreateModal = () => {
    showCreateModal.value = true;
    createForm.reset();
};

const openEditModal = (student) => {
    selectedStudent.value = student;
    editForm.name = student.name;
    editForm.email = student.email;
    editForm.department_id = student.department_id;
    showEditModal.value = true;
};

const openDeleteModal = (student) => {
    selectedStudent.value = student;
    showDeleteModal.value = true;
};

const closeModals = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    showDeleteModal.value = false;
    selectedStudent.value = null;
};

const createStudent = () => {
    createForm.post(route('admin.students.store'), {
        onSuccess: () => {
            closeModals();
            createForm.reset();
        },
    });
};

const updateStudent = () => {
    editForm.put(route('admin.students.update', selectedStudent.value.id), {
        onSuccess: () => {
            closeModals();
        },
    });
};

const deleteStudent = () => {
    deleteForm.delete(route('admin.students.destroy', selectedStudent.value.id), {
        onSuccess: () => {
            closeModals();
        },
    });
};

const searchStudents = () => {
    router.get(route('admin.students.index'), {
        search: searchQuery.value,
        per_page: perPage.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const changePerPage = () => {
    router.get(route('admin.students.index'), {
        search: searchQuery.value,
        per_page: perPage.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Watch for changes
watch(searchQuery, () => {
    searchStudents();
});

watch(perPage, () => {
    changePerPage();
});
</script>

<template>
    <Head title="Student Management" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-900">
                        Student Management
                    </h2>
                    <p class="text-gray-600 mt-1">Manage student accounts and information</p>
                </div>
                <PrimaryButton @click="openCreateModal">
                    Add New Student
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
                                    placeholder="Search students..."
                                    class="w-64"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Students Table -->
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
                                            Email
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Department
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            User Type
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Created At
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Action
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="(student, index) in filteredStudents" :key="student.id"
                                        :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ (props.students.current_page - 1) * props.students.per_page + index + 1 }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ student.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ student.email }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span v-if="student.department" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                {{ student.department.name }}
                                            </span>
                                            <span v-else class="text-gray-400">No Department</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ student.role }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ new Date(student.created_at).toLocaleDateString() }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <SecondaryButton @click="openEditModal(student)">
                                                    Edit
                                                </SecondaryButton>
                                                <DangerButton @click="openDeleteModal(student)">
                                                    Delete
                                                </DangerButton>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="props.students.last_page > 1" class="mt-6 flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Showing {{ props.students.from }} to {{ props.students.to }} of {{ props.students.total }} entries
                            </div>
                            <div class="flex space-x-2">
                                <SecondaryButton
                                    v-if="props.students.prev_page_url"
                                    @click="router.get(props.students.prev_page_url)"
                                    :disabled="!props.students.prev_page_url"
                                >
                                    Previous
                                </SecondaryButton>
                                <span
                                    v-for="page in props.students.links"
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
                                    v-if="props.students.next_page_url"
                                    @click="router.get(props.students.next_page_url)"
                                    :disabled="!props.students.next_page_url"
                                >
                                    Next
                                </SecondaryButton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Student Modal -->
        <Modal :show="showCreateModal" @close="closeModals">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Add New Student</h2>
                <form @submit.prevent="createStudent">
                    <div class="space-y-4">
                        <div>
                            <InputLabel for="name" value="Name" />
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
                            <InputLabel for="email" value="Email" />
                            <TextInput
                                id="email"
                                v-model="createForm.email"
                                type="email"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="createForm.errors.email" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="password" value="Password" />
                            <TextInput
                                id="password"
                                v-model="createForm.password"
                                type="password"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="createForm.errors.password" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="password_confirmation" value="Confirm Password" />
                            <TextInput
                                id="password_confirmation"
                                v-model="createForm.password_confirmation"
                                type="password"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="createForm.errors.password_confirmation" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="department" value="Department (Optional)" />
                            <select
                                id="department"
                                v-model="createForm.department_id"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">Select Department</option>
                                <option
                                    v-for="department in props.departments"
                                    :key="department.id"
                                    :value="department.id"
                                >
                                    {{ department.name }} {{ department.code ? `(${department.code})` : '' }}
                                </option>
                            </select>
                            <InputError :message="createForm.errors.department_id" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <SecondaryButton type="button" @click="closeModals">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton type="submit" :disabled="createForm.processing">
                            Create Student
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit Student Modal -->
        <Modal :show="showEditModal" @close="closeModals">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Edit Student</h2>
                <form @submit.prevent="updateStudent">
                    <div class="space-y-4">
                        <div>
                            <InputLabel for="edit_name" value="Name" />
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
                            <InputLabel for="edit_email" value="Email" />
                            <TextInput
                                id="edit_email"
                                v-model="editForm.email"
                                type="email"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="editForm.errors.email" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="edit_department" value="Department (Optional)" />
                            <select
                                id="edit_department"
                                v-model="editForm.department_id"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">Select Department</option>
                                <option
                                    v-for="department in props.departments"
                                    :key="department.id"
                                    :value="department.id"
                                >
                                    {{ department.name }} {{ department.code ? `(${department.code})` : '' }}
                                </option>
                            </select>
                            <InputError :message="editForm.errors.department_id" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <SecondaryButton type="button" @click="closeModals">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton type="submit" :disabled="editForm.processing">
                            Update Student
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="closeModals">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Delete Student</h2>
                <p class="text-sm text-gray-600 mb-4">
                    Are you sure you want to delete <strong>{{ selectedStudent?.name }}</strong>? This action cannot be undone.
                </p>

                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton type="button" @click="closeModals">
                        Cancel
                    </SecondaryButton>
                    <DangerButton @click="deleteStudent" :disabled="deleteForm.processing">
                        Delete Student
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
