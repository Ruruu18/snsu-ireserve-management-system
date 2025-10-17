<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import { useRealTimeNotifications } from '@/composables/useRealTimeData';

// State
const notifications = ref([]);
const unreadCount = ref(0);
const showDropdown = ref(false);
const loading = ref(false);
const selectedNotification = ref(null);
const showNotificationModal = ref(false);

// Fetch notifications
const fetchNotifications = async () => {
    try {
        const response = await axios.get('/admin/notifications');
        notifications.value = response.data.notifications;
        unreadCount.value = response.data.unread_count;
    } catch (error) {
        console.error('Error fetching notifications:', error);
    }
};

// Show notification details
const showNotificationDetails = (notification) => {
    selectedNotification.value = notification;
    showNotificationModal.value = true;
    showDropdown.value = false;

    // Mark as read when viewing details
    if (!notification.is_read) {
        markAsRead(notification.id);
    }
};

// Mark notification as read
const markAsRead = async (notificationId) => {
    try {
        const response = await axios.patch(`/admin/notifications/${notificationId}/read`);
        unreadCount.value = response.data.unread_count;

        // Update the notification in the list
        const notification = notifications.value.find(n => n.id === notificationId);
        if (notification) {
            notification.is_read = true;
        }
    } catch (error) {
        console.error('Error marking notification as read:', error);
    }
};

// Close notification modal
const closeNotificationModal = () => {
    showNotificationModal.value = false;
    selectedNotification.value = null;
};

// Mark all as read
const markAllAsRead = async () => {
    try {
        loading.value = true;
        await axios.patch('/admin/notifications/read-all');
        unreadCount.value = 0;
        notifications.value.forEach(notification => {
            notification.is_read = true;
        });
    } catch (error) {
        console.error('Error marking all notifications as read:', error);
    } finally {
        loading.value = false;
    }
};

// Toggle dropdown
const toggleDropdown = () => {
    showDropdown.value = !showDropdown.value;
    if (showDropdown.value && notifications.value.length === 0) {
        fetchNotifications();
    }
};

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
    const dropdown = document.getElementById('notification-dropdown');
    const button = document.getElementById('notification-button');

    if (dropdown && button && !dropdown.contains(event.target) && !button.contains(event.target)) {
        showDropdown.value = false;
    }
};

// Format text by removing underscores and capitalizing
const formatText = (text) => {
    if (!text) return '';
    return text
        .split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
        .join(' ');
};

// Get notification icon based on type
const getNotificationIcon = (type) => {
    switch (type) {
        case 'return_request':
            return '🔄';
        case 'new_reservation':
            return '📝';
        case 'cart_addition':
            return '🛒';
        case 'user_registration':
            return '👤';
        case 'student_creation':
            return '🎓';
        default:
            return '🔔';
    }
};

// Fetch unread count periodically
const refreshInterval = ref(null);

onMounted(() => {
    // Fetch initial unread count
    fetchNotifications();

    // Set up fast periodic refresh every 5 seconds for real-time feel
    refreshInterval.value = setInterval(() => {
        if (!showDropdown.value) {
            axios.get('/admin/notifications/unread-count').then(response => {
                unreadCount.value = response.data.unread_count;
            }).catch(error => {
                // Silently handle errors to avoid console spam
                console.debug('Notification fetch error:', error);
            });
        }
    }, 5000);

    // Add click outside listener
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    if (refreshInterval.value) {
        clearInterval(refreshInterval.value);
    }
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div class="relative">
        <!-- Notification Bell Button -->
        <button
            id="notification-button"
            @click="toggleDropdown"
            class="relative inline-flex items-center rounded-xl border border-gray-200 bg-white/90 backdrop-blur-sm p-2 transition-all duration-200 ease-in-out hover:bg-white hover:border-gray-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[#2F6C2F] focus:ring-offset-2 focus:ring-offset-[#d6efd8]"
        >
            <!-- Bell Icon -->
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>

            <!-- Unread Badge -->
            <span
                v-if="unreadCount > 0"
                class="absolute -top-1 -right-1 inline-flex items-center justify-center px-1 py-0.5 text-xs font-bold leading-none text-white bg-red-500 rounded-full min-w-[18px] h-[18px]"
            >
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
        </button>

        <!-- Notification Dropdown -->
        <div
            v-show="showDropdown"
            id="notification-dropdown"
            class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50 max-h-96 overflow-hidden"
        >
            <!-- Header -->
            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
                    <button
                        v-if="unreadCount > 0"
                        @click="markAllAsRead"
                        :disabled="loading"
                        class="text-xs text-blue-600 hover:text-blue-800 disabled:opacity-50"
                    >
                        {{ loading ? 'Marking...' : 'Mark all read' }}
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-1">
                    {{ unreadCount }} unread notification{{ unreadCount !== 1 ? 's' : '' }}
                </p>
            </div>

            <!-- Notifications List -->
            <div class="max-h-64 overflow-y-auto">
                <div v-if="notifications.length === 0" class="px-4 py-8 text-center text-gray-500">
                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m13-8V3a1 1 0 00-1-1H6a1 1 0 00-1 1v2m16 0V3a1 1 0 00-1-1H6a1 1 0 00-1 1v2" />
                    </svg>
                    <p class="text-sm">No notifications yet</p>
                </div>

                <div
                    v-for="notification in notifications"
                    :key="notification.id"
                    @click="showNotificationDetails(notification)"
                    :class="[
                        'px-4 py-3 border-b border-gray-100 cursor-pointer hover:bg-gray-50 transition-colors duration-150',
                        !notification.is_read ? 'bg-blue-50' : ''
                    ]"
                >
                    <div class="flex items-start space-x-3">
                        <!-- Notification Icon -->
                        <div class="flex-shrink-0 mt-0.5">
                            <span class="text-lg">{{ getNotificationIcon(notification.type) }}</span>
                        </div>

                        <!-- Notification Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    {{ notification.title }}
                                </p>
                                <div v-if="!notification.is_read" class="w-2 h-2 bg-blue-500 rounded-full flex-shrink-0"></div>
                            </div>
                            <p class="text-sm text-gray-600 mt-1 line-clamp-2">
                                {{ notification.message }}
                            </p>
                            <p class="text-xs text-gray-400 mt-1">
                                {{ notification.time_ago }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div v-if="notifications.length > 0" class="px-4 py-2 border-t border-gray-200 bg-gray-50">
                <button
                    @click="showDropdown = false"
                    class="w-full text-center text-xs text-gray-500 hover:text-gray-700"
                >
                    Close
                </button>
            </div>
        </div>

        <!-- Notification Details Modal -->
        <div
            v-if="showNotificationModal"
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title"
            role="dialog"
            aria-modal="true"
        >
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    @click="closeNotificationModal"
                ></div>

                <!-- Modal panel -->
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <!-- Icon -->
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                <span class="text-2xl">{{ selectedNotification ? getNotificationIcon(selectedNotification.type) : '🔔' }}</span>
                            </div>

                            <!-- Content -->
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    {{ selectedNotification?.title }}
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 mb-4">
                                        {{ selectedNotification?.message }}
                                    </p>

                                    <!-- Additional details from notification data -->
                                    <div v-if="selectedNotification?.data" class="bg-gray-50 rounded-lg p-3 mb-4">
                                        <h4 class="text-sm font-medium text-gray-900 mb-2">Details:</h4>
                                        <div class="space-y-1 text-sm text-gray-600">
                                            <div v-if="selectedNotification.data.user_name">
                                                <strong>Student:</strong> {{ selectedNotification.data.user_name }}
                                            </div>
                                            <div v-if="selectedNotification.data.equipment_name">
                                                <strong>Equipment:</strong> {{ selectedNotification.data.equipment_name }}
                                            </div>
                                            <div v-if="selectedNotification.data.quantity">
                                                <strong>Quantity:</strong> {{ selectedNotification.data.quantity }}
                                            </div>
                                            <div v-if="selectedNotification.data.item_count">
                                                <strong>Items:</strong> {{ selectedNotification.data.item_count }}
                                            </div>
                                            <div v-if="selectedNotification.data.total_quantity">
                                                <strong>Total Quantity:</strong> {{ selectedNotification.data.total_quantity }}
                                            </div>
                                            <div v-if="selectedNotification.data.reservation_id">
                                                <strong>Reservation ID:</strong> {{ selectedNotification.data.reservation_id }}
                                            </div>
                                        </div>
                                    </div>

                                    <p class="text-xs text-gray-400">
                                        {{ selectedNotification?.time_ago }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal footer -->
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button
                            type="button"
                            @click="closeNotificationModal"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>