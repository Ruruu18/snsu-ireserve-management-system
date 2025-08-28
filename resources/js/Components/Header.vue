<script setup>
import { ref } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
</script>

<template>
    <nav class="border-b border-gray-200 bg-gradient-to-r from-[#d6efd8] to-[#c8e6ca] shadow-sm">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex h-20 justify-between items-center">
                <div class="flex items-center">
                    <!-- Logo -->
                    <Link
                        :href="page.props.auth.user.role === 'admin' ? route('dashboard') : page.props.auth.user.role === 'faculty_staff' ? route('faculty.dashboard') : route('student.dashboard')"
                        class="flex items-center space-x-3 hover:opacity-80 transition-opacity duration-200"
                    >
                        <img src="/images/logo.png" alt="SNSU Logo" class="h-16 w-auto drop-shadow-sm">
                        <div class="flex flex-col">
                            <span class="text-2xl font-black text-[#2F6C2F] tracking-tight">SNSU iReserve</span>
                            <span class="text-base font-bold text-[#2F6C2F] opacity-90 tracking-tight">Management System</span>
                        </div>
                    </Link>
                </div>

                <div class="flex items-center">
                    <!-- Profile Dropdown -->
                    <div class="relative">
                        <Dropdown align="right" width="56">
                            <template #trigger>
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-xl border border-gray-200 bg-white/90 backdrop-blur-sm px-4 py-2.5 text-sm font-medium text-gray-700 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[#2F6C2F] focus:ring-offset-2 focus:ring-offset-[#d6efd8] group"
                                >
                                    <div class="flex items-center space-x-3">
                                        <div class="relative">
                                            <img src="/images/user.png" alt="User Icon" class="w-6 h-6 text-gray-600">
                                            <div class="absolute -bottom-1 -right-1 w-3 h-3 bg-green-500 border-2 border-white rounded-full"></div>
                                        </div>
                                        <span class="font-medium">{{ page.props.auth.user.name }}</span>
                                        <svg
                                            class="w-4 h-4 text-gray-500 transition-transform duration-200 group-hover:rotate-180"
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
                                                <p class="text-xs text-gray-500 capitalize">{{ page.props.auth.user.role }}</p>
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
        </div>
    </nav>
</template>
