<script setup>
import { ref } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import CartIcon from '@/Components/CartIcon.vue';
import NotificationBell from '@/Components/NotificationBell.vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const showMobileMenu = ref(false);

const emit = defineEmits(['openMobileMenu']);

const toggleMobileMenu = () => {
    showMobileMenu.value = !showMobileMenu.value;
};

const openMobileMenu = () => {
    emit('openMobileMenu');
};
</script>

<template>
    <nav class="border-b border-gray-200 bg-gradient-to-r from-[#d6efd8] to-[#c8e6ca] shadow-sm">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 sm:h-20 justify-between items-center">
                <div class="flex items-center">
                    <!-- Mobile Menu Button -->
                    <button
                        @click="openMobileMenu"
                        class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-[#2F6C2F] hover:text-[#1a4a1a] hover:bg-[#c8e6ca] focus:outline-none focus:ring-2 focus:ring-inset focus:ring-[#2F6C2F] mr-3"
                        aria-controls="mobile-menu"
                        :aria-expanded="false"
                    >
                        <span class="sr-only">Open main menu</span>
                        <svg v-show="!showMobileMenu" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg v-show="showMobileMenu" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <!-- Logo -->
                    <Link
                        :href="page.props.auth.user.role === 'admin' ? route('dashboard') : page.props.auth.user.role === 'faculty_staff' ? route('faculty.dashboard') : route('student.dashboard')"
                        class="flex items-center space-x-2 sm:space-x-3 hover:opacity-80 transition-opacity duration-200"
                    >
                        <img src="/images/logo.png" alt="SNSU Logo" class="h-10 sm:h-12 lg:h-16 w-auto drop-shadow-sm">
                        <div class="flex flex-col">
                            <span class="text-lg sm:text-xl lg:text-2xl font-black text-[#2F6C2F] tracking-tight">SNSU iReserve</span>
                            <span class="text-xs sm:text-sm lg:text-base font-bold text-[#2F6C2F] opacity-90 tracking-tight hidden sm:block">Management System</span>
                        </div>
                    </Link>
                </div>

                <div class="flex items-center space-x-2 sm:space-x-3">
                    <!-- Cart Icon (Only for students) -->
                    <CartIcon v-if="page.props.auth.user.role === 'student'" />

                    <!-- Notification Bell (Only for admins) -->
                    <NotificationBell v-if="page.props.auth.user.role === 'admin'" />

                    <!-- Profile Dropdown -->
                    <div class="relative">
                        <Dropdown align="right" width="56">
                            <template #trigger>
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-xl border border-gray-200 bg-white/90 backdrop-blur-sm px-2 sm:px-4 py-2 sm:py-2.5 text-xs sm:text-sm font-medium text-gray-700 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[#2F6C2F] focus:ring-offset-2 focus:ring-offset-[#d6efd8] group"
                                >
                                    <div class="flex items-center space-x-1 sm:space-x-3">
                                        <div class="relative">
                                            <img src="/images/user.png" alt="User Icon" class="w-5 sm:w-6 h-5 sm:h-6 text-gray-600">
                                            <div class="absolute -bottom-1 -right-1 w-2 sm:w-3 h-2 sm:h-3 bg-green-500 border-2 border-white rounded-full"></div>
                                        </div>
                                        <span class="font-medium hidden sm:inline-block truncate max-w-[120px]">{{ page.props.auth.user.name }}</span>
                                        <span class="font-medium sm:hidden text-xs">{{ page.props.auth.user.name.charAt(0).toUpperCase() }}</span>
                                        <svg
                                            class="w-3 sm:w-4 h-3 sm:h-4 text-gray-500 transition-transform duration-200 group-hover:rotate-180 hidden sm:inline-block"
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </div>
                                </button>
                            </template>

                            <template #content>
                                <div class="py-2">
                                    <!-- User Info Section -->
                                    <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/50">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-gradient-to-br from-[#2F6C2F] to-[#4a7c59] rounded-full flex items-center justify-center">
                                                <span class="text-white font-semibold text-sm">
                                                    {{ page.props.auth.user.name.charAt(0).toUpperCase() }}
                                                </span>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ page.props.auth.user.name }}</p>
                                                <p class="text-xs text-gray-500 capitalize">{{ page.props.auth.user.role.replace('_', ' ') }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Menu Items -->
                                    <div class="py-1">
                                        <DropdownLink
                                            :href="route('profile.edit')"
                                            class="flex items-center space-x-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-[#f0f9f0] hover:text-[#2F6C2F] transition-colors duration-150"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            <span>Profile Settings</span>
                                        </DropdownLink>

                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                            class="flex items-center space-x-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors duration-150 w-full text-left"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                            </svg>
                                            <span>Sign Out</span>
                                        </DropdownLink>
                                    </div>
                                </div>
                            </template>
                        </Dropdown>
                    </div>
                </div>
            </div>

            <!-- Mobile menu panel -->
            <div v-show="showMobileMenu" class="md:hidden" id="mobile-menu">
                <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 bg-white border-t border-gray-200">
                    <div class="px-3 py-2">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-gradient-to-br from-[#2F6C2F] to-[#4a7c59] rounded-full flex items-center justify-center">
                                <span class="text-white font-semibold text-xs">
                                    {{ page.props.auth.user.name.charAt(0).toUpperCase() }}
                                </span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ page.props.auth.user.name }}</p>
                                <p class="text-xs text-gray-500 capitalize">{{ page.props.auth.user.role.replace('_', ' ') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-3">
                        <Link
                            :href="route('profile.edit')"
                            class="flex items-center space-x-3 px-3 py-2 text-sm text-gray-700 hover:bg-[#f0f9f0] hover:text-[#2F6C2F] transition-colors duration-150 rounded-md"
                            @click="showMobileMenu = false"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span>Profile Settings</span>
                        </Link>

                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="flex items-center space-x-3 px-3 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors duration-150 w-full text-left rounded-md"
                            @click="showMobileMenu = false"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            <span>Sign Out</span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</template>
