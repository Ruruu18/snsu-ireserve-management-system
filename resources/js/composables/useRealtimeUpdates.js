import { ref, onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function useRealtimeUpdates() {
    const { props } = usePage();
    const notifications = ref([]);

    const addNotification = (notification) => {
        const id = Date.now() + Math.random();
        const newNotification = {
            id,
            ...notification,
            timestamp: new Date(),
        };

        notifications.value.unshift(newNotification);

        // Auto-remove after 5 seconds unless it's a critical status
        if (!['issued', 'completed', 'cancelled'].includes(notification.type)) {
            setTimeout(() => {
                removeNotification(id);
            }, 5000);
        }
    };

    const removeNotification = (id) => {
        const index = notifications.value.findIndex(n => n.id === id);
        if (index > -1) {
            notifications.value.splice(index, 1);
        }
    };

    const showToast = (message, type = 'info') => {
        // Create a toast notification
        const toast = document.createElement('div');
        toast.className = `fixed bottom-4 right-4 max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 z-50 transform transition-all duration-300 ease-in-out`;

        const typeClasses = {
            success: 'border-l-4 border-green-400',
            error: 'border-l-4 border-red-400',
            warning: 'border-l-4 border-yellow-400',
            info: 'border-l-4 border-blue-400',
        };

        const iconSvgs = {
            success: `<svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>`,
            error: `<svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>`,
            warning: `<svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>`,
            info: `<svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>`
        };

        toast.innerHTML = `
            <div class="p-4 ${typeClasses[type] || typeClasses.info}">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        ${iconSvgs[type] || iconSvgs.info}
                    </div>
                    <div class="ml-3 w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900">
                            ${message}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            ${new Date().toLocaleTimeString()}
                        </p>
                    </div>
                    <div class="ml-4 flex-shrink-0 flex">
                        <button class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none" onclick="this.parentElement.parentElement.parentElement.parentElement.remove()">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(toast);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.classList.add('opacity-0', 'translate-x-full');
                setTimeout(() => {
                    if (toast.parentNode) {
                        document.body.removeChild(toast);
                    }
                }, 300);
            }
        }, 5000);
    };

    onMounted(() => {
        // Only set up Echo if we're authenticated and have Pusher configuration
        if (props.auth?.user && window.Echo) {
            const userId = props.auth.user.id;

            // Listen for reservation status updates for this user
            window.Echo.private(`user.${userId}`)
                .listen('.reservation.status.updated', (e) => {
                    const reservation = e.reservation;
                    const message = e.message || 'Your reservation status has been updated.';

                    // Determine notification type based on status
                    let type = 'info';
                    if (['approved', 'issued', 'completed'].includes(reservation.status)) {
                        type = 'success';
                    } else if (['cancelled', 'rejected'].includes(reservation.status)) {
                        type = 'error';
                    } else if (reservation.status === 'return_requested') {
                        type = 'warning';
                    }

                    // Show toast notification
                    showToast(message, type);

                    // Add to notifications list
                    addNotification({
                        type: reservation.status,
                        title: `Reservation ${reservation.status}`,
                        message: message,
                        reservation_code: reservation.reservation_code,
                        student_name: reservation.student_name,
                    });

                    // Trigger a page refresh for certain status changes to update the UI
                    if (['approved', 'issued', 'completed', 'cancelled'].includes(reservation.status)) {
                        // Small delay to let the user see the notification
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    }
                });

            // Listen for admin notifications if user is admin
            if (['admin', 'faculty'].includes(props.auth.user.role)) {
                window.Echo.private('admin')
                    .listen('.reservation.status.updated', (e) => {
                        const reservation = e.reservation;
                        const message = `${reservation.student_name}'s reservation has been updated to ${reservation.status}`;

                        showToast(message, 'info');

                        addNotification({
                            type: 'admin_update',
                            title: 'Reservation Updated',
                            message: message,
                            reservation_code: reservation.reservation_code,
                            student_name: reservation.student_name,
                        });
                    });
            }
        }
    });

    onUnmounted(() => {
        // Clean up Echo listeners
        if (window.Echo && props.auth?.user) {
            const userId = props.auth.user.id;
            window.Echo.leave(`user.${userId}`);

            if (['admin', 'faculty'].includes(props.auth.user.role)) {
                window.Echo.leave('admin');
            }
        }
    });

    return {
        notifications,
        addNotification,
        removeNotification,
        showToast,
    };
}