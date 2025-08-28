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
    users: Object,
    filters: Object,
    departments: Array,
});

const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const showPromoteModal = ref(false);
const showDemoteModal = ref(false);
const selectedUser = ref(null);
const searchQuery = ref(props.filters?.search || '');
const roleFilter = ref(props.filters?.role || '');
const perPage = ref(props.filters?.per_page || 10);



// Forms
const createForm = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'student',
    department_id: '',
});

const editForm = useForm({
    name: '',
    email: '',
    role: '',
    department_id: '',
});

const deleteForm = useForm({});
const promoteForm = useForm({});
const demoteForm = useForm({});

// Computed properties
const filteredUsers = computed(() => {
    if (!props.users?.data) return [];
    return props.users.data;
});

const userStats = computed(() => {
    const total = props.users?.total || 0;
    const students = filteredUsers.value.filter(user => user.role === 'student').length;
    const facultyStaff = filteredUsers.value.filter(user => user.role === 'faculty_staff').length;
    return { total, students, facultyStaff };
});

// Methods
const openCreateModal = () => {
    showCreateModal.value = true;
    createForm.reset();
};

const openEditModal = (user) => {
    selectedUser.value = user;
    editForm.name = user.name;
    editForm.email = user.email;
    editForm.role = user.role;
    editForm.department_id = user.department_id;
    showEditModal.value = true;
};

const openDeleteModal = (user) => {
    selectedUser.value = user;
    showDeleteModal.value = true;
};

const openPromoteModal = (user) => {
    selectedUser.value = user;
    showPromoteModal.value = true;
};

const openDemoteModal = (user) => {
    selectedUser.value = user;
    showDemoteModal.value = true;
};

const closeModals = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    showDeleteModal.value = false;
    showPromoteModal.value = false;
    showDemoteModal.value = false;
    selectedUser.value = null;
};

const createUser = () => {
    createForm.post(route('admin.users.store'), {
        onSuccess: () => {
            closeModals();
            createForm.reset();
        },
    });
};

const updateUser = () => {
    editForm.put(route('admin.users.update', selectedUser.value.id), {
        onSuccess: () => {
            closeModals();
        },
    });
};

const deleteUser = () => {
    deleteForm.delete(route('admin.users.destroy', selectedUser.value.id), {
        onSuccess: () => {
            closeModals();
        },
    });
};

const promoteUser = () => {
    promoteForm.post(route('admin.users.promote', selectedUser.value.id), {
        onSuccess: () => {
            closeModals();
        },
    });
};

const demoteUser = () => {
    demoteForm.post(route('admin.users.demote', selectedUser.value.id), {
        onSuccess: () => {
            closeModals();
        },
    });
};

const searchUsers = () => {
    router.get(route('admin.users.index'), {
        search: searchQuery.value,
        role: roleFilter.value,
        per_page: perPage.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const changeFilter = () => {
    router.get(route('admin.users.index'), {
        search: searchQuery.value,
        role: roleFilter.value,
        per_page: perPage.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const getRoleBadgeClass = (role) => {
    switch (role) {
        case 'student':
            return 'bg-blue-100 text-blue-800';
        case 'faculty_staff':
            return 'bg-green-100 text-green-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const formatRoleName = (role) => {
    return role.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
};

// Watch for changes
watch([searchQuery, roleFilter, perPage], () => {
    changeFilter();
}, { deep: true });
</script>

<template>
    <Head title="Manage All Users" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-900">
                        Manage All Users
                    </h2>
                    <p class="text-gray-600 mt-1">Manage all system users (Students and Faculty Staff)</p>
                </div>
                <PrimaryButton @click="openCreateModal">
                    Add New User
                </PrimaryButton>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- User Statistics -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="text-2xl">👥</div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500">Total Users</div>
                                    <div class="text-2xl font-bold text-gray-900">{{ props.users?.total || 0 }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="text-2xl">🎓</div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500">Students</div>
                                    <div class="text-2xl font-bold text-blue-600">{{ filteredUsers.filter(u => u.role === 'student').length }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="text-2xl">👨‍🏫</div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-500">Faculty Staff</div>
                                    <div class="text-2xl font-bold text-green-600">{{ filteredUsers.filter(u => u.role === 'faculty_staff').length }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search and Filters -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="flex flex-col lg:flex-row gap-4 items-center justify-between">
                            <div class="flex flex-col sm:flex-row items-center space-y-2 sm:space-y-0 sm:space-x-4">
                                <div class="flex items-center space-x-2">
                                    <label for="per-page" class="text-sm font-medium text-gray-700 whitespace-nowrap">
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

                                <div class="flex items-center space-x-2">
                                    <label for="role-filter" class="text-sm font-medium text-gray-700 whitespace-nowrap">
                                        Filter by role:
                                    </label>
                                    <select
                                        id="role-filter"
                                        v-model="roleFilter"
                                        class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">All Roles</option>
                                        <option value="student">Students</option>
                                        <option value="faculty_staff">Faculty Staff</option>
                                    </select>
                                </div>
                            </div>

                            <div class="flex items-center space-x-2">
                                <label for="search" class="text-sm font-medium text-gray-700 whitespace-nowrap">
                                    Search:
                                </label>
                                <TextInput
                                    id="search"
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Search users..."
                                    class="w-64"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Users Table -->
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
                                            Role
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Department
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Created At
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="(user, index) in filteredUsers" :key="user.id"
                                        :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ (props.users.current_page - 1) * props.users.per_page + index + 1 }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ user.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ user.email }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getRoleBadgeClass(user.role)]">
                                                {{ formatRoleName(user.role) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span v-if="user.department" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                {{ user.department.name }}
                                            </span>
                                            <span v-else class="text-gray-400">No Department</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ new Date(user.created_at).toLocaleDateString() }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex flex-wrap gap-2">
                                                <SecondaryButton @click="openEditModal(user)" class="text-xs">
                                                    Edit
                                                </SecondaryButton>

                                                <PrimaryButton
                                                    v-if="user.role === 'student'"
                                                    @click="openPromoteModal(user)"
                                                    class="text-xs bg-green-600 hover:bg-green-700"
                                                >
                                                    Promote to Faculty
                                                </PrimaryButton>

                                                <SecondaryButton
                                                    v-if="user.role === 'faculty_staff'"
                                                    @click="openDemoteModal(user)"
                                                    class="text-xs bg-yellow-600 hover:bg-yellow-700 text-white"
                                                >
                                                    Demote to Student
                                                </SecondaryButton>

                                                <DangerButton @click="openDeleteModal(user)" class="text-xs">
                                                    Delete
                                                </DangerButton>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="props.users.last_page > 1" class="mt-6 flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Showing {{ props.users.from }} to {{ props.users.to }} of {{ props.users.total }} entries
                            </div>
                            <div class="flex space-x-2">
                                <SecondaryButton
                                    v-if="props.users.prev_page_url"
                                    @click="router.get(props.users.prev_page_url)"
                                    :disabled="!props.users.prev_page_url"
                                >
                                    Previous
                                </SecondaryButton>
                                <span
                                    v-for="page in props.users.links"
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
                                    v-if="props.users.next_page_url"
                                    @click="router.get(props.users.next_page_url)"
                                    :disabled="!props.users.next_page_url"
                                >
                                    Next
                                </SecondaryButton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create User Modal -->
        <Modal :show="showCreateModal" @close="closeModals">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Add New User</h2>
                <form @submit.prevent="createUser">
                    <div class="space-y-4">
                        <div>
                            <InputLabel for="name" value="Full Name" />
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
                            <InputLabel for="email" value="Email Address" />
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
                            <InputLabel for="role" value="User Role" />
                            <select
                                id="role"
                                v-model="createForm.role"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            >
                                <option value="student">Student</option>
                                <option value="faculty_staff">Faculty Staff</option>
                            </select>
                            <InputError :message="createForm.errors.role" class="mt-2" />
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
                            Create User
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit User Modal -->
        <Modal :show="showEditModal" @close="closeModals">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Edit User</h2>
                <form @submit.prevent="updateUser">
                    <div class="space-y-4">
                        <div>
                            <InputLabel for="edit_name" value="Full Name" />
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
                            <InputLabel for="edit_email" value="Email Address" />
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
                            <InputLabel for="edit_role" value="User Role" />
                            <select
                                id="edit_role"
                                v-model="editForm.role"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            >
                                <option value="student">Student</option>
                                <option value="faculty_staff">Faculty Staff</option>
                            </select>
                            <InputError :message="editForm.errors.role" class="mt-2" />
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
                            Update User
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Promote User Modal -->
        <Modal :show="showPromoteModal" @close="closeModals">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Promote to Faculty Staff</h2>
                <p class="text-sm text-gray-600 mb-4">
                    Are you sure you want to promote <strong>{{ selectedUser?.name }}</strong> to Faculty Staff?
                    They will gain admin-level access to the system.
                </p>

                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton type="button" @click="closeModals">
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton @click="promoteUser" :disabled="promoteForm.processing" class="bg-green-600 hover:bg-green-700">
                        Promote to Faculty Staff
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Demote User Modal -->
        <Modal :show="showDemoteModal" @close="closeModals">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Demote to Student</h2>
                <p class="text-sm text-gray-600 mb-4">
                    Are you sure you want to demote <strong>{{ selectedUser?.name }}</strong> to Student?
                    They will lose admin-level access to the system.
                </p>

                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton type="button" @click="closeModals">
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton @click="demoteUser" :disabled="demoteForm.processing" class="bg-yellow-600 hover:bg-yellow-700">
                        Demote to Student
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="closeModals">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Delete User</h2>
                <p class="text-sm text-gray-600 mb-4">
                    Are you sure you want to delete <strong>{{ selectedUser?.name }}</strong>? This action cannot be undone.
                    All associated data will be permanently removed.
                </p>

                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton type="button" @click="closeModals">
                        Cancel
                    </SecondaryButton>
                    <DangerButton @click="deleteUser" :disabled="deleteForm.processing">
                        Delete User
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
