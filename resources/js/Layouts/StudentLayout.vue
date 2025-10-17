<script setup>
import { ref } from 'vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import StudentSidebar from '@/Components/StudentSidebar.vue';
import Header from '@/Components/Header.vue';

const showingNavigationDropdown = ref(false);
const showMobileSidebar = ref(false);

const closeMobileSidebar = () => {
    showMobileSidebar.value = false;
};
</script>

<template>
    <div>
        <div class="h-screen bg-gray-100 flex flex-col overflow-hidden">
            <!-- Top Navigation Bar -->
            <Header @openMobileMenu="showMobileSidebar = true" />

            <!-- Mobile Navigation Menu -->
            <div
                :class="{
                    block: showingNavigationDropdown,
                    hidden: !showingNavigationDropdown,
                }"
                class="sm:hidden"
            >
                <div class="space-y-1 pb-3 pt-2">
                    <ResponsiveNavLink
                        :href="route('student.dashboard')"
                        :active="route().current('student.dashboard')"
                    >
                        Student Dashboard
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('profile.edit')">
                        Profile
                    </ResponsiveNavLink>
                </div>

                <!-- Mobile Settings Options -->
                <div class="border-t border-gray-200 pb-1 pt-4">
                    <div class="px-4">
                        <div class="text-base font-medium text-gray-800">
                            {{ $page.props.auth.user.name }}
                        </div>
                        <div class="text-sm font-medium text-gray-500">
                            {{ $page.props.auth.user.email }}
                        </div>
                    </div>

                    <div class="mt-3 space-y-1">
                        <ResponsiveNavLink
                            :href="route('logout')"
                            method="post"
                            as="button"
                        >
                            Log Out
                        </ResponsiveNavLink>
                    </div>
                </div>
            </div>

            <!-- Main Layout with Sidebar -->
            <div class="flex flex-1 overflow-hidden">
                <!-- Student Sidebar -->
                <StudentSidebar
                    :showMobileMenu="showMobileSidebar"
                    @closeMobileMenu="closeMobileSidebar"
                />

                <!-- Main Content Area -->
                <div class="flex-1 flex flex-col h-full overflow-hidden md:ml-0">
                    <!-- Page Heading -->
                    <header
                        class="bg-white shadow flex-shrink-0"
                        v-if="$slots.header"
                    >
                        <div class="px-4 py-6 sm:px-6 lg:px-8">
                            <slot name="header" />
                        </div>
                    </header>

                    <!-- Page Content -->
                    <main class="flex-1 overflow-y-auto overflow-x-hidden">
                        <slot />
                    </main>
                </div>
            </div>
        </div>
    </div>
</template>
