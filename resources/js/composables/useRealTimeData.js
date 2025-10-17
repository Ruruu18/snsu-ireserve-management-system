import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

export function useRealTimeData(url, interval = 5000, options = {}) {
    const data = ref(null);
    const loading = ref(false);
    const error = ref(null);
    const refreshInterval = ref(null);

    const {
        immediate = true,
        onSuccess = () => {},
        onError = () => {},
        condition = () => true
    } = options;

    const fetchData = async () => {
        if (!condition()) return;

        try {
            loading.value = true;
            error.value = null;
            const response = await axios.get(url);
            data.value = response.data;
            onSuccess(response.data);
        } catch (err) {
            error.value = err;
            onError(err);
            console.debug('Real-time fetch error:', err);
        } finally {
            loading.value = false;
        }
    };

    const startPolling = () => {
        if (refreshInterval.value) {
            clearInterval(refreshInterval.value);
        }
        refreshInterval.value = setInterval(fetchData, interval);
    };

    const stopPolling = () => {
        if (refreshInterval.value) {
            clearInterval(refreshInterval.value);
            refreshInterval.value = null;
        }
    };

    const restart = (newInterval) => {
        stopPolling();
        if (newInterval) interval = newInterval;
        startPolling();
    };

    onMounted(() => {
        if (immediate) {
            fetchData();
        }
        startPolling();
    });

    onUnmounted(() => {
        stopPolling();
    });

    return {
        data,
        loading,
        error,
        fetchData,
        startPolling,
        stopPolling,
        restart
    };
}

export function useRealTimeStats(interval = 10000) {
    return useRealTimeData('/admin-dashboard/stats', interval);
}

export function useRealTimeNotifications(interval = 5000) {
    return useRealTimeData('/admin/notifications/unread-count', interval);
}

export function useRealTimeReservations(interval = 8000) {
    return useRealTimeData('/admin/reservations/recent', interval);
}