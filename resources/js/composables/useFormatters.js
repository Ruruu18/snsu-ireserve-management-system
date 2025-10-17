/**
 * Composable for formatting text values
 */
export function useFormatters() {
    /**
     * Format text by removing underscores and capitalizing words
     * Examples:
     * - "return_request" → "Return Request"
     * - "faculty_staff" → "Faculty Staff"
     * - "pending" → "Pending"
     */
    const formatText = (text) => {
        if (!text) return '';
        return text
            .split('_')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
            .join(' ');
    };

    /**
     * Format status specifically
     */
    const formatStatus = (status) => {
        return formatText(status);
    };

    /**
     * Format role specifically
     */
    const formatRole = (role) => {
        return formatText(role);
    };

    return {
        formatText,
        formatStatus,
        formatRole
    };
}
